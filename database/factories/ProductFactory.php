<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 100, 3000);

        return [
            'name'           => fake()->words(3, true),
            'description'    => fake()->text(150),
            'brand'          => fake()->company(),
            'status'         => fake()->randomElement(['active', 'active', 'active', 'inactive']),
            'sku'            => strtoupper(fake()->unique()->bothify('PROD-####-??')),
            'price'          => $price,
            'discount'       => fake()->randomElement([0, 0, 10, 20, 50]),
            'stock'          => fake()->numberBetween(5, 100),
            'subcategory_id' => Subcategory::factory(),
            'cover_image'    => 'https://placehold.co/600x600?text=' . urlencode(fake()->word()),
        ];
    }
}
