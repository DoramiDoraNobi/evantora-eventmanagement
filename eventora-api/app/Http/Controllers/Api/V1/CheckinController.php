<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    /**
     * POST /api/v1/organizer/{orgId}/checkin — Scan QR and check-in
     */
    public function scan(Request $request, $orgId)
    {
        $user = $request->user();
        $organization = app('current_organization');

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'qr_code' => 'required|string',
            'method' => 'nullable|string|in:qr_scan,manual,search',
            'device_info' => 'nullable|string',
        ]);

        $qrCode = $request->qr_code;

        // If scanned as a full verification URL, extract the UUID
        if (filter_var($qrCode, FILTER_VALIDATE_URL) || str_contains($qrCode, '/ticket/')) {
            $parts = explode('/', rtrim($qrCode, '/'));
            if (end($parts) === 'verify') {
                array_pop($parts);
            }
            $qrCode = end($parts);
        }

        $attendee = Attendee::with('event', 'ticket')
            ->where('qr_code', $qrCode)
            ->where('organization_id', $organization->id)
            ->first();

        if (!$attendee) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR Code: Ticket not found.',
            ], 404);
        }

        if ($attendee->event_id != $request->event_id) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket valid, but it is for a different event: ' . ($attendee->event->title ?? 'Unknown'),
            ], 403);
        }

        if ($attendee->status === 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket Already Used!',
                'checked_in_at' => $attendee->checked_in_at,
                'attendee' => $this->formatAttendee($attendee),
            ], 409);
        }

        if ($attendee->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Ticket Status: ' . ucfirst($attendee->status),
                'attendee' => $this->formatAttendee($attendee),
            ], 422);
        }

        DB::transaction(function () use ($attendee, $user, $request) {
            $attendee->update([
                'status' => 'checked_in',
                'checked_in_at' => now(),
            ]);

            DB::table('checkin_logs')->insert([
                'attendee_id' => $attendee->id,
                'event_id' => $attendee->event_id,
                'organization_id' => $attendee->organization_id,
                'checked_in_by' => $user->id,
                'action' => 'checkin',
                'method' => $request->method ?? 'qr_scan',
                'device_info' => $request->device_info ?? $request->header('User-Agent'),
            ]);
        });

        $attendee->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Check-in Successful!',
            'attendee' => $this->formatAttendee($attendee),
        ]);
    }

    /**
     * GET /api/v1/organizer/{orgId}/events/{eventId}/checkin-stats
     */
    public function stats(Request $request, $orgId, $eventId)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($eventId);

        $total = $event->attendees()->whereIn('status', ['confirmed', 'checked_in'])->count();
        $checkedIn = $event->attendees()->where('status', 'checked_in')->count();
        $pending = $event->attendees()->where('status', 'confirmed')->count();

        return response()->json([
            'event_id' => $event->id,
            'event_title' => $event->title,
            'total_attendees' => $total,
            'checked_in' => $checkedIn,
            'pending' => $pending,
            'checkin_rate' => $total > 0 ? round(($checkedIn / $total) * 100, 1) : 0,
        ]);
    }

    protected function formatAttendee($attendee): array
    {
        return [
            'id' => $attendee->id,
            'name' => $attendee->name,
            'email' => $attendee->email,
            'ticket_number' => $attendee->ticket_number,
            'ticket_type' => $attendee->ticket?->name,
            'event_title' => $attendee->event?->title,
            'status' => $attendee->status,
            'checked_in_at' => $attendee->checked_in_at,
        ];
    }
}
