<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\PublicEventController::class, 'index'])->name('public.home');

Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Organization Routes
    Route::get('/organization', [\App\Http\Controllers\Admin\OrganizationController::class, 'edit'])->name('organization.edit')->middleware('tenant.role:owner,admin');
    Route::patch('/organization', [\App\Http\Controllers\Admin\OrganizationController::class, 'update'])->name('organization.update')->middleware('tenant.role:owner,admin');

    // Editor Image Upload
    Route::post('/upload-image', [\App\Http\Controllers\Admin\ImageUploadController::class, 'upload'])->name('upload.image')->middleware('tenant.role');

    
    // Event Routes
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class)->middleware('tenant.role:owner,admin,event_manager');
    Route::get('events/{event}/scanner', [\App\Http\Controllers\Admin\EventController::class, 'scanner'])->name('events.scanner')->middleware('tenant.role:owner,admin,event_manager,checkin_staff');
    Route::get('events/{event}/attendees', [\App\Http\Controllers\Admin\AttendeeController::class, 'index'])->name('events.attendees.index')->middleware('tenant.role:owner,admin,event_manager');
    Route::get('events/{event}/attendees/export', [\App\Http\Controllers\Admin\AttendeeController::class, 'export'])->name('events.attendees.export')->middleware('tenant.role:owner,admin,event_manager');
    Route::get('events/{event}/tickets', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('events.tickets.index')->middleware('tenant.role:owner,admin,event_manager');
    Route::post('events/{event}/tickets', [\App\Http\Controllers\Admin\TicketController::class, 'store'])->name('events.tickets.store')->middleware('tenant.role:owner,admin,event_manager');
    Route::delete('events/{event}/tickets/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'destroy'])->name('events.tickets.destroy')->middleware('tenant.role:owner,admin,event_manager');
    
    // Stripe Connect Routes
    Route::get('settings/stripe/connect', [\App\Http\Controllers\Admin\StripeConnectController::class, 'connect'])->name('stripe.connect');
    Route::get('settings/stripe/callback', [\App\Http\Controllers\Admin\StripeConnectController::class, 'callback'])->name('stripe.callback');
    Route::post('settings/stripe/disconnect', [\App\Http\Controllers\Admin\StripeConnectController::class, 'disconnect'])->name('stripe.disconnect');

    // Team Routes
    Route::get('/organization/team', [\App\Http\Controllers\Admin\TeamController::class, 'index'])->name('team.index')->middleware('tenant.role:owner,admin');
    Route::post('/organization/team', [\App\Http\Controllers\Admin\TeamController::class, 'store'])->name('team.store')->middleware('tenant.role:owner,admin');

    // Coupon Routes
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->middleware('tenant.role:owner,admin,event_manager');

    // Finance & Payout Routes
    Route::get('/organization/finance', [\App\Http\Controllers\Admin\FinanceController::class, 'index'])->name('finance.index')->middleware('tenant.role:owner,admin');
    Route::post('/organization/finance/payout', [\App\Http\Controllers\Admin\FinanceController::class, 'store'])->name('finance.store')->middleware('tenant.role:owner,admin');
});

// Super Admin Routes
Route::middleware(['auth', 'verified', 'superadmin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tenants', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'index'])->name('tenants.index');
    Route::post('/tenants/{tenant}/toggle', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'toggleStatus'])->name('tenants.toggle');

    // Categories CRUD
    Route::resource('categories', \App\Http\Controllers\SuperAdmin\CategoryController::class)->except(['show']);

    // Global Settings
    Route::get('/settings', [\App\Http\Controllers\SuperAdmin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SuperAdmin\SettingController::class, 'update'])->name('settings.update');
    // Payout Requests
    Route::resource('payouts', \App\Http\Controllers\SuperAdmin\PayoutController::class)->only(['index', 'update']);
});

// Check-in route — outside auth middleware group because fetch POST doesn't
// reliably carry session cookies through Laravel's auth middleware.
// Authentication is enforced inside the controller itself.
Route::post('events/checkin', [\App\Http\Controllers\Api\CheckinController::class, 'scan'])->name('events.checkin');

require __DIR__.'/auth.php';

// Buyer Auth (guest only)
Route::middleware('guest')->group(function() {
    Route::get('buyer/login', [\App\Http\Controllers\Buyer\BuyerAuthController::class, 'showLoginForm'])->name('buyer.login');
    Route::post('buyer/login', [\App\Http\Controllers\Buyer\BuyerAuthController::class, 'login']);
    Route::get('buyer/register', [\App\Http\Controllers\Buyer\BuyerAuthController::class, 'showRegisterForm'])->name('buyer.register');
    Route::post('buyer/register', [\App\Http\Controllers\Buyer\BuyerAuthController::class, 'register']);
});

// Buyer Dashboard (auth required)
Route::middleware('auth')->prefix('buyer')->group(function () {
    Route::get('my-tickets', [\App\Http\Controllers\Buyer\MyTicketsController::class, 'index'])->name('buyer.my-tickets');
    Route::get('my-tickets/{orderNumber}', [\App\Http\Controllers\Buyer\MyTicketsController::class, 'show'])->name('buyer.order-detail');
    Route::post('logout', [\App\Http\Controllers\Buyer\BuyerAuthController::class, 'logout'])->name('buyer.logout');
});

// Ticket Lookup (no auth)
Route::get('tickets/lookup', [\App\Http\Controllers\Buyer\TicketLookupController::class, 'showForm'])->name('tickets.lookup');
Route::post('tickets/lookup', [\App\Http\Controllers\Buyer\TicketLookupController::class, 'lookup']);

// Public Event Routes
Route::get('/e/{organizationSlug}', [\App\Http\Controllers\PublicEventController::class, 'organizationProfile'])->name('public.organization.show');
Route::get('/e/{organizationSlug}/{eventSlug}', [\App\Http\Controllers\PublicEventController::class, 'show'])->name('public.event.show');
Route::get('/e/{organizationSlug}/{eventSlug}/checkout', [\App\Http\Controllers\PublicEventController::class, 'checkout'])->name('public.event.checkout');
Route::post('/e/{organizationSlug}/{eventSlug}/checkout', [\App\Http\Controllers\PublicEventController::class, 'processCheckout'])->name('public.event.process');
Route::get('/order/{orderNumber}/success', [\App\Http\Controllers\PublicEventController::class, 'orderSuccess'])->name('public.order.success');
Route::get('/ticket/{ticketNumber}/download', [\App\Http\Controllers\PublicEventController::class, 'downloadTicket'])->name('public.ticket.download');
Route::get('/ticket/{qrCode}/verify', [\App\Http\Controllers\PublicEventController::class, 'verifyTicket'])->name('public.ticket.verify');
Route::post('stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');
