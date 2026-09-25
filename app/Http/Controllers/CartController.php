<?php

namespace App\Http\Controllers;

use App\Services\PaymobService;
use App\Http\Requests\CartitemRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\CartitemResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Cartitem;
use App\Models\Order;
use App\Models\Orderitem;
use App\Models\Payment;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //     CartController
    // ├── index()          // Get Cart + Cart Items
    // ├── store()          // Add item
    // ├── update()         // Update quantity
    // ├── destroyItem()    // Delete one item
    // ├── clear()          // Clear entire cart


    public function index()
    {
        $cart = auth()->user()->cart()->where('status', 'active')->first();
        if ($cart === null) {
            return response()->json(['cart' => null, 'cart_items' => []], 200);
        }
        $cartItems = $cart->cartItems;
        return response()->json([
            'cart' => new CartResource($cart),
            'cart_items' => CartitemResource::collection($cartItems)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartitemRequest $request)
    {
        $data = $request->validated();
        $product_id = $data['product_id'];
        $product = Product::where('id', $product_id)->firstOrFail();
        if ($product->status !== 'active') {
            return response()->json(['message' => 'this product is not avilable'], 422);
        }

        $cart = auth()->user()->cart()->where('status', 'active')->first();
        if (auth()->user()->cart()->where('status', 'active')->doesntExist()) {
            $cart = Cart::create(['user_id' => auth()->id()]);
        }

        $cartItem = Cartitem::where('cart_id', $cart->id)
            ->where('product_id', $product_id)->first();

        if ($cartItem === null) {
            if ($product->stock < $data['quantity']) {
                return response()->json(['message' => 'the quantity is not avilable'], 422);
            }
            $data['cart_id'] = $cart->id;
            $data['price'] = $product->price;
            $newCartItem = Cartitem::create($data);
            return response()->json([
                'message' => 'cart item created successfully',
                'cart_item' => new CartitemResource($newCartItem)
            ], 201);
        }
        if ($cartItem->quantity + $data['quantity'] > $product->stock) {
            return response()->json(['message' => 'the quantity is not avilable'], 422);
        }
        $new_quantity = $cartItem->quantity + $data['quantity'];
        $cartItem->update(['quantity' => $new_quantity, 'price' => $product->price]);
        return response()->json([
            'cart_item' => new CartitemResource($cartItem)
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cartitem $cart)
    {

        $cart_user_id = $cart->cart->user->id;
        if (auth()->id() !== $cart_user_id) {
            return response()->json([
                'message' => 'you cannot delete this item',
            ], 403);
        }
        $cart->delete();
        return response()->json([
            'message' => 'cart item deleted successfully',
        ], 200);
    }
    public function clear()
    {
        $cart = auth()->user()->cart()->where('status', 'active')->first();
        // $cart_user_id = $cart->user->id;
        // if (auth()->id() !== $cart_user_id) {
        //     return response()->json([
        //         'message' => 'you cannot clear this cart',
        //     ], 403);
        // }
        if ($cart !== null) {
            $cart->cartItems()->delete();
        }
        return response()->json([
            'message' => 'cart cleared successfully',
        ], 200);
    }



    public function checkout(CheckoutRequest $checkout, PaymobService $paymobService)
    {
        $user = auth()->user();
        $cart = $user->cart()->where('status', 'active')->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        $cartItems = $cart->cartItems;
        $paymentMethod = $checkout->payment_method; // 'cash_on_delivery' or 'card'

        try {
            $result = DB::transaction(function () use ($cart, $cartItems, $checkout, $paymentMethod, $user) {

                $subtotal = 0;
                $orderItemsData = [];

                // 1. Lock Products & Validate Stock instantly from locked rows
                foreach ($cartItems as $item) {
                    $product = Product::where('id', $item->product_id)
                        ->where('status', 'active')
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw new Exception("Product ID {$item->product_id} is no longer available.");
                    }

                    if ($product->stock < $item->quantity) {
                        throw new Exception("Quantity for product {$product->name} is not available.");
                    }

                    $unitPrice = round($product->price * (1 - ($product->discount / 100)), 2);
                    $itemSubtotal = round($unitPrice * $item->quantity, 2);
                    $subtotal += $itemSubtotal;

                    $orderItemsData[] = [
                        'product' => $product,
                        'quantity' => $item->quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                $shippingFee = 50;
                $total = round($subtotal + $shippingFee, 2);

                // 2. Create Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'total' => $total,
                    'discount' => 0,
                    'status' => 'pending',
                    'shipping_address' => $checkout->shipping_address,

                ]);

                // 3. Create Order Items & Decrement Stock (If COD)
                foreach ($orderItemsData as $itemData) {
                    $product = $itemData['product'];
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'unit_price' => $itemData['unit_price'],
                        'quantity' => $itemData['quantity'],
                        'subtotal' => $itemData['subtotal'],
                    ]);

                    // خصم مباشر فقط لو كاش عند الاستلام
                    if ($paymentMethod === 'cash_on_delivery') {
                        $product->decrement('stock', $itemData['quantity']);
                    }
                }

                // 4. Create Payment Record
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'method' => $paymentMethod,
                    'amount' => $total,
                    'status' => 'pending',
                    'gateway'=>'paymob'
                ]);

                // 5. Update Cart status if COD
                if ($paymentMethod === 'cash_on_delivery') {
                    $cart->update(['status' => 'completed']);
                }

                return [
                    'order' => $order,
                    'payment' => $payment,
                ];
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage(),
            ], 422);
        }

        $order = $result['order'];
        $payment = $result['payment'];

        // 6. Handle Paymob Integration Strategy outside DB transaction
        if ($paymentMethod === 'card') {
            try {
                $intention = $paymobService->createIntention($order);

                $payment->update([
                    'gateway_order_id' => $intention['id'],
                ]);

                $publicKey = config('services.paymob.public_key');
                $clientSecret = $intention['client_secret'];
                $paymentUrl = "https://accept.paymob.com/unifiedcheckout/?publicKey={$publicKey}&clientSecret={$clientSecret}";

                return response()->json([
                    'payment_url' => $paymentUrl,
                    'order_id' => $order->id,
                ]);
            } catch (Exception $e) {
                // لو Paymob API فشل، يلغى الطلب
                $order->update(['status' => 'failed']);
                $payment->update(['status' => 'failed']);

                return response()->json(['message' => 'Payment gateway error, please try again.'], 500);
            }
        }

        return response()->json([
            'message' => 'Order created successfully',
            'payment' => $payment,
            'order' => $order,
        ], 200);
    }
}
