<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartitemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id'=> $this->cart_id,
            'product_id'=> $this->product_id,
            'quantity'=> $this->quantity,
            'price'=> $this->price,
        ];
    }
}
