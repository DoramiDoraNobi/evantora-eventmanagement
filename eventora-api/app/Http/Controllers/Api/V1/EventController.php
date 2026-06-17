<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * GET /api/v1/organizer/{orgId}/events — List org's events
     */
    public function index(Request $request, $orgId)
    {
        $organization = app('current_organization');

        $query = $organization->events()->withCount(['attendees', 'orders']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $events = $query->latest()->paginate($request->get('per_page', 15));

        return EventResource::collection($events);
    }

    /**
     * POST /api/v1/organizer/{orgId}/events — Create event
     */
    public function store(Request $request, $orgId)
    {
        $organization = app('current_organization');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'type' => 'required|in:offline,online,hybrid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:draft,published',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'online_url' => 'nullable|url|max:500',
            'capacity' => 'nullable|integer|min:1',
            'refund_policy' => 'nullable|string',
            'terms' => 'nullable|string',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('hero_image')) {
            $file = $request->file('hero_image');
            $filename = uniqid('hero_') . '.webp';
            $path = 'events/' . $filename;

            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->decode($file->getPathname());
            $image->scaleDown(width: 1920);
            $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(80));
            Storage::disk('public')->put($path, $encoded->toString());

            $validated['hero_image'] = $path;
        }

        $validated['slug'] = Str::slug($validated['title'] . '-' . uniqid());

        $event = $organization->events()->create($validated);

        return (new EventResource($event->loadCount(['attendees', 'orders'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/organizer/{orgId}/events/{id} — Event detail
     */
    public function show(Request $request, $orgId, $id)
    {
        $organization = app('current_organization');

        $event = $organization->events()
            ->with(['tickets'])
            ->withCount(['attendees', 'orders'])
            ->findOrFail($id);

        return new EventResource($event);
    }

    /**
     * PUT /api/v1/organizer/{orgId}/events/{id} — Update event
     */
    public function update(Request $request, $orgId, $id)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'type' => 'sometimes|in:offline,online,hybrid',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'timezone' => 'sometimes|string|max:50',
            'status' => 'sometimes|in:draft,published,cancelled,archived',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'online_url' => 'nullable|url|max:500',
            'capacity' => 'nullable|integer|min:1',
            'refund_policy' => 'nullable|string',
            'terms' => 'nullable|string',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('hero_image')) {
            if ($event->hero_image) {
                Storage::disk('public')->delete($event->hero_image);
            }
            $file = $request->file('hero_image');
            $filename = uniqid('hero_') . '.webp';
            $path = 'events/' . $filename;

            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->decode($file->getPathname());
            $image->scaleDown(width: 1920);
            $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(80));
            Storage::disk('public')->put($path, $encoded->toString());

            $validated['hero_image'] = $path;
        }

        $event->update($validated);

        return new EventResource($event->fresh()->loadCount(['attendees', 'orders']));
    }

    /**
     * DELETE /api/v1/organizer/{orgId}/events/{id} — Delete event
     */
    public function destroy(Request $request, $orgId, $id)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }
}
