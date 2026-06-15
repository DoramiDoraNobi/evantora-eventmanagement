<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $organization = app('current_organization');
        $events = $organization->events()->latest()->paginate(10);
        return view('admin.events.index', compact('events', 'organization'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $organization = app('current_organization');
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:offline,online,hybrid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:draft,published',
            'venue_name' => 'nullable|required_if:type,offline,hybrid|string|max:255',
            'online_url' => 'nullable|required_if:type,online,hybrid|url|max:500',
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
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $encoded->toString());

            $validated['hero_image'] = $path;
        }

        $validated['slug'] = Str::slug($validated['title'] . '-' . uniqid());
        
        $event = $organization->events()->create($validated);

        return redirect()->route('events.edit', $event->id)->with('status', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        $this->authorizeEvent($event);
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:offline,online,hybrid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:draft,published,cancelled,archived',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string',
            'online_url' => 'nullable|url|max:500',
            'capacity' => 'nullable|integer|min:1',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('hero_image')) {
            if ($event->hero_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($event->hero_image);
            }
            $file = $request->file('hero_image');
            $filename = uniqid('hero_') . '.webp';
            $path = 'events/' . $filename;

            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->decode($file->getPathname());
            $image->scaleDown(width: 1920);
            $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(80));
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $encoded->toString());

            $validated['hero_image'] = $path;
        }

        if ($request->slug && $request->slug !== $event->slug) {
            $validated['slug'] = Str::slug($request->slug);
            // In a real app we'd validate uniqueness here too
        }

        $event->update($validated);

        return redirect()->back()->with('status', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);
        $event->delete();
        return redirect()->route('events.index')->with('status', 'Event deleted successfully.');
    }

    public function scanner(Event $event)
    {
        $this->authorizeEvent($event);
        return view('admin.events.scanner', compact('event'));
    }

    protected function authorizeEvent(Event $event)
    {
        if ($event->organization_id !== app('current_organization')->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}