<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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

            'status' => $this->status,

            'subtotal' => $this->subtotal,
            'shipping_fee' => $this->shipping_fee,
            'discount' => $this->discount,
            'total' => $this->total,
            'currency' => $this->currency,

            'shipping_address' => $this->shipping_address,

            'placed_at' => $this->placed_at,
            'cancelled_at' => $this->cancelled_at,

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),

            'payments' => PaymentResource::collection(
                $this->whenLoaded('payments')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
