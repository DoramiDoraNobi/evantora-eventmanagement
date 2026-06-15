<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success - {{ $order->event->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 flex items-center justify-center min-h-screen">
    <div class="max-w-2xl w-full mx-4 bg-white rounded-2xl shadow-lg p-8 text-center border-t-8" style="border-top-color: {{ $order->event->organization->primary_color }}">
        @if($order->status === 'paid')
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Order Confirmed!</h1>
            <p class="text-gray-600 mb-8">Thank you for registering. Order #{{ $order->order_number }}</p>
            
            <div class="text-left bg-gray-50 rounded-xl p-6 border border-gray-100 mb-8">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Your Tickets</h3>
                <div class="space-y-4">
                    @foreach($order->attendees as $attendee)
                    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                        <div>
                            <div class="font-bold text-gray-900">{{ $attendee->name }}</div>
                            <div class="text-sm text-gray-500">{{ $attendee->ticket->name }}</div>
                        </div>
                        <a href="{{ route('public.ticket.download', $attendee->ticket_number) }}" target="_blank" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">View E-Ticket</a>
                    </div>
                    @endforeach
                </div>
            </div>
        @elseif($order->status === 'pending')
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Payment Pending</h1>
            <p class="text-gray-600 mb-8">We are waiting for your payment confirmation for Order #{{ $order->order_number }}.</p>
            <p class="text-sm text-gray-500 mb-8">If you have completed the payment, please wait a moment and refresh this page. Tickets will be available once payment is confirmed.</p>
        @else
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Order Failed</h1>
            <p class="text-gray-600 mb-8">Sorry, your payment for Order #{{ $order->order_number }} could not be processed or has been cancelled.</p>
        @endif
        
        <a href="{{ route('public.event.show', ['organizationSlug' => $order->event->organization->slug, 'eventSlug' => $order->event->slug]) }}" class="text-indigo-600 font-medium hover:underline">Back to Event Page</a>
    </div>
</body>
</html>