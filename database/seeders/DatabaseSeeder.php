<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\Order;
use App\Models\Orderitem;
use App\Models\Payment;
use App\Enums\OrderStatus;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@gmail.com',
            'phone'    => '01000000000',
            'address'  => 'Cairo, Egypt',
            'role'     => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // 2. Customers
        $customers = User::factory(25)->create([
            'role'     => 'user',
            'password' => Hash::make('password123'),
        ]);

        // 3. Categories & Subcategories
        $subcategories = collect();
        $categoriesNames = ['Electronics', 'Fashion', 'Home & Kitchen', 'Beauty'];

        foreach ($categoriesNames as $name) {
            $cat = Category::create(['name' => $name]);
            for ($i = 1; $i <= 3; $i++) {
                $sub = Subcategory::create([
                    'name'        => "{$name} Sub-{$i}",
                    'category_id' => $cat->id,
                ]);
                $subcategories->push($sub);
            }
        }

        // 4. Products
        $products = collect();
        foreach ($subcategories as $sub) {
            $prods = Product::factory(3)->create(['subcategory_id' => $sub->id]);
            $products = $products->merge($prods);
        }

        // 5. Orders
        $statuses = [
            OrderStatus::DELIVERED,
            OrderStatus::DELIVERED,
            OrderStatus::SHIPPED,
            OrderStatus::PENDING,
        ];

        for ($i = 0; $i < 60; $i++) {
            $createdAt = fake()->dateTimeBetween('-45 days', 'now');
            $status = fake()->randomElement($statuses);
            $user = $customers->random();

            $order = Order::create([
                'user_id'          => $user->id,
                'status'           => $status,
                'subtotal'         => 0,
                'shipping_fee'     => 50.00,
                'discount'         => 0.00,
                'total'            => 0,
                'shipping_address' => [
                    'city'   => fake()->city(),
                    'street' => fake()->streetAddress(),
                    'phone'  => $user->phone ?? '01123456789',
                ],
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);

            $selectedProducts = $products->random(rand(1, 4));
            $orderSubtotal = 0;

            foreach ($selectedProducts as $prod) {
                $qty = rand(1, 3);
                $lineTotal = $prod->price * $qty;
                $orderSubtotal += $lineTotal;

                Orderitem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $prod->id,
                    'product_name' => $prod->name,
                    'product_sku'  => $prod->sku,
                    'unit_price'   => $prod->price,
                    'quantity'     => $qty,
                    'subtotal'     => $lineTotal,
                    'created_at'   => $createdAt,
                    'updated_at'   => $createdAt,
                ]);
            }

            $totalAmount = $orderSubtotal + 50.00;
            $order->update([
                'subtotal' => $orderSubtotal,
                'total'    => $totalAmount,
            ]);

            if (in_array($status, [OrderStatus::DELIVERED, OrderStatus::SHIPPED])) {
                Payment::create([
                    'order_id'         => $order->id,
                    'method'           => fake()->randomElement(['card', 'cash_on_delivery']),
                    'status'           => 'successful',
                    'amount'           => $totalAmount,
                    'gateway'          => 'paymob',
                    'transaction_id'   => strtoupper(fake()->bothify('TXN-########')),
                    'gateway_order_id' => (string) fake()->randomNumber(8),
                    'paid_at'          => $createdAt,
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);
            }
        }
    }
}
