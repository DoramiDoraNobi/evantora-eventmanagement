<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Tiket - {{ $attendee->event->title }}</title>
    <!-- Modern Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .animate-bounce-slow {
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% {
                transform: translateY(-5%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }
            50% {
                transform: translateY(0);
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }
    </style>
</head>
<body class="text-gray-900 antialiased min-h-screen flex flex-col justify-between">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-100 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
            <span class="font-extrabold text-lg text-indigo-600">Eventora Ticket Verification</span>
            @auth
                <span class="text-sm text-gray-500">Logged in as: <strong class="text-gray-700">{{ auth()->user()->name }}</strong></span>
            @else
                <a id="btn-login-organizer" href="{{ route('login') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Login Penyelenggara</a>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4 py-12">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden transform hover:scale-[1.01] transition-all duration-300">
            <!-- Top Color Stripe -->
            <div class="h-3 w-full" style="background-color: {{ $attendee->event->organization->primary_color }}"></div>
            
            <div class="p-8 text-center">
                <!-- Status Badge and Icon -->
                @if($checkinSuccess)
                    <!-- Success Check-in -->
                    <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner animate-pulse">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-800 uppercase tracking-wider">Check-in Berhasil</span>
                    <h2 class="text-2xl font-black mt-4 text-green-600">Akses Diterima!</h2>
                    <p class="text-gray-600 text-sm mt-2">Selamat datang di event, tiket berhasil diverifikasi.</p>

                @elseif($attendee->status === 'checked_in')
                    <!-- Already Checked-in -->
                    <div class="w-20 h-20 bg-yellow-50 text-yellow-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner animate-bounce-slow">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 uppercase tracking-wider">Tiket Sudah Digunakan</span>
                    <h2 class="text-2xl font-black mt-4 text-yellow-600">Peringatan Duplikasi</h2>
                    <p class="text-gray-600 text-sm mt-2">Tiket ini telah digunakan untuk masuk pada:<br><strong class="text-gray-800">{{ \Carbon\Carbon::parse($attendee->checked_in_at)->format('d M Y, H:i') }} WIB</strong>.</p>

                @elseif($attendee->status === 'confirmed')
                    <!-- Valid Ticket (But not scanned via checkin user or guest scanned) -->
                    <div class="w-20 h-20 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">Tiket Valid</span>
                    <h2 class="text-2xl font-black mt-4 text-blue-600">Akses Valid</h2>
                    <p class="text-gray-600 text-sm mt-2">Tiket ini sah dan siap digunakan untuk masuk.</p>
                    
                    @if(!$isOrganizer)
                        <div class="mt-4 p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-xs text-indigo-800 text-left">
                            <strong>Penyelenggara Event?</strong> Silakan login menggunakan akun organizer Anda di browser ini untuk langsung memproses check-in tiket ketika dipindai.
                        </div>
                    @endif

                @else
                    <!-- Inactive/Cancelled/Pending -->
                    <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-800 uppercase tracking-wider">Akses Ditolak</span>
                    <h2 class="text-2xl font-black mt-4 text-red-600">Tiket Tidak Aktif</h2>
                    <p class="text-gray-600 text-sm mt-2">Status tiket saat ini: <strong class="text-gray-800">{{ ucfirst($attendee->status) }}</strong>. Tiket tidak dapat digunakan untuk masuk.</p>
                @endif

                <!-- Ticket Details Box -->
                <div class="mt-8 bg-gray-50 border border-gray-100 rounded-2xl p-5 text-left space-y-4">
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Nama Acara</div>
                        <div class="font-bold text-gray-950 mt-0.5">{{ $attendee->event->title }}</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 border-t border-gray-200/60 pt-4">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Pengunjung</div>
                            <div class="font-bold text-gray-800 mt-0.5 truncate">{{ $attendee->name }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Tipe Tiket</div>
                            <div class="font-bold text-gray-800 mt-0.5">{{ $attendee->ticket->name }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-gray-200/60 pt-4 text-xs">
                        <div>
                            <span class="text-gray-500 font-semibold uppercase tracking-wider">Nomor Tiket</span>
                            <span class="block font-mono text-gray-700 mt-0.5">{{ $attendee->ticket_number }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 font-semibold uppercase tracking-wider">Penyelenggara</span>
                            <span class="block font-bold text-gray-700 mt-0.5">{{ $attendee->event->organization->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} Eventora. Hak Cipta Dilindungi.
    </footer>
</body>
</html>
