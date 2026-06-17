<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    /**
     * GET /api/v1/organizer/{orgId}/events/{eventId}/attendees
     */
    public function index(Request $request, $orgId, $eventId)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($eventId);

        $query = $event->attendees()->with(['ticket', 'order']);

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('ticket_number', 'like', "%{$request->search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendees = $query->latest()->paginate($request->get('per_page', 20));

        return AttendeeResource::collection($attendees);
    }
}
