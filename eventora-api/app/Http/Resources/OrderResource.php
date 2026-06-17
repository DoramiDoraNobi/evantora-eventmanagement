<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'buyer_name' => $this->buyer_name,
            'buyer_email' => $this->buyer_email,
            'buyer_phone' => $this->buyer_phone,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) ($this->discount ?? 0),
            'total' => (float) $this->total,
            'status' => $this->status,
            'currency' => $this->currency,
            'paid_at' => $this->paid_at,
            'expires_at' => $this->expires_at,
            'event' => new EventResource($this->whenLoaded('event')),
            'attendees' => AttendeeResource::collection($this->whenLoaded('attendees')),
            'created_at' => $this->created_at,
        ];
    }
}
