# Brainstorming: Apakah Eventora Perlu Mobile App (Flutter) dan API?

## Konteks & Status Saat Ini

| Komponen | Status |
|---|---|
| Laravel Web App (Backend + Blade Views) | ✅ Sudah dibangun (MVP berjalan) |
| Public Marketplace & Ticketing | ✅ Sudah berjalan |
| Organizer Dashboard (Web) | ✅ Sudah berjalan |
| Super Admin Console | ✅ Sudah berjalan |
| QR Check-in Scanner (Web) | ✅ Sudah berjalan |
| REST API | ⚠️ Minimal (hanya login + checkin scan) |
| Flutter Mobile App | ❌ Belum ada (hanya boilerplate kosong) |

---

## Analisis Kompetitor CodeCanyon

Berdasarkan riset pasar, **semua produk event management terlaris di CodeCanyon 2025-2026 menyertakan Flutter Mobile App + Laravel API:**

| Produk | Penjualan | Flutter App | Laravel API | Harga |
|---|---|---|---|---|
| **Evento Bundle** | 🔥 Tinggi | ✅ Ya | ✅ Ya | $59-99 |
| **EventPro** | 166+ | ✅ Ya | ✅ Ya | $39-59 |
| **GoEvent** | 150+ | ✅ Ya | ✅ Ya | $39-49 |
| **Eventiq** | Sedang | ✅ Ya | ✅ Ya | $49 |
| **EventRight Pro** | Sedang | ✅ Ya | ✅ Ya | $49-69 |

> [!IMPORTANT]
> **Kesimpulan Pasar:** Mobile App bukan fitur opsional di CodeCanyon — ini sudah menjadi **standar minimum** untuk kategori event management. Produk tanpa mobile app akan sangat sulit bersaing.

---

## Jawaban: Ya, Anda PERLU Mobile App + API

### Mengapa Mobile App (Flutter) Wajib?

#### 1. 🎯 Standar Pasar CodeCanyon
Pembeli CodeCanyon mengharapkan **bundle lengkap**: Web Admin + Mobile App. Produk dengan mobile app menikmati conversion rate 2-3x lebih tinggi. Tanpa mobile app, Eventora akan dianggap *incomplete* dan kalah bersaing.

#### 2. 📱 Use Case Nyata yang Membutuhkan Native App

| Use Case | Web | Mobile App | Pemenang |
|---|---|---|---|
| QR Check-in di venue | ⚠️ Bisa, tapi lambat | ✅ Cepat, native camera | **Mobile** |
| Push notification event reminder | ❌ Tidak bisa | ✅ Firebase/FCM | **Mobile** |
| Offline check-in (tanpa internet) | ❌ Tidak bisa | ✅ SQLite cache | **Mobile** |
| Attendee melihat tiket digital | ⚠️ Perlu buka browser | ✅ Instant access | **Mobile** |
| Badge scanning | ❌ | ✅ NFC/QR native | **Mobile** |
| Staff mobile dashboard | ⚠️ Responsive web | ✅ Optimized UX | **Mobile** |

#### 3. 💰 Nilai Jual Lebih Tinggi
- **Tanpa Mobile App**: Harga realistis $29-39 (script biasa)
- **Dengan Mobile App**: Harga bisa $59-99 (premium bundle)
- **Dengan Mobile App + SaaS Ready**: Bisa $99-149 (elite tier)

#### 4. 📋 PRD Mendukung
PRD Anda sendiri menyebutkan di Section 4.2 bahwa "Mobile app native" ada di *Out of Scope* untuk **tahap awal**, tapi **bukan berarti tidak perlu**. Di Section 8.4, fitur **Offline Check-in Mode** secara eksplisit membutuhkan native mobile. Di Section 8.5, **Session App-like Experience** juga memerlukan mobile app.

---

## Mengapa API Wajib Dibangun?

### 1. API adalah fondasi untuk Mobile App
Flutter App membutuhkan RESTful API untuk berkomunikasi dengan Laravel backend. Tanpa API, tidak ada mobile app.

### 2. API membuka pintu integrasi
- Webhook outbound untuk Zapier
- Third-party integrations
- Custom frontend oleh pembeli (React/Vue/Angular)

### 3. API meningkatkan nilai jual
Pembeli CodeCanyon yang mahir ingin membangun custom frontend mereka sendiri. API yang terdokumentasi baik menjadi selling point besar.

---

## Rekomendasi Strategi: Dual-Track Development

### Track 1: Perkuat API Layer (Prioritas TINGGI)
Bangun REST API yang komprehensif di atas Laravel yang sudah ada. Ini bukan memulai dari nol — hampir seluruh logic bisnis sudah ada di controller-controller Anda. Anda tinggal membuat API Controller yang memanggil logic yang sama.

```
📁 app/Http/Controllers/Api/V1/
├── AuthController.php          ← (sudah ada sebagian)
├── EventController.php         ← CRUD events
├── TicketController.php        ← Get tickets per event
├── OrderController.php         ← Checkout, order history
├── AttendeeController.php      ← List, manage attendees
├── CheckinController.php       ← (sudah ada)
├── OrganizationController.php  ← Org profile, settings
├── ProfileController.php       ← User profile
└── DashboardController.php     ← Stats/analytics
```

### Track 2: Flutter Mobile App (Prioritas SEDANG-TINGGI)
Bangun app Flutter yang mengkonsumsi API di atas. Fokus pada **3 persona utama**:

#### App 1: **Eventora Attendee App** (untuk peserta)
- Browse & discover events
- Beli tiket
- Lihat tiket digital (QR code)
- Terima push notification reminder
- Lihat agenda & speakers
- Download sertifikat

#### App 2: **Eventora Organizer App** (untuk penyelenggara)
- QR Check-in scanner (prioritas #1)
- Lihat daftar attendee real-time
- Statistik event ringkas
- Manage event status
- Offline check-in mode

> [!TIP]
> **Shortcut:** Bisa digabung jadi 1 app dengan role-based UI. Jika user login sebagai organizer/staff → tampilkan scanner + dashboard. Jika login sebagai attendee → tampilkan tiket + browse events.

---

## Arsitektur yang Diusulkan

```
┌─────────────────────────────────────────────────────┐
│                   EVENTORA ECOSYSTEM                │
│                                                     │
│  ┌──────────────┐    ┌──────────────────────────┐   │
│  │  Laravel Web  │    │    Laravel REST API       │   │
│  │  (Blade SSR)  │    │    /api/v1/*              │   │
│  │               │    │                          │   │
│  │ • Admin Panel │    │ • Auth (Sanctum)         │   │
│  │ • Public Site │    │ • Events CRUD            │   │
│  │ • Checkout    │    │ • Tickets                │   │
│  │ • SuperAdmin  │    │ • Orders/Checkout        │   │
│  └──────┬───────┘    │ • Attendees              │   │
│         │            │ • Check-in               │   │
│         │            │ • Dashboard Stats        │   │
│         │            └──────────┬───────────────┘   │
│         │                       │                   │
│         ▼                       ▼                   │
│  ┌──────────────┐    ┌──────────────────────────┐   │
│  │   Browser     │    │   Flutter Mobile App     │   │
│  │  (Desktop)    │    │   (iOS + Android)        │   │
│  └──────────────┘    │                          │   │
│                      │ • Attendee: Browse,      │   │
│                      │   Buy, View Ticket       │   │
│                      │ • Staff: QR Scanner,     │   │
│                      │   Attendee List          │   │
│                      │ • Offline Mode           │   │
│                      └──────────────────────────┘   │
└─────────────────────────────────────────────────────┘
```

---

## Timeline Estimasi

| Fase | Durasi | Deliverable |
|---|---|---|
| **API Layer** | 1-2 minggu | Full REST API dengan Sanctum auth |
| **Flutter App - Core** | 2-3 minggu | Auth, Browse Events, Buy Tickets, View QR |
| **Flutter App - Organizer** | 1-2 minggu | Scanner, Attendee List, Dashboard |
| **Flutter App - Polish** | 1 minggu | UI/UX polish, offline mode, push notif |
| **Documentation** | 1 minggu | API docs, app setup guide, screenshots |
| **Total** | **6-9 minggu** | Complete CodeCanyon-ready bundle |

---

## Kesimpulan Akhir

| Pertanyaan | Jawaban | Alasan |
|---|---|---|
| Perlu Flutter Mobile App? | ✅ **YA, WAJIB** | Standar pasar CodeCanyon, use case nyata, nilai jual 2-3x lipat |
| Perlu REST API? | ✅ **YA, WAJIB** | Fondasi untuk mobile app, integrasi, dan fleksibilitas pembeli |
| Kapan mulai? | **Setelah web app stabil** | API dulu, lalu Flutter. Jangan paralel sebelum API siap |
| Satu app atau dua? | **Satu app, dual-role** | Lebih efisien dan lebih mudah dimaintain |

> [!CAUTION]
> **Tanpa mobile app, Eventora akan sangat sulit bersaing di CodeCanyon.** Semua top seller di kategori ini sudah menyertakan Flutter app sebagai bagian dari bundle. Produk web-only di kategori event management akan dianggap *incomplete* oleh pembeli.
