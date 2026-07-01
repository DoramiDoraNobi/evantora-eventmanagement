<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Category;
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
        $query = Event::with(['organization', 'category', 'tickets' => fn($q) => $q->where('is_active', true)])
            ->where('status', 'published')
            ->whereHas('organization', fn($q) => $q->where('is_active', true));

        // Date filter
        $filter = $request->input('filter');
        if ($filter == 'today') {
            $query->whereDate('start_date', now()->toDateString());
        } elseif ($filter == '14_days') {
            $query->whereDate('start_date', '>=', now()->toDateString())
                  ->whereDate('start_date', '<=', now()->addDays(14)->toDateString());
        } elseif ($filter == '30_days') {
            $query->whereDate('start_date', '>=', now()->toDateString())
                  ->whereDate('start_date', '<=', now()->addDays(30)->toDateString());
        } else {
            $query->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('short_description', 'like', "%{$request->search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where(function ($q) use ($request) {
                $q->where('venue_city', 'like', "%{$request->location}%")
                  ->orWhere('venue_name', 'like', "%{$request->location}%");
            });
        }

        // Price filter
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('tickets', function ($q) use ($request) {
                $q->where('is_active', true);
                if ($request->filled('min_price')) {
                    $q->where('price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('price', '<=', $request->max_price);
                }
            });
        }

        if ($request->input('sort') == 'latest') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('start_date', 'asc');
        }

        $events = $query->paginate($request->get('per_page', 15));

        return EventResource::collection($events);
    }

    /**
     * GET /api/v1/events/{slug} — Event detail with tickets
     */
    public function show($identifier)
    {
        $event = Event::with([
                'organization',
                'category',
                'tickets' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->where(function ($q) use ($identifier) {
                $q->where('id', $identifier)->orWhere('slug', $identifier);
            })
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

    /**
     * GET /api/v1/categories — List active categories
     */
    public function categories()
    {
        $categories = Category::active()->ordered()->get(['id', 'name', 'slug', 'icon', 'color']);
        return response()->json(['data' => $categories]);
    }
}
