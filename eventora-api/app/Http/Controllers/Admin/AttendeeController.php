<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AttendeeController extends Controller
{
    /**
     * Display a listing of the attendees for the given event.
     */
    public function index(Event $event)
    {
        // Ensure user can access this event's attendees
        $organization = auth()->user()->organizations()->first();
        if ($event->organization_id !== $organization->id) {
            abort(403, 'Unauthorized action.');
        }

        $attendees = $event->attendees()
            ->with(['ticket', 'order'])
            ->latest()
            ->paginate(20);

        return view('admin.attendees.index', compact('event', 'attendees'));
    }

    /**
     * Export attendees to CSV.
     */
    public function export(Event $event)
    {
        $organization = auth()->user()->organizations()->first();
        if ($event->organization_id !== $organization->id) {
            abort(403, 'Unauthorized action.');
        }

        $attendees = $event->attendees()->with(['ticket', 'order'])->latest()->get();

        $csvFileName = 'attendees_' . $event->slug . '_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Name', 'Email', 'Ticket Type', 'Ticket Number', 'Payment Status', 'Check-in Status', 'Checked-in At', 'Order UUID'];

        $callback = function() use($attendees, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($attendees as $attendee) {
                $row['Name']  = $attendee->name;
                $row['Email'] = $attendee->email;
                $row['Ticket Type'] = $attendee->ticket ? $attendee->ticket->name : 'N/A';
                $row['Ticket Number'] = $attendee->ticket_number;
                $row['Payment Status'] = $attendee->order ? $attendee->order->payment_status : 'N/A';
                $row['Check-in Status'] = $attendee->status;
                $row['Checked-in At'] = $attendee->checked_in_at ? $attendee->checked_in_at->format('Y-m-d H:i:s') : '';
                $row['Order UUID'] = $attendee->order ? $attendee->order->id : '';

                fputcsv($file, array($row['Name'], $row['Email'], $row['Ticket Type'], $row['Ticket Number'], $row['Payment Status'], $row['Check-in Status'], $row['Checked-in At'], $row['Order UUID']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
