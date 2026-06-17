<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Event;
use App\Models\Organization;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    /**
     * GET /api/v1/events — Browse published events
     */
    public function index(Request $request)
    {
        $query = Event::with(['organization', 'tickets' => fn($q) => $q->where('is_active', true)])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->whereHas('organization', fn($q) => $q->where('is_active', true));

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('short_description', 'like', "%{$request->search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->orderBy('start_date', 'asc')->paginate($request->get('per_page', 15));

        return EventResource::collection($events);
    }

    /**
     * GET /api/v1/events/{slug} — Event detail with tickets
     */
    public function show($slug)
    {
        $event = Event::with([
                'organization',
                'tickets' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereHas('organization', fn($q) => $q->where('is_active', true))
            ->withCount('attendees')
            ->firstOrFail();

        return new EventResource($event);
    }

    /**
     * GET /api/v1/organizations/{slug} — Organization public profile + events
     */
    public function organizationProfile(Request $request, $slug)
    {
        $organization = Organization::where('slug', $slug)
            ->where('is_active', true)
            ->withCount('events')
            ->firstOrFail();

        $events = $organization->events()
            ->with(['tickets' => fn($q) => $q->where('is_active', true)])
            ->where('status', 'published')
            ->orderBy('start_date', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'organization' => new OrganizationResource($organization),
            'events' => EventResource::collection($events),
        ]);
    }
}
