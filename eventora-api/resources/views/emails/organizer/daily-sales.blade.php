<x-mail::message>
# Halo Tim {{ $organization->name }},

Berikut adalah ringkasan penjualan tiket untuk tanggal **{{ $date }}**.

**Total Keseluruhan:**
- **Tiket Terjual:** {{ $totalTickets }} tiket
- **Pendapatan:** {{ strtoupper($organization->currency) }} {{ number_format($totalSales, 2) }}

---

### Rincian Per Acara:

@foreach($eventsSummary as $event)
**{{ $event['title'] }}**
- Terjual: {{ $event['tickets'] }} tiket
- Pendapatan: {{ strtoupper($organization->currency) }} {{ number_format($event['revenue'], 2) }}
@endforeach

<br>
Untuk melihat rincian pemesanan, silakan masuk ke Dasbor Penyelenggara.

<x-mail::button :url="route('dashboard')">
Buka Dashboard
</x-mail::button>

Semoga hari Anda menyenangkan!<br>
{{ config('app.name') }}
</x-mail::message>
