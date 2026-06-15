<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendee;
use App\Models\CheckinLog;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    public function scan(Request $request)
    {
        // Manual auth check — this route is outside the auth middleware group
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in and refresh the scanner page.'
            ], 401);
        }

        $request->validate([
            'qr_code' => 'required|string',
            'method' => 'nullable|string|in:qr_scan,manual,search,image_upload',
            'device_info' => 'nullable|string'
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

        // Find the attendee by QR code
        $attendee = Attendee::with('event', 'ticket')->where('qr_code', $qrCode)->first();

        if (!$attendee) {
            return $this->logAndRespondFailedCheckin($user, $qrCode, 'Invalid QR Code: Ticket not found', 404);
        }

        // Authorization check: Is this user allowed to check-in for this event?
        // Wait, for MVP, we check if user belongs to the event's organization
        $isAuthorized = DB::table('organization_user')
            ->where('organization_id', $attendee->organization_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAuthorized && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not have permission to scan tickets for this event.'
            ], 403);
        }

        // Check if ticket is valid for entry
        if ($attendee->status === 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket Already Used!',
                'checked_in_at' => $attendee->checked_in_at,
                'attendee' => $this->formatAttendeeResponse($attendee)
            ], 409);
        }

        if ($attendee->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Ticket Status: ' . ucfirst($attendee->status) . '. Payment might be pending or cancelled.',
                'attendee' => $this->formatAttendeeResponse($attendee)
            ], 422);
        }

        // Proceed to check-in
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

        return response()->json([
            'success' => true,
            'message' => 'Check-in Successful!',
            'attendee' => $this->formatAttendeeResponse($attendee)
        ]);
    }

    protected function formatAttendeeResponse($attendee)
    {
        return [
            'id' => $attendee->id,
            'name' => $attendee->name,
            'email' => $attendee->email,
            'ticket_number' => $attendee->ticket_number,
            'ticket_type' => $attendee->ticket->name,
            'event_title' => $attendee->event->title,
        ];
    }

    protected function logAndRespondFailedCheckin($user, $qrCode, $message, $status)
    {
        // We can't log to checkin_logs if attendee_id is missing, so we just return response.
        // In a real app, we might have an anomaly log.
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
