<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'price' => (float) $this->price,
            'quantity' => $this->quantity,
            'quantity_sold' => $this->quantity_sold,
            'quantity_available' => $this->quantity ? max(0, $this->quantity - $this->quantity_sold) : null,
            'max_per_order' => $this->max_per_order,
            'is_active' => (bool) $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
