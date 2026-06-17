<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * GET /api/v1/organizer/{orgId}/events/{eventId}/tickets
     */
    public function index(Request $request, $orgId, $eventId)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($eventId);

        $tickets = $event->tickets()->orderBy('sort_order')->get();

        return TicketResource::collection($tickets);
    }

    /**
     * POST /api/v1/organizer/{orgId}/events/{eventId}/tickets
     */
    public function store(Request $request, $orgId, $eventId)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($eventId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:free,paid',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'max_per_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['organization_id'] = $organization->id;

        if ($validated['type'] === 'free') {
            $validated['price'] = 0;
        }

        $ticket = $event->tickets()->create($validated);

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * DELETE /api/v1/organizer/{orgId}/events/{eventId}/tickets/{id}
     */
    public function destroy(Request $request, $orgId, $eventId, $id)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($eventId);
        $ticket = $event->tickets()->findOrFail($id);

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully.']);
    }
}
