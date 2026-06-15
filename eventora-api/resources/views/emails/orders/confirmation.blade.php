<x-mail::message>
# Halo {{ $order->buyer_name }},

Terima kasih atas pesanan Anda! Berikut adalah rincian pesanan Anda untuk acara **{{ $order->event->title }}**.

**Detail Pesanan:**
- **No. Pesanan:** {{ $order->order_number }}
- **Tanggal:** {{ $order->created_at->format('d M Y H:i') }}
- **Total Pembayaran:** {{ strtoupper($order->currency) }} {{ number_format($order->total, 2) }}
- **Status:** Lunas (Paid)

Anda dapat mengunduh E-Ticket (PDF) yang berisi QR Code atau melihat status tiket Anda melalui tautan di bawah ini:

<x-mail::button :url="route('tickets.lookup') . '?email=' . urlencode($order->buyer_email) . '&order_number=' . $order->order_number">
Lihat E-Ticket
</x-mail::button>

Jika tombol di atas tidak berfungsi, Anda juga bisa mengecek tiket secara mandiri di halaman "Cari Tiket" pada situs web kami dengan menggunakan email `{{ $order->buyer_email }}` dan nomor pesanan `{{ $order->order_number }}`.

Terima kasih, <br>
Penyelenggara {{ $order->event->title }} via {{ config('app.name') }}
</x-mail::message>
