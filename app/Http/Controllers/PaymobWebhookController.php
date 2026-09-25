<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymobWebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        // 1. قراءة البيانات القادمة من Paymob
        $data = $request->all();
        $obj = $data['obj'] ?? [];

        // 2. التحقق من الـ HMAC
        if (!$this->verifyHmac($data)) {
            Log::error('Paymob Callback Failed: Invalid HMAC signature');
            return response()->json(['message' => 'Invalid HMAC signature'], 400);
        }

        $merchantOrderId = $obj['order']['merchant_order_id'] ?? null;
        if (!$merchantOrderId) {
            Log::error('Paymob Callback Failed: Missing merchant_order_id');
            return response()->json(['message' => 'Order reference missing'], 400);
        }

        $isSuccess = filter_var($obj['success'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $paidAmountCents = (int) ($obj['amount_cents'] ?? 0);

        try {
            return DB::transaction(function () use ($merchantOrderId, $isSuccess, $paidAmountCents, $obj) {

                // 3. قفل الصفوف واستدعاء العلاقة الصحيحة (items)
                $order = Order::with('items')->where('id', $merchantOrderId)->lockForUpdate()->first();
                $payment = Payment::where('order_id', $merchantOrderId)->lockForUpdate()->first();

                if (!$order || !$payment) {
                    Log::error("Paymob Callback Failed: Order or Payment not found for ID: {$merchantOrderId}");
                    return response()->json(['message' => 'Order or Payment record not found'], 404);
                }

                // 4. Idempotency Check: التأكد أن العملية لم تُعالج سابقاً
                if ($payment->status === 'completed' || $order->status === 'processing' || $order->status === 'completed') {
                    return response()->json(['message' => 'Already processed'], 200);
                }

                // حساب المبلغ المتوقع بالقروش
                $expectedAmountCents = (int) round((float) $order->total * 100);

                // السماح بفرق التقريب المسموح به (حتى 100 قرش = 1 جنيه)
                $isAmountValid = abs($paidAmountCents - $expectedAmountCents) <= 100;

                // 5. عند نجاح الدفع وصحة المبلغ
                if ($isSuccess && $isAmountValid) {

                    // تحديث جدول الـ Payment
                    $payment->update([
                        'status' => 'completed',
                        'transaction_id' => $obj['id'] ?? null,
                        'paid_at' => now(),
                    ]);

                    // تحديث جدول الـ Order
                    $order->update([
                        'status' => 'pending',
                    ]);

                    // خصم الكميات من المخزون باستخدام علاقة items
                    if ($order->items && $order->items->isNotEmpty()) {
                        foreach ($order->items as $item) {
                            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                            if ($product) {
                                $product->decrement('stock', $item->quantity);
                            }
                        }
                    }

                    // تفريغ/تعديل حالة السلة (Cart)
                    if ($order->user) {
                        $cart = $order->user->cart()->where('status', 'active')->first();
                        if ($cart) {
                            $cart->update(['status' => 'completed']);
                        }
                    }

                    return response()->json(['message' => 'Payment successful and order updated'], 200);
                } else {
                    // 6. في حالة فشل الدفع أو عدم تطابق المبلغ
                    Log::warning("Paymob Callback Mismatch/Failed: Paid: {$paidAmountCents}, Expected: {$expectedAmountCents}, Success: " . ($isSuccess ? 'true' : 'false'));

                    $payment->update(['status' => 'failed']);
                    $order->update(['status' => 'failed']);

                    return response()->json(['message' => 'Payment failed or amount mismatch'], 200);
                }
            });
        } catch (\Exception $e) {
            Log::error('Paymob Callback Exception: ' . $e->getMessage());

            return response()->json([
                'message' => 'Callback processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * التحقق من التوقيع المشفر (HMAC)
     */
    private function verifyHmac(array $data): bool
    {
        $hmacSecret = config('services.paymob.hmac');
        $receivedHmac = $data['hmac'] ?? '';
        $obj = $data['obj'] ?? [];

        // دالة صغيرة لتحويل القيم البولينية لنصوص حسب متطلبات Paymob
        $formatBool = function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }
            return $value;
        };

        // الترتيب الصارم مع تحويل القيم البولينية
        $str = ($obj['amount_cents'] ?? '') .
            ($obj['created_at'] ?? '') .
            ($obj['currency'] ?? '') .
            $formatBool($obj['error_occured'] ?? '') .
            $formatBool($obj['has_parent_transaction'] ?? '') .
            ($obj['id'] ?? '') .
            ($obj['integration_id'] ?? '') .
            $formatBool($obj['is_3d_secure'] ?? '') .
            $formatBool($obj['is_auth'] ?? '') .
            $formatBool($obj['is_capture'] ?? '') .
            $formatBool($obj['is_refunded'] ?? '') .
            $formatBool($obj['is_standalone_payment'] ?? '') .
            $formatBool($obj['is_voided'] ?? '') .
            ($obj['order']['id'] ?? '') .
            ($obj['owner'] ?? '') .
            $formatBool($obj['pending'] ?? '') .
            ($obj['source_data']['pan'] ?? '') .
            ($obj['source_data']['sub_type'] ?? '') .
            ($obj['source_data']['type'] ?? '') .
            $formatBool($obj['success'] ?? '');

        $calculatedHmac = hash_hmac('sha512', $str, $hmacSecret);

        return hash_equals($calculatedHmac, $receivedHmac);
    }
    public function checkoutReturn(Request $request)
    {
        // Paymob بيبعت النتيجة في الرابط مباشرة كـ GET Parameters
        $isSuccess = filter_var($request->query('success'), FILTER_VALIDATE_BOOLEAN);

        if ($isSuccess) {
            // توجيه العميل لصفحة النجاح في الواجهة الأمامية (Frontend)
            // أو إرجاع رسالة نجاح بسيطة
            return response()->json(['message' => 'تم الدفع بنجاح! جاري تحضير طلبك.']);

            // أو لو بتستخدم Blade:
            // return view('payment.success');
        } else {
            // توجيه العميل لصفحة الفشل
            return response()->json(['message' => 'فشلت عملية الدفع، يرجى المحاولة مرة أخرى.']);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function destroy(string $id)
    {
        //
    }
}
