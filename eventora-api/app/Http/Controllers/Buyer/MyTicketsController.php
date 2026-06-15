<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTicketsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $orders = Order::with(['event.organization', 'attendees.ticket'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('buyer_email', $user->email);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('buyer.my-tickets', compact('orders'));
    }

    public function show($orderNumber)
    {
        $user = Auth::user();
        
        $order = Order::with(['event.organization', 'attendees.ticket', 'payments'])
            ->where('order_number', $orderNumber)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('buyer_email', $user->email);
            })
            ->firstOrFail();
            
        return view('buyer.order-detail', compact('order'));
    }
}
