<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PaymobService
{
    // public function authenticate()
    // {
    //     $response = Http::post(
    //         'https://accept.paymob.com/api/auth/tokens',
    //         [
    //             'api_key' => config('services.paymob.api_key'),
    //         ]
    //     );

    //     if (!$response->successful()) {
    //         throw new \Exception('Paymob authentication failed');
    //     }

    //     return $response->json('token');
    // }

    // public function createOrder(string $token, Order $order)
    // {
    //     $response = Http::post(
    //         'https://accept.paymob.com/api/ecommerce/orders',
    //         [
    //             'auth_token' => $token,

    //             'delivery_needed' => false,

    //             'amount_cents' => (int) ($order->subtotal * 100),

    //             'currency' => 'EGP',

    //             'merchant_order_id' => $order->id,

    //             'items' => [],
    //         ]
    //     );
    //     if (!$response->successful()) {

    //         if (!$response->successful()) {
    //             return $response->json();
    //         }
    //     }
    //     return $response->json();
    // }
    public function createIntention(Order $order)
    { 
        $user = auth()->user();

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . config('services.paymob.secret_key'),
            'Content-Type'  => 'application/json',
        ])->post('https://accept.paymob.com/v1/intention/', [
            'amount' => (int) round($order->total * 100), // إجمالي المبلغ بالقروش شامل الشحن
            'currency' => 'EGP',
            'payment_methods' => [
                (int) config('services.paymob.integration_id'),
            ],
            'items' => [
                [
                    'name' => 'Order #' . $order->id,
                    'amount' => (int) round($order->total * 100),
                    'description' => 'Purchase from Store',
                    'quantity' => 1,
                ]
            ],
            'billing_data' => [
                'first_name' => $user->first_name ?? $user->name ?? 'Customer',
                'last_name' => $user->last_name ?? 'User',
                'email' => $user->email ?? 'customer@example.com',
                'phone_number' => $user->phone ?? '01000000000',
            ],
            'special_reference' => (string) $order->id,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Paymob Intention creation failed: ' . $response->body());
        }

        return $response->json();
    }
}
