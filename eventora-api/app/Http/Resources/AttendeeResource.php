<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'qr_code' => $this->qr_code,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'checked_in_at' => $this->checked_in_at,
            'ticket' => new TicketResource($this->whenLoaded('ticket')),
            'order' => new OrderResource($this->whenLoaded('order')),
            'event' => new EventResource($this->whenLoaded('event')),
        ];
    }
}
