<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'venue_name' => $this->venue_name,
            'venue_address' => $this->venue_address,
            'online_url' => $this->online_url,
            'hero_image_url' => $this->hero_image ? Storage::disk('public')->url($this->hero_image) : null,
            'capacity' => $this->capacity,
            'refund_policy' => $this->refund_policy,
            'terms' => $this->terms,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'attendees_count' => $this->whenCounted('attendees'),
            'orders_count' => $this->whenCounted('orders'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
