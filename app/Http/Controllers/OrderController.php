<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Http\Resources\OrderResource;
use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function my_orders()
    {
        $orders = auth()->user()->orders()->latest()->get();
        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data'    => OrderResource::collection($orders)
        ], 200);
    }
    public function my_order(string $id)
    {
        $order = auth()->user()->orders()->where('id', $id)->with('payments')->with('items')->firstOrFail();
        return response()->json([
            'message' => 'Order retrieved successfully',
            'data'    => new OrderResource($order)
        ], 200);
    }
    public function index(Request $request)
    {
        $query = Order::with('payments');

        //search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('user_id', 'like', "%{$search}%")
                    ->orWhere('shipping_address', 'like', "%{$search}%");
            });
        }
        //filtering
        if ($request->filled('order_status')) {
            $query->where('status', $request->order_status);
        }
        if ($request->filled('payment_status')) {
            $query->whereHas('payments', function ($q) use ($request) {
                $q->where('status', $request->payment_status);
            });
        }
        if ($request->filled('payment_method')) {
            $query->whereHas('payments', function ($q) use ($request) {
                $q->where('method', $request->payment_method);
            });
        }
        //sort
        $allowedSorts = [
            'total',
            'created_at'
        ];

        if ($request->filled('sort')) {
            $sort = strtolower($request->sort);
            $direction = strtolower($request->direction ?? 'asc');

            if (!in_array($direction, ['asc', 'desc'])) {

                $direction = 'asc';
            }
            if (in_array($sort, $allowedSorts)) {

                $query->orderBy($sort, $direction);
            }
        } else {

            $query->latest();
        }
        return OrderResource::collection($query->paginate(10));
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
        $order = Order::where('id', $id)->with('payments')->with('items')->firstOrFail();
        return response()->json([
            'message' => 'Order retrieved successfully',
            'data'    => new OrderResource($order)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        $newStatus = OrderStatus::tryFrom($request->status);
        if (!$newStatus) {
            return response()->json(['message' => 'status not available'], 422);
        }
        if (!$order->status->canTransitionTo($newStatus)) {
            return response()->json([
                'message' => "you cannot change status from {$order->status->value} to {$newStatus->value}."
            ], 422);
        }
        DB::transaction(function () use ($newStatus,$order) {
        $payment = $order->payments()->where('method', 'cash_on_delivery')->latest()->first();
        if ($newStatus === OrderStatus::DELIVERED && $payment) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);
        }
        $order->update(['status' => $newStatus]);
        DB::afterCommit(function () use ($order) {
            event(new OrderStatusUpdated($order));
        });
        });
        return response()->json([
            'message' => 'the status updated successfully',
            'order' => $order
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
