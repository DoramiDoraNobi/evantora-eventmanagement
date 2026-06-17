<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'description' => $this->description,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'primary_color' => $this->primary_color,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'is_active' => (bool) $this->is_active,
            'stripe_account_id' => $this->when($request->user()?->getRoleInOrganization($this->id), $this->stripe_account_id),
            'role' => $this->whenPivotLoaded('organization_user', fn() => $this->pivot->role),
            'users_count' => $this->whenCounted('users'),
            'events_count' => $this->whenCounted('events'),
            'created_at' => $this->created_at,
        ];
    }
}
