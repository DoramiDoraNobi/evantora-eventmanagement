# Product Requirements Document (PRD)

## 1. Ringkasan Produk
**Nama produk sementara:** Eventora / EventSuite / EventHub

**Kategori:** Web app event management multi-tenant, siap dijual sebagai productized SaaS dan/atau script marketplace.

**Tujuan utama:** Membantu organizer membuat, menjual, mengelola, dan menganalisis event dari satu dashboard. Produk harus kuat sebagai:
1. **SaaS multi-tenant** untuk banyak organizer dalam satu platform.
2. **Script/white-label** yang bisa diinstal ulang oleh pembeli marketplace.
3. **Produk yang mudah dikustomisasi** dan didokumentasikan dengan baik.

**Value proposition:**
- Setup cepat.
- Alur kerja event end-to-end dari pembuatan event sampai post-event follow-up.
- Fokus pada fitur yang sering dicari organizer: ticketing, QR check-in, peserta, pembayaran, email/WhatsApp, sertifikat, analitik.
- Arsitektur modern dan modular agar mudah dikembangkan.

---

## 2. Masalah yang Ingin Diselesaikan
Organizer biasanya menghadapi masalah berikut:
- Event dibuat manual di banyak tools terpisah.
- Ticketing, check-in, dan komunikasi peserta tidak terhubung.
- Sulit melihat data peserta dan performa event secara menyeluruh.
- Reminder dan follow-up masih manual.
- Aplikasi event yang ada sering sulit dikustomisasi, dokumentasinya lemah, dan kurang siap untuk skala SaaS.

---

## 3. Target Pengguna
### 3.1 Persona Utama
**A. Event Organizer / EO**
- Menyelenggarakan seminar, workshop, konferensi, community meetup, kelas, dan acara komersial.
- Butuh ticketing, check-in, sponsor, dan laporan.

**B. Tim Marketing / Growth**
- Menggunakan event untuk lead generation.
- Butuh form registrasi, segmentasi peserta, email automation, dan analytics.

**C. Admin Internal Perusahaan**
- Mengelola event internal, training, onboarding, town hall, dan webinar.
- Butuh approval flow, attendee list, sertifikat, dan laporan.

**D. Agency / Reseller**
- Membeli produk untuk dipakai ulang atau dijual kembali.
- Butuh white-label, multi-client, tema, dan dokumentasi yang sangat jelas.

### 3.2 Persona Sekunder
**E. Peserta Event**
- Mendaftar event.
- Menerima tiket/QR.
- Melihat agenda.
- Check-in.
- Mengunduh sertifikat.

---

## 4. Scope Produk
### 4.1 In Scope (Versi Awal)
- Multi-tenant organization management.
- Event creation and publishing.
- Ticketing and registration.
- Payment integration.
- Attendee management.
- QR code check-in.
- Email notifications.
- WhatsApp notifications (opsional sesuai integrasi).
- Coupon / promo code.
- Speaker / agenda management.
- Certificate generation.
- Dashboard analytics.
- CMS halaman publik event.
- Role-based access control.
- Audit log dasar.
- Branding / white-label.

### 4.2 Out of Scope (Tahap Awal)
- Mobile app native.
- Marketplace vendor kompleks.
- Live streaming built-in penuh.
- Community feed social networking lengkap.
- Complex enterprise procurement workflow.

---

## 5. Prinsip Desain Produk
1. **Mobile-first** untuk halaman publik dan check-in.
2. **Admin dashboard first-class** untuk organizer.
3. **Modular**: tiap fitur bisa diaktifkan / dimatikan.
4. **Scalable**: siap menjadi SaaS multi-tenant.
5. **Marketplace-ready**: mudah diinstal, mudah didokumentasikan.
6. **Secure by default**.
7. **Fast setup**: pembeli bisa berjalan dalam waktu singkat.
8. **Low-friction UX**: hindari form panjang yang tidak perlu.

---

## 6. Product Goals
### 6.1 Business Goals
- Menjadi produk event management yang layak dijual di marketplace dan dapat dikembangkan menjadi SaaS.
- Memudahkan upsell ke paket premium.
- Mengurangi ketergantungan pada layanan pihak ketiga.

### 6.2 User Goals
- Membuat event dalam hitungan menit.
- Menjual tiket dengan pembayaran online.
- Mengelola peserta tanpa spreadsheet.
- Check-in cepat di venue.
- Mendapat laporan event secara real time.

### 6.3 Success Metrics
- Time to first event published.
- Conversion rate registrasi.
- Check-in speed per attendee.
- Payment success rate.
- Weekly active organizers.
- Retention organizer.
- Number of events created per tenant.
- Support ticket volume per install.

---

## 7. Produk dan Modul Utama

# 7.1 Authentication & Account
### Tujuan
Mengelola login aman untuk admin, staf, dan peserta.

### Fitur
- Email/password login.
- Password reset.
- Optional magic link.
- Optional social login.
- 2FA untuk admin.
- Session management.
- Remember device.
- Logout all sessions.
- Profile settings.
- Language preference.
- Time zone preference.

### Role
- Super Admin.
- Organization Owner.
- Organization Admin.
- Event Manager.
- Finance Staff.
- Check-in Staff.
- Marketing Staff.
- Speaker.
- Participant.

### Acceptance Criteria
- User hanya melihat fitur sesuai role.
- Password disimpan hash aman.
- Admin dapat mengaktifkan 2FA.

---

# 7.2 Multi-Tenant Organization Management
### Tujuan
Mendukung banyak organisasi dalam satu platform.

### Fitur
- Create organization.
- Organization profile.
- Logo, warna brand, domain/subdomain.
- Default currency.
- Default locale.
- Branding settings.
- SMTP settings.
- WhatsApp API settings.
- Staff invitation.
- Role assignment.
- Organization usage dashboard.

### Catatan Penting
- Tenant isolation harus jelas.
- Data antar organisasi tidak boleh saling terlihat.
- Fitur white-label harus bisa diaktifkan per tenant.

---

# 7.3 Event Management
### Tujuan
Membuat dan mengelola event dari awal sampai selesai.

### Fitur inti
- Create event.
- Edit event.
- Draft / publish / archived status.
- Single-day event.
- Multi-day event.
- Online event.
- Offline event.
- Hybrid event.
- Event category.
- Event tags.
- Event location.
- Venue detail.
- Map embed.
- Event timezone.
- Capacity.
- Registration deadline.
- Event visibility: public, private, unlisted.
- Event hero image.
- Banner.
- SEO metadata.
- Custom slug.
- Custom thank-you page.
- Custom registration flow.

### Event Data Fields
- Title.
- Subtitle.
- Description.
- Short description.
- Start date/time.
- End date/time.
- Time zone.
- Venue / online link.
- Organizer name.
- Organizer description.
- Contact person.
- Support email.
- Capacity.
- Ticket policy.
- Refund policy.
- Sponsor section.
- FAQ section.
- Terms and conditions.

### Acceptance Criteria
- Event dapat dipublish hanya jika field minimum terpenuhi.
- Perubahan status tercatat di audit log.
- Event publik memiliki halaman landing yang rapi.

---

# 7.4 Ticketing & Registration
### Tujuan
Mengelola pendaftaran dan penjualan tiket.

### Ticket Types
- Free ticket.
- Paid ticket.
- Early bird.
- VIP.
- Group ticket.
- Invite-only ticket.
- Promo-only ticket.

### Fitur
- Ticket CRUD.
- Seat/capacity per ticket type.
- Sales start/end schedule.
- Quantity limit per buyer.
- Waiting list.
- Coupon codes.
- Tiered pricing.
- Add-on purchase.
- Custom registration fields.
- Attendee questions.
- Ticket transfer rules.
- Refund eligibility rules.
- Ticket stock control.
- One attendee per ticket or multiple attendees per order.
- Auto-generate ticket number.
- QR code per ticket.

### Registration Flow
1. User pilih event.
2. User pilih ticket.
3. Isi data peserta.
4. Pilih add-on jika ada.
5. Checkout.
6. Terima konfirmasi dan tiket.

### Acceptance Criteria
- Registrasi langsung membuat attendee record.
- Tiket berbeda harus punya aturan stok terpisah.
- QR code unik per tiket.

---

# 7.5 Payment & Billing
### Tujuan
Menerima pembayaran tiket secara aman.

### Payment Features
- Payment gateway integration.
- Manual bank transfer.
- Invoice generation.
- Payment status tracking.
- Partial payment or deposit option.
- Refund processing.
- Tax / VAT handling.
- Discount / coupon handling.
- Transaction logs.
- Payment webhook handler.

### Recommended Payment Methods
- Stripe (Credit/debit card, global standard).
- PayPal.
- Bank transfer (manual).
- Virtual account / Local methods (optional).

### Acceptance Criteria
- Payment status update harus real time atau near real time.
- Duplicate webhook tidak boleh menggandakan order.
- Refund memengaruhi status peserta dan laporan keuangan.

---

# 7.6 Attendee Management
### Tujuan
Mengelola peserta sebagai data operasional inti.

### Fitur
- Attendee list.
- Search and filters.
- Status: registered, paid, pending, canceled, checked-in, no-show.
- Import attendee CSV.
- Export CSV / XLSX.
- Manual attendee creation.
- Notes per attendee.
- Tagging.
- Custom fields.
- Attendance history.
- Ticket ownership transfer.
- Merge duplicate attendees.
- Bulk email / WhatsApp.

### Data Peserta
- Nama lengkap.
- Email.
- Nomor WhatsApp.
- Company.
- Job title.
- City.
- Notes.
- Ticket type.
- Check-in status.
- QR ticket.

---

# 7.7 Check-in & Access Control
### Tujuan
Mempercepat proses masuk venue.

### Fitur
- QR code check-in.
- Check-in by staff.
- Check-in self-service kiosk.
- Offline-ready scanning mode.
- Search attendee by name/email.
- Duplicate scan prevention.
- Check-out optional.
- Area-based access control.
- Session-based access control.
- Badge printing support.

### UX Requirements
- Scan result harus muncul dalam 1 detik idealnya.
- Tampilan check-in harus sangat sederhana.
- Mode mobile harus optimal.

### Acceptance Criteria
- QR yang sudah dipakai tidak bisa dipakai ulang kecuali diizinkan.
- Staff hanya dapat mengakses event yang ditugaskan.

---

# 7.8 Communication & Notifications
### Tujuan
Mengirim pesan otomatis sebelum, saat, dan setelah event.

### Channel
- Email.
- WhatsApp.
- SMS optional.
- In-app notification.

### Template Otomasi
- Registration confirmation.
- Payment confirmation.
- Ticket sent.
- Payment reminder.
- Event reminder H-7/H-1/H-0.
- Check-in instructions.
- Post-event thank you.
- Certificate available.
- Event cancellation.
- Schedule update.

### Fitur
- Template editor.
- Variables / merge tags.
- Scheduling.
- Audience segment.
- Delivery logs.
- Retry failed sends.
- Unsubscribe handling.

### Merge Tag Examples
- {{first_name}}
- {{event_name}}
- {{ticket_type}}
- {{start_date}}
- {{venue_name}}
- {{checkin_url}}

---

# 7.9 Agenda, Session, Speaker, and Track Management
### Tujuan
Mendukung event yang lebih kompleks.

### Fitur
- Multiple sessions.
- Tracks / stages.
- Speaker profiles.
- Speaker bio.
- Speaker photo.
- Session title, abstract, duration.
- Session time slot.
- Session capacity.
- Session tags.
- Favorite sessions.
- Session assignment to room.
- Speaker approval workflow.

### Public Features
- Agenda page.
- Speaker page.
- Session detail page.
- Add to calendar.

---

# 7.10 Certificate Management
### Tujuan
Menghasilkan sertifikat otomatis bagi peserta.

### Fitur
- Certificate template builder.
- Certificate image/PDF output.
- Auto-generate participant name.
- Auto-generate event name and date.
- Unique certificate ID.
- Download by attendee.
- Verification page / QR verification.
- Rules by attendance or completion.

### Acceptance Criteria
- Sertifikat hanya dapat diunduh jika syarat terpenuhi.
- Sertifikat memiliki verification ID unik.

---

# 7.11 Landing Page / CMS untuk Event
### Tujuan
Menyediakan halaman publik yang menarik dan mudah diedit.

### Fitur
- Homepage event.
- About section.
- Agenda section.
- Speakers section.
- Tickets section.
- Sponsors section.
- FAQ section.
- Testimonials.
- CTA buttons.
- Custom pages.
- Footer links.
- SEO support.

### CMS Needs
- Block-based editor atau section builder sederhana.
- Reorder sections.
- Enable/disable blocks.
- Media manager.
- Page preview.

---

# 7.12 Analytics & Reporting
### Tujuan
Memberikan insight bisnis bagi organizer.

### Dashboard Metrics
- Total events.
- Total registrations.
- Total paid tickets.
- Revenue.
- Conversion rate.
- Check-in rate.
- No-show rate.
- Refund rate.
- Ticket type performance.
- Coupon performance.
- Source performance.

### Reports
- Registrations over time.
- Revenue over time.
- Attendance over time.
- Channel attribution.
- Ticket sales breakdown.
- Session attendance.
- Speaker popularity.
- Participant segmentation.

### Export
- CSV.
- XLSX.
- PDF summary.

---

# 7.13 Sponsor Management
### Tujuan
Mendukung event komersial yang sering bergantung pada sponsor.

### Fitur
- Sponsor tiers.
- Sponsor logos.
- Sponsor package.
- Sponsor placement on page.
- Sponsor contact tracking.
- Sponsor deliverables checklist.
- Sponsor report.

---

# 7.14 Feedback & Post-Event
### Tujuan
Mendapatkan feedback dan menjaga hubungan peserta.

### Fitur
- Survey builder sederhana.
- NPS rating.
- Session rating.
- Speaker rating.
- Open feedback form.
- Export feedback.
- Follow-up automation.

---

# 7.15 Support & Help Center
### Tujuan
Mengurangi beban support.

### Fitur
- Public help center.
- FAQ builder.
- Setup guide.
- Troubleshooting articles.
- Embedded video guide.
- Contact support form.
- Ticketing system optional.

---

## 8. Advanced Features / Differentiators
Ini fitur yang bisa menjadi pembeda kuat di pasar.

### 8.1 AI Features
- Generate event description.
- Generate social media copy.
- Generate email campaign drafts.
- Generate agenda draft.
- Suggest ticket pricing copy.
- Summarize attendee feedback.
- Draft post-event recap.

### 8.2 WhatsApp-Native Workflow
- WhatsApp reminder.
- Ticket delivery via WhatsApp.
- Check-in link via WhatsApp.
- Broadcast segment.
- Failed delivery retry.

### 8.3 White-Label / Reseller Mode
- Custom domain.
- Remove branding.
- Custom logo.
- Custom color theme.
- Custom email sender.
- Sub-account for clients.

### 8.4 Offline Check-in Mode
- Local cached attendee list.
- Sync after reconnect.
- Graceful offline operation.

### 8.5 Session App-like Experience
- Agenda favorit.
- Speaker follow.
- Notification reminder session.
- Personal schedule.

---

## 9. Non-Functional Requirements
### 9.1 Performance
- Dashboard should load quickly.
- Public pages optimized for SEO and speed.
- Check-in flow should be very responsive.
- Heavy reports should be paginated or queued.

### 9.2 Security
- CSRF protection.
- Input validation.
- Rate limiting.
- Password hashing.
- Role-based authorization.
- Tenant data isolation.
- Secure webhook verification.
- Audit logs for critical actions.
- File upload security.

### 9.3 Reliability
- Queue-based notification sending.
- Retry mechanism.
- Backup strategy.
- Monitoring / error logging.
- Graceful failure handling.

### 9.4 Scalability
- Support many tenants.
- Support high registration bursts.
- Support large check-in queues.
- Background jobs for report generation.

### 9.5 Accessibility
- Keyboard navigation.
- Contrast compliance.
- Screen-reader friendly labels.
- Mobile responsiveness.

---

## 10. Information Architecture
### Public Site
- Home.
- Event list.
- Event detail.
- Speaker detail.
- Agenda.
- FAQs.
- Contact.
- Login.
- Register.
- Ticket checkout.

### Admin Dashboard
- Overview.
- Events.
- Tickets.
- Attendees.
- Payments.
- Check-in.
- Communications.
- Speakers.
- Sponsors.
- Certificates.
- Analytics.
- Settings.
- Billing.
- Team management.
- Logs.

---

## 11. Key User Flows
### 11.1 Organizer Creates Event
1. Login.
2. Create organization.
3. Create event.
4. Set details.
5. Create tickets.
6. Configure payments.
7. Publish.

### 11.2 Participant Registers
1. Open event page.
2. Select ticket.
3. Enter details.
4. Pay / confirm.
5. Receive ticket and QR.

### 11.3 Check-in on Event Day
1. Staff login.
2. Open check-in screen.
3. Scan QR.
4. Verify status.
5. Mark attendee checked-in.

### 11.4 Post-Event Certificate
1. System checks attendance rule.
2. Generate certificate.
3. Notify attendee.
4. Attendee downloads file.

---

## 12. Permissions Matrix (Ringkas)
### Super Admin
- Manage all tenants.
- Billing.
- Global settings.
- Feature flags.

### Organization Owner
- Full access within tenant.
- Staff management.
- Branding.
- Payments.

### Event Manager
- Event setup.
- Attendees.
- Ticketing.
- Communications.

### Finance Staff
- View transactions.
- Refund handling.
- Export revenue.

### Check-in Staff
- Scan attendee.
- View attendee status.
- No access to finance.

### Marketing Staff
- Manage landing page.
- Email campaigns.
- Coupons.
- Reports.

---

## 13. Data Model Ringkas
### Core Entities
- User.
- Organization.
- Event.
- Ticket.
- Order.
- Payment.
- Attendee.
- CheckInLog.
- Speaker.
- Session.
- Sponsor.
- Coupon.
- Certificate.
- Notification.
- Survey.
- AuditLog.

### Relasi Utama
- Organization has many Events.
- Event has many Tickets.
- Order has many Attendees.
- Attendee belongs to Ticket and Order.
- Event has many Sessions.
- Session may belong to Speaker.
- Event has many CheckInLogs.
- Event has many Certificates.

---

## 14. Integrasi Eksternal
### Wajib / Prioritas Tinggi
- Payment gateway.
- Email service.
- WhatsApp provider.
- Storage service.
- Queue system.

### Nice to Have
- Google Calendar.
- Zoom / Meet.
- CRM integration.
- Zapier / webhook outbound.
- Analytics tools.
- Maps API.

---

## 15. Admin & Super Admin Requirements
### Super Admin Console
- Tenant management.
- Feature flag management.
- Usage overview.
- Subscription management.
- Suspended tenant handling.
- Global support tools.
- System health.

### Support Tools
- Impersonate tenant admin.
- View audit log.
- View failed jobs.
- View webhook logs.
- Clear cache / regenerate settings.

---

## 16. Monetization Model
### Option A: Marketplace Script
- One-time purchase.
- Optional paid support.
- Optional installation service.
- Paid add-ons.

### Option B: SaaS
- Free trial.
- Starter.
- Pro.
- Business.
- Enterprise.

### Upsell Opportunities
- Extra event quota.
- Extra staff seats.
- Custom domain.
- White-label.
- WhatsApp integration.
- AI credits.
- Advanced analytics.

---

## 17. Launch Plan / Release Phases
### Phase 1 — MVP
- Auth.
- Organization.
- Event CRUD.
- Ticketing.
- Registration.
- Payment.
- Email confirmation.
- QR check-in.
- Basic dashboard.

### Phase 2 — Competitive
- WhatsApp notifications.
- Certificates.
- Agenda and speakers.
- Analytics.
- Coupons.
- Import/export.
- Public CMS blocks.

### Phase 3 — Differentiation
- AI tools.
- White-label.
- Offline check-in.
- Advanced reporting.
- Sponsor tools.
- Multi-language.

### Phase 4 — Marketplace Elite
- SaaS billing.
- Multi-tenant hardening.
- Public documentation portal.
- Video tutorials.
- Demo data.
- One-click installer.

---

## 18. QA / Testing Checklist
- Login and reset password.
- Role permission checks.
- Tenant isolation tests.
- Ticket stock reduction.
- Payment webhook duplicates.
- Refund flow.
- QR invalid/used flow.
- Check-in offline simulation.
- Email send and retry.
- Certificate generation.
- Event publish validation.
- CSV import validation.
- Time zone correctness.

---

## 19. Risks
- Scope creep terlalu besar.
- Integrasi payment/WhatsApp berbeda-beda per negara.
- Tenant isolation bug berisiko tinggi.
- UX check-in yang lambat akan sangat merusak pengalaman.
- Dokumentasi yang buruk akan mengurangi nilai jual.
- Overengineering fitur AI sebelum core product solid.

---

## 20. Recommended MVP Feature Set
Ini versi yang paling aman untuk mulai dibangun:
- Multi-tenant organization.
- Event management.
- Ticketing.
- Public event page.
- Registration form.
- Payment integration.
- Email notifications.
- QR check-in.
- Attendee management.
- Basic analytics.
- Admin roles.
- Branding settings.
- Export reports.

---

## 21. Recommended Differentiators for Market Sale
Jika targetnya dijual sebagai produk unggulan, prioritaskan:
1. Clean architecture.
2. Excellent documentation.
3. White-label.
4. WhatsApp automation.
5. Offline check-in.
6. Certificate generator.
7. AI content assistant.
8. Multi-tenant SaaS readiness.

---

## 22. Definition of Done
Produk dianggap siap rilis bila:
- Bisa membuat event end-to-end.
- Bisa menerima registrasi dan pembayaran.
- Bisa check-in dengan QR.
- Bisa mengirim email notifikasi.
- Bisa menghasilkan laporan dasar.
- Aman secara role dan tenant.
- Dokumentasi setup lengkap.
- Demo site berjalan.
- Seeder demo data tersedia.

---

## 23. Product Decisions
Berdasarkan hasil diskusi, berikut adalah keputusan produk yang diambil:
- **Model Bisnis**: Dijual sebagai SaaS dan juga Script Marketplace (target utama CodeCanyon). Arsitektur akan dibangun sebagai multi-tenant SaaS agar handal untuk kedua model tersebut.
- **Payment Gateway**: Karena menargetkan CodeCanyon (pasar global), gateway prioritas utama adalah **Stripe** dan **PayPal**.
- **Bahasa**: Versi awal (MVP) akan menggunakan **Bahasa Inggris (English)** sepenuhnya.
- **WhatsApp API**: Akan disiapkan arsitektur notifikasi, bisa menggunakan unofficial gateway untuk MVP.
- **Fokus Event**: Seminar, workshop, dan komunitas.

---

## 24. Catatan Strategi Produk
Untuk pasar jualan, produk tidak cukup hanya lengkap. Yang paling penting adalah:
- rapi,
- stabil,
- mudah dipasang,
- mudah dipahami pembeli,
- dan terlihat aktif dikembangkan.

Kalau ingin menang di marketplace, dokumentasi, demo, dan kualitas UI sering sama pentingnya dengan fitur.

