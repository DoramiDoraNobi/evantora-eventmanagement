<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class TicketLookupController extends Controller
{
    public function showForm()
    {
        return view('buyer.lookup');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $orders = Order::with(['event.organization', 'attendees.ticket'])
            ->where('buyer_email', $request->email)
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($orders->isEmpty()) {
            return back()->with('error', 'No tickets found for this email address.');
        }

        return view('buyer.lookup-results', compact('orders', 'request'));
    }
}
