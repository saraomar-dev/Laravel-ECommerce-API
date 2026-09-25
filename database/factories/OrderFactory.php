<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => \App\Models\User::factory(),
            'total'      => 0, // سيتم حسابه ديناميكياً من عناصر الطلب
            'status'     => fake()->randomElement(['delivered', 'delivered', 'shipped', 'pending']),
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}
