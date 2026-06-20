<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AdminController, AuthController, ChatController, DeliveryController,
    DriverController, HomeController, NotificationController, PromoController,
    RatingController, RiderController, SosController, SupportController, WalletController
};

// ── Public ────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Auth ──────────────────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('phone',    [AuthController::class, 'showPhone'])->name('phone');
    Route::post('phone',   [AuthController::class, 'sendOtp'])->name('send-otp');
    Route::get('register',        [AuthController::class, 'showRegister'])->name('register');
    Route::post('register',       [AuthController::class, 'register'])->name('do-register');
    Route::post('logout',         [AuthController::class, 'logout'])->name('logout');
    Route::get('forgot-password',  [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [AuthController::class, 'submitForgotPassword'])->name('forgot-password.submit');
});

// ── Shared (logged-in) ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Promo validation (AJAX)
    Route::post('/promo/validate', [PromoController::class, 'validate'])->name('promo.validate');

    // Online drivers by vehicle type (AJAX)
    Route::get('/online-drivers', [RiderController::class, 'onlineDrivers'])->name('online-drivers');

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/',        [WalletController::class, 'index'])->name('index');
        Route::post('/topup',  [WalletController::class, 'topup'])->name('topup');
        Route::post('/withdraw',[WalletController::class, 'withdraw'])->name('withdraw');
    });

    // Chat
    Route::get('/chat/{rideId}/{lastId?}', [ChatController::class, 'poll'])->name('chat.poll');
    Route::post('/chat/{rideId}',          [ChatController::class, 'send'])->name('chat.send');

    // SOS
    Route::post('/sos', [SosController::class, 'trigger'])->name('sos.trigger');

    // Notifications
    Route::get('/notifications',       [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');

    // Support
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/',    [SupportController::class, 'index'])->name('index');
        Route::post('/',   [SupportController::class, 'store'])->name('store');
    });

    // Delivery
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/',       [DeliveryController::class, 'index'])->name('index');
        Route::get('/create', [DeliveryController::class, 'create'])->name('create');
        Route::post('/',      [DeliveryController::class, 'store'])->name('store');
    });

    // Ratings
    Route::post('/rides/{rideId}/rate', [RatingController::class, 'store'])->name('rides.rate');

    // ── Rider ─────────────────────────────────────────────────────────
    Route::prefix('rider')->name('rider.')->middleware('role:rider')->group(function () {
        Route::get('dashboard',          [RiderController::class, 'dashboard'])->name('dashboard');
        Route::get('request-ride',       [RiderController::class, 'requestRide'])->name('request-ride');
        Route::post('request-ride',      [RiderController::class, 'storeRide'])->name('store-ride');
        Route::get('active-ride',        [RiderController::class, 'activeRide'])->name('active-ride');
        Route::get('active-ride/{id}',   [RiderController::class, 'activeRide'])->name('active-ride.show');
        Route::post('accept-bid/{ride}/{bid}', [RiderController::class, 'acceptBid'])->name('accept-bid');
        Route::post('cancel-ride/{id}',  [RiderController::class, 'cancelRide'])->name('cancel-ride');
        Route::get('bids/{rideId}',      [RiderController::class, 'getBids'])->name('bids');
        Route::get('driver-location/{ride}', [RiderController::class, 'driverLocation'])->name('driver-location');
        Route::get('history',              [RiderController::class, 'history'])->name('history');
        Route::get('rides/{ride}',         [RiderController::class, 'rideDetail'])->name('ride-detail');
        Route::get('rides/{ride}/edit',    [RiderController::class, 'editRide'])->name('edit-ride');
        Route::patch('rides/{ride}',       [RiderController::class, 'updateRide'])->name('update-ride');
        Route::get('rate/{ride}',          [RiderController::class, 'showRate'])->name('rate');
        Route::post('rate/{ride}',         [RiderController::class, 'storeRate'])->name('rate.store');
        Route::get('profile',            [RiderController::class, 'profile'])->name('profile');
        Route::post('profile',           [RiderController::class, 'updateProfile'])->name('profile.update');
        Route::get('schedule',           [RiderController::class, 'schedule'])->name('schedule');
        Route::post('schedule',          [RiderController::class, 'scheduleStore'])->name('schedule.store');
        Route::get('share-trip/{ride}',  [RiderController::class, 'shareTrip'])->name('share-trip');
    });

    // ── Driver ────────────────────────────────────────────────────────
    Route::prefix('driver')->name('driver.')->middleware('role:driver')->group(function () {
        Route::get('pending',              [DriverController::class, 'pending'])->name('pending');
        Route::get('dashboard',            [DriverController::class, 'dashboard'])->name('dashboard');
        Route::get('active-ride/{id?}',    [DriverController::class, 'activeRide'])->name('active-ride');
        Route::post('update-status/{id}',  [DriverController::class, 'updateRideStatus'])->name('update-ride-status');
        Route::post('bid/{rideId}',        [DriverController::class, 'placeBid'])->name('place-bid');
        Route::post('toggle-online',       [DriverController::class, 'toggleOnline'])->name('toggle-online');
        Route::get('earnings',             [DriverController::class, 'earnings'])->name('earnings');
        Route::get('documents',            [DriverController::class, 'documents'])->name('documents');
        Route::post('documents',           [DriverController::class, 'uploadDocument'])->name('documents.upload');
        Route::get('profile',              [DriverController::class, 'profile'])->name('profile');
        Route::post('profile',             [DriverController::class, 'updateProfile'])->name('profile.update');
        Route::post('location',            [DriverController::class, 'updateLocation'])->name('location');
        Route::get('requests',             [DriverController::class, 'requests'])->name('requests');
        Route::get('rate/{ride}',          [DriverController::class, 'showRate'])->name('rate');
        Route::post('rate/{ride}',         [DriverController::class, 'storeRate'])->name('rate.store');
    });

    // ── Admin ─────────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('dashboard',                  [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('drivers',                    [AdminController::class, 'drivers'])->name('drivers');
        Route::get('drivers/{id}',               [AdminController::class, 'driverDetail'])->name('driver-detail');
        Route::post('drivers/{id}/approve',      [AdminController::class, 'approveDriver'])->name('drivers.approve');
        Route::post('drivers/{id}/reject',       [AdminController::class, 'rejectDriver'])->name('drivers.reject');
        Route::post('drivers/{id}/suspend',      [AdminController::class, 'suspendDriver'])->name('drivers.suspend');
        Route::post('documents/{id}/verify',     [AdminController::class, 'verifyDocument'])->name('documents.verify');
        Route::get('users',                      [AdminController::class, 'users'])->name('users');
        Route::post('users/{id}/ban',            [AdminController::class, 'banUser'])->name('users.ban');
        Route::post('users/{id}/unban',          [AdminController::class, 'unbanUser'])->name('users.unban');
        Route::get('rides',                      [AdminController::class, 'rides'])->name('rides');
        Route::get('sos',                        [AdminController::class, 'sos'])->name('sos');
        Route::post('sos/{id}/resolve',          [AdminController::class, 'resolveSos'])->name('sos.resolve');
        Route::get('analytics',                  [AdminController::class, 'analytics'])->name('analytics');
        Route::get('finance',                    [AdminController::class, 'finance'])->name('finance');
        Route::get('tickets',                    [AdminController::class, 'tickets'])->name('tickets');
        Route::post('tickets/{id}/reply',        [AdminController::class, 'replyTicket'])->name('tickets.reply');
        Route::get('promos',                     [AdminController::class, 'promos'])->name('promos');
        Route::post('promos',                    [AdminController::class, 'storePromo'])->name('promos.store');
        Route::post('promos/{id}/toggle',        [AdminController::class, 'togglePromo'])->name('promos.toggle');
        Route::post('broadcast',                 [AdminController::class, 'broadcast'])->name('broadcast');
        Route::get('complaints',                 [AdminController::class, 'complaints'])->name('complaints');
        Route::post('complaints/{id}/resolve',   [AdminController::class, 'resolveComplaint'])->name('complaints.resolve');
        Route::get('password-requests',              [AdminController::class, 'passwordRequests'])->name('password-requests');
        Route::post('password-requests/{id}/resolve',[AdminController::class, 'resolvePasswordRequest'])->name('password-requests.resolve');
    });
});