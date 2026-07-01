<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Event $event)
    {
        $this->authorizeEvent($event);
        $tickets = $event->tickets()->orderBy('sort_order')->get();
        return view('admin.events.tickets', compact('event', 'tickets'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeEvent($event);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:free,paid',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'sales_start' => 'nullable|date',
            'sales_end' => 'nullable|date|after_or_equal:sales_start',
            'is_active' => 'sometimes',
        ]);
        
        $validated['organization_id'] = $event->organization_id;
        $validated['is_active'] = $request->has('is_active');
        
        // Ensure free tickets have price 0
        if ($validated['type'] === 'free') {
            $validated['price'] = 0;
        }

        $event->tickets()->create($validated);

        return redirect()->back()->with('status', 'Ticket created successfully.');
    }

    public function destroy(Event $event, Ticket $ticket)
    {
        $this->authorizeEvent($event);
        if ($ticket->event_id !== $event->id) abort(404);
        
        $ticket->delete();
        return redirect()->back()->with('status', 'Ticket deleted successfully.');
    }

    protected function authorizeEvent(Event $event)
    {
        if ($event->organization_id !== app('current_organization')->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}