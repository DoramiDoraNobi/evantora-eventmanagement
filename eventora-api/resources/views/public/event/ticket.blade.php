<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket: {{ $attendee->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex justify-center py-10 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="px-8 py-6 text-center text-white relative" style="background-color: {{ $attendee->event->organization->primary_color }}">
            <h1 class="text-2xl font-bold leading-tight">{{ $attendee->event->title }}</h1>
            <p class="mt-2 text-sm opacity-90">{{ \Carbon\Carbon::parse($attendee->event->start_date)->format('D, M d Y - g:i A') }}</p>
            
            <!-- Ticket notch left -->
            <div class="absolute -left-4 -bottom-4 w-8 h-8 bg-gray-100 rounded-full"></div>
            <!-- Ticket notch right -->
            <div class="absolute -right-4 -bottom-4 w-8 h-8 bg-gray-100 rounded-full"></div>
        </div>
        
        <!-- Divider -->
        <div class="border-b-2 border-dashed border-gray-300"></div>

        <!-- Body -->
        <div class="p-8 text-center relative">
            <!-- Ticket notch top left -->
            <div class="absolute -left-4 -top-4 w-8 h-8 bg-gray-100 rounded-full"></div>
            <!-- Ticket notch top right -->
            <div class="absolute -right-4 -top-4 w-8 h-8 bg-gray-100 rounded-full"></div>

            <div class="mb-6">
                <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">Attendee</div>
                <div class="text-xl font-bold text-gray-900 mt-1">{{ $attendee->name }}</div>
            </div>
            
            <div class="mb-8">
                <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">Ticket Type</div>
                <div class="text-lg font-semibold text-gray-800 mt-1">{{ $attendee->ticket->name }}</div>
            </div>

            <div class="flex justify-center mb-6">
                <div class="p-4 bg-white border-2 border-gray-100 rounded-xl inline-block shadow-sm">
                    {!! QrCode::size(200)->generate(route('public.ticket.verify', $attendee->qr_code)) !!}
                </div>
            </div>
            
            <div class="text-xs text-gray-400 font-mono tracking-widest">
                {{ $attendee->ticket_number }}
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 text-center text-xs text-gray-500 border-t border-gray-100">
            Powered by Eventora
        </div>
    </div>
</body>
</html>