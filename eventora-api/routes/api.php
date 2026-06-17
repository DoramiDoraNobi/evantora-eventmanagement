<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PublicEventController;
use App\Http\Controllers\Api\V1\PublicCheckoutController;
use App\Http\Controllers\Api\V1\BuyerController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\AttendeeController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\TeamController;

/*
|--------------------------------------------------------------------------
| Eventora REST API v1
|--------------------------------------------------------------------------
|
| All endpoints are prefixed with /api/v1/.
| Auth is handled via Laravel Sanctum Bearer tokens.
|
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {

    // =====================================================================
    // PUBLIC ROUTES (No Authentication Required)
    // =====================================================================

    // Auth
    Route::middleware('throttle:auth')->group(function () {
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::post('auth/register', [AuthController::class, 'register']);
    });

    // Browse Events
    Route::get('events', [PublicEventController::class, 'index']);
    Route::get('events/{slug}', [PublicEventController::class, 'show']);

    // Organization Profile
    Route::get('organizations/{slug}', [PublicEventController::class, 'organizationProfile']);

    // Order Status (by order number)
    Route::get('orders/{orderNumber}', [PublicCheckoutController::class, 'orderStatus']);

    // =====================================================================
    // AUTHENTICATED ROUTES (Sanctum Bearer Token Required)
    // =====================================================================

    Route::middleware('auth:sanctum')->group(function () {

        // --- Auth ---
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // --- Checkout (auth optional but available) ---
        Route::post('events/{slug}/checkout', [PublicCheckoutController::class, 'checkout']);

        // --- Buyer Routes ---
        Route::prefix('buyer')->group(function () {
            Route::get('orders', [BuyerController::class, 'orders']);
            Route::get('orders/{orderNumber}', [BuyerController::class, 'orderDetail']);
            Route::get('tickets', [BuyerController::class, 'tickets']);
        });

        // --- Organizer Routes ---
        Route::prefix('organizer')->group(function () {

            // Organization listing (no org context needed)
            Route::get('organizations', [OrganizationController::class, 'index']);
            Route::get('organizations/{id}', [OrganizationController::class, 'show']);
            Route::patch('organizations/{id}', [OrganizationController::class, 'update']);

            // Organization-scoped routes (requires api.org middleware)
            Route::prefix('{orgId}')->middleware('api.org')->group(function () {

                // Dashboard
                Route::get('dashboard', [DashboardController::class, 'index']);

                // Events CRUD
                Route::get('events', [EventController::class, 'index']);
                Route::post('events', [EventController::class, 'store']);
                Route::get('events/{id}', [EventController::class, 'show']);
                Route::put('events/{id}', [EventController::class, 'update']);
                Route::post('events/{id}', [EventController::class, 'update']); // For multipart/form-data
                Route::delete('events/{id}', [EventController::class, 'destroy']);

                // Tickets
                Route::get('events/{eventId}/tickets', [TicketController::class, 'index']);
                Route::post('events/{eventId}/tickets', [TicketController::class, 'store']);
                Route::delete('events/{eventId}/tickets/{ticketId}', [TicketController::class, 'destroy']);

                // Attendees
                Route::get('events/{eventId}/attendees', [AttendeeController::class, 'index']);

                // Check-in
                Route::post('checkin', [CheckinController::class, 'scan']);
                Route::get('events/{eventId}/checkin-stats', [CheckinController::class, 'stats']);

                // Team
                Route::get('team', [TeamController::class, 'index']);
                Route::post('team', [TeamController::class, 'store']);
            });
        });
    });
});
