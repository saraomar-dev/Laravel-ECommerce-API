<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $finalPrice = $this->price - ($this->price * $this->discount / 100);
        return [
            'id' => $this->id,
            'subcategory_id' => $this->subcategory_id,
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'is_in_stock' => $this->stock > 0,
            'status' =>  $this->when(
                auth()->check() && auth()->user()->role === 'admin',
                $this->status
            ),
            'sku' => $this->when(
                auth()->check() && auth()->user()->role === 'admin',
                $this->sku
            ),
            'cover_image' => $this->cover_image
                ? asset('storage/' . $this->cover_image)
                : null,
            'price' => $this->price,
            'stock' => $this->stock,
            'discount' => $this->when(
                $this->discount > 0,
                $this->discount
            ),
            'final_price' => $this->when(
                $this->discount > 0,
                $finalPrice
            ),
        ];
    }
}
