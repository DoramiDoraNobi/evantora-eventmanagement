<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\AttendeeResource;
use App\Models\Order;
use App\Models\Attendee;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    /**
     * GET /api/v1/buyer/orders — List buyer's orders
     */
    public function orders(Request $request)
    {
        $user = $request->user();

        $orders = Order::with(['event.organization', 'attendees.ticket'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('buyer_email', $user->email);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * GET /api/v1/buyer/orders/{orderNumber} — Single order detail
     */
    public function orderDetail(Request $request, $orderNumber)
    {
        $user = $request->user();

        $order = Order::with(['event.organization', 'attendees.ticket', 'payments'])
            ->where('order_number', $orderNumber)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('buyer_email', $user->email);
            })
            ->firstOrFail();

        return new OrderResource($order);
    }

    /**
     * GET /api/v1/buyer/tickets — Flat list of all buyer's attendee tickets
     */
    public function tickets(Request $request)
    {
        $user = $request->user();

        $attendees = Attendee::with(['ticket', 'event.organization'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('buyer_email', $user->email);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return AttendeeResource::collection($attendees);
    }
}
