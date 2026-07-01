# Analisis Kompetitor & Brainstorming Fitur Eventora

Berdasarkan riset mendalam terhadap aplikasi event management terkemuka di CodeCanyon (seperti Evento Bundle, MagicMate, GoEvent) dan platform SaaS event global tahun 2024-2025, berikut adalah evaluasi fitur-fitur yang **kurang/belum ada** di Eventora saat ini, ide **fitur nilai tambah (Value-Add)**, serta kompilasi **keluhan pelanggan** di aplikasi kompetitor yang harus kita hindari.

---

## 1. Fitur Esensial yang Masih Kurang (Standard Market)
Saat ini Eventora sudah memiliki MVP yang sangat baik (Ticketing, Checkout, Organization, Scanner Web). Namun, agar setara dengan *Top Sellers* di CodeCanyon, fitur-fitur berikut perlu ditambahkan:

### A. Fitur Operasional & Tiket
*   **Ticket Stock Management (Advanced):** Peringatan "Sisa Tiket" (Scarcity triggers), pembatasan tiket per user, dan fitur *Waitlist* (Daftar tunggu) jika tiket habis.
*   **Seat Mapping / Seat Selection:** Untuk event seperti konser atau teater, kemampuan memilih kursi secara visual sangat dicari.
*   **Multi-Tier Pricing:** Harga *Early Bird*, *Regular*, dan *On the Spot* yang otomatis berubah berdasarkan tanggal.
*   **Coupon / Promo Code Engine:** Validasi kupon berbasis jumlah pemakaian (misal: 100 pembeli pertama) atau tanggal *expired*. (Database sudah siap, perlu integrasi di Checkout UI).
*   **Pajak & Biaya Layanan (Tax & Service Fee):** Pengaturan *Tax* (PPN) dan *Service/Platform Fee* yang dibebankan ke pembeli atau diserap oleh organizer.

### B. Fitur Organizer (Vendor)
*   **Payout & Commission System:** Super Admin harus bisa memotong komisi (misal: 5% per tiket) dan Organizer bisa mengajukan *Withdrawal/Payout* untuk dana tiket mereka.
*   **Scanner App Offline Mode:** Kemampuan scan tiket menggunakan Mobile App tanpa koneksi internet yang stabil (sangat vital di venue padat).
*   **Export/Import Data:** Kemampuan mengekspor daftar *Attendee* ke CSV/Excel, dan import guest list eksternal.

### C. Fitur Pengguna (Buyer)
*   **Push Notifications:** Notifikasi pengingat H-1 event, perubahan jadwal, atau promo (memerlukan Firebase Cloud Messaging).
*   **Social Login (OAuth):** Login cepat dengan Google, Apple, atau Facebook untuk mengurangi *friction* saat checkout.

---

## 2. Fitur "Nilai Tambah" (Value-Add & Unique Selling Point)
Untuk membuat Eventora **lebih baik** dari script standar CodeCanyon dan bisa dijual dengan harga Premium (Elite Tier), pertimbangkan fitur-fitur ini:

### 🌟 Integrasi AI (Tren 2025)
*   **AI Event Description Generator:** Organizer cukup menulis 1 kalimat, AI (OpenAI API) otomatis membuatkan deskripsi event yang menarik dan SEO-friendly.
*   **AI Chatbot Support (Widget):** Chatbot sederhana di halaman event untuk menjawab pertanyaan dasar attendee (misal: "Apakah ada parkir?", "Jam berapa mulai?").

### 💼 Engagement & Networking (B2B / Conference)
*   **Attendee Matchmaking:** Fitur di mobile app agar sesama peserta (untuk event networking/bisnis) bisa melihat profil publik satu sama lain dan bertukar kontak via QR code (seperti fitur *LinkedIn QR*).
*   **Live Q&A dan Polling:** Fitur interaktif saat event berlangsung (terintegrasi di Mobile App).

### 📈 Marketing & Omnichannel
*   **WhatsApp Integration:** Mengirimkan e-Ticket langsung via WhatsApp (menggunakan Twilio API atau provider lokal). Tingkat *open rate* tiket di WA jauh lebih tinggi daripada email.
*   **Custom Event Landing Page Builder:** Organizer bisa mengubah warna, font, dan banner halaman event mereka sendiri tanpa coding.

---

## 3. Keluhan Pelanggan Kompetitor (Apa yang Harus Kita Hindari?)

Berdasarkan review negatif pada produk kompetitor di CodeCanyon dan platform G2/Trustpilot, ini adalah sumber komplain utama pembeli script event:

> [!WARNING]
> **1. Instalasi dan Setup yang Terlalu Rumit**
> *   **Keluhan:** "App Flutter gagal di-build," "Setup Laravel API dengan Firebase sangat membingungkan."
> *   **Solusi Eventora:** Buat dokumentasi instalasi yang *fool-proof* (anti-gagal). Sediakan script Auto-Installer berbasis GUI atau Docker compose. Hindari *hardcode* environment variables di dalam Flutter.

> [!WARNING]
> **2. Checkout Friction (Terlalu Banyak Langkah)**
> *   **Keluhan:** "Kenapa user harus buat akun dan verifikasi email dulu cuma untuk beli tiket gratis?"
> *   **Solusi Eventora:** Implementasikan sistem **Guest Checkout**. Biarkan user beli tiket hanya bermodal nama dan email. Pembuatan akun bisa dilakukan *setelah* pembayaran selesai ("Simpan kata sandi untuk melihat tiket Anda").

> [!WARNING]
> **3. Bug pada Fitur Scanner di Kondisi Lemah Sinyal**
> *   **Keluhan:** "Saat di venue basement, sinyal jelek, scanner loading terus dan antrean jadi panjang."
> *   **Solusi Eventora:** Scanner Mobile App *wajib* punya **Offline Mode**. Download database tiket ke SQLite HP saat ada WiFi, scan secara offline, lalu *sync* ke server setelah sinyal kembali.

> [!WARNING]
> **4. Kurangnya Transparansi Biaya (Hidden Fees)**
> *   **Keluhan:** "Platform tidak memberitahu ada tambahan biaya layanan sampai ke halaman bayar terakhir."
> *   **Solusi Eventora:** Tampilkan rincian harga (Subtotal, Pajak, Fee) sejak awal user memilih jumlah tiket.

> [!WARNING]
> **5. Kinerja Aplikasi (System Crash saat "War Tiket")**
> *   **Keluhan:** "Saat event populer dirilis, server down, terjadi *double-booking*."
> *   **Solusi Eventora:** Implementasikan *Pessimistic Locking* pada stok tiket di database (gunakan `lockForUpdate()` di Laravel) untuk mencegah overselling. Pertimbangkan penggunaan Redis queue untuk proses antrean pembelian.

---

## Rekomendasi Langkah Selanjutnya

1.  **Fokus Lengkapi Core MVP (Bulan Ini):** Selesaikan integrasi Payment Gateway sesungguhnya (Stripe/Xendit), sistem Kupon, dan Payout/Withdrawal komisi Organizer.
2.  **Selesaikan API & Mobile App:** Segera selesaikan fitur login dan tiket di Flutter.
3.  **Implementasi Offline Scanner:** Ini adalah kunci untuk mengalahkan kompetitor CodeCanyon, karena hampir semua script murah gagal di area ini.
