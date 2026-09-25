<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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

            'method' => $this->method,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            $this->mergeWhen($request->user()?->role === 'admin', [
                'gateway' => $this->gateway,
                'transaction_id' => $this->transaction_id,
                'paid_at' => $this->paid_at,

            ]),
        ];
    }
}
