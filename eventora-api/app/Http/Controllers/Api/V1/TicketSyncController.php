<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketSyncController extends Controller
{
    /**
     * GET /api/v1/organizer/{orgId}/events/{eventId}/tickets-sync
     * Returns minimal ticket data for offline database
     */
    public function index(Request $request, $orgId, $eventId)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($eventId);

        $attendees = $event->attendees()
            ->select('id', 'event_id', 'ticket_number', 'qr_code', 'name', 'status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendees,
        ]);
    }

    /**
     * POST /api/v1/organizer/{orgId}/events/{eventId}/tickets-sync
     * Syncs offline scanned tickets back to the server
     */
    public function sync(Request $request, $orgId, $eventId)
    {
        $organization = app('current_organization');
        $event = $organization->events()->findOrFail($eventId);

        $request->validate([
            'scanned_tickets' => 'required|array',
            'scanned_tickets.*.qr_code' => 'required|string',
            'scanned_tickets.*.scanned_at' => 'required|date',
        ]);

        $scannedTickets = $request->scanned_tickets;
        $user = $request->user();

        $syncedCount = 0;
        $failedTickets = [];

        DB::beginTransaction();
        try {
            foreach ($scannedTickets as $scan) {
                $attendee = Attendee::where('event_id', $event->id)
                    ->where('qr_code', $scan['qr_code'])
                    ->lockForUpdate()
                    ->first();

                if ($attendee && $attendee->status === 'confirmed') {
                    $attendee->update([
                        'status' => 'checked_in',
                        'checked_in_at' => $scan['scanned_at'],
                    ]);

                    DB::table('checkin_logs')->insert([
                        'attendee_id' => $attendee->id,
                        'event_id' => $event->id,
                        'organization_id' => $organization->id,
                        'checked_in_by' => $user->id,
                        'action' => 'checkin',
                        'method' => 'qr_scan',
                        'device_info' => 'Offline Sync - ' . $request->header('User-Agent'),
                    ]);

                    $syncedCount++;
                } else {
                    $failedTickets[] = $scan['qr_code'];
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully synced {$syncedCount} tickets.",
            'failed_tickets' => $failedTickets,
        ]);
    }
}
