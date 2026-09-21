<?php

use App\Http\Controllers\Admin\Ticketing\BankAccountController;
use App\Http\Controllers\Admin\Ticketing\ChargeController;
use App\Http\Controllers\Admin\Ticketing\CustomerController;
use App\Http\Controllers\Admin\Ticketing\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\Ticketing\DrawController;
use App\Http\Controllers\Admin\Ticketing\SecurityController;
use App\Http\Controllers\Admin\Ticketing\TicketSaleController;
use App\Http\Controllers\Admin\Ticketing\TicketTypeController;
use App\Http\Controllers\Admin\Ticketing\WithdrawalController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\Ticketing\DashboardController as CustomerDashboardController;
use App\Http\Controllers\ProfileController as AccountProfileController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    $filePath = Storage::disk('public')->path($path);

    return Response::file($filePath, [
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->where('path', '.*');

Route::middleware(['guest', 'ip.security'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/dashboard', fn () => auth()->user()?->hasAnyRole(['super-admin', 'admin'])
    ? redirect()->route('admin.dashboard')
    : redirect()->route('customer.dashboard'))->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AccountProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AccountProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AccountProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active.user', 'ip.security'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::resource('ticket-types', TicketTypeController::class)->except('show');
    Route::resource('ticket-sales', TicketSaleController::class);
    Route::get('draws', [DrawController::class, 'index'])->name('draws.index');
    Route::get('draws/{draw}', [DrawController::class, 'show'])->name('draws.show');
    Route::post('draws/{draw}/winners', [DrawController::class, 'assign'])->name('draws.winners.store');
    Route::post('draws/{draw}/complete', [DrawController::class, 'complete'])->name('draws.complete');
    Route::resource('bank-accounts', BankAccountController::class)->except('show');
    Route::resource('charges', ChargeController::class);
    Route::patch('charge-payments/{payment}', [ChargeController::class, 'verifyPayment'])->name('charge-payments.verify');
    Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::patch('withdrawals/{withdrawal}', [WithdrawalController::class, 'update'])->name('withdrawals.update');
    Route::get('security', [SecurityController::class, 'index'])->name('security.index');
    Route::post('security/blocked-ips', [SecurityController::class, 'blockIp'])->name('security.ips.block');
    Route::delete('security/blocked-ips/{ip}', [SecurityController::class, 'unblockIp'])->where('ip', '.*')->name('security.ips.unblock');
    Route::post('security/trusted-devices/current', [SecurityController::class, 'trustCurrentDevice'])->name('security.devices.trust-current');
    Route::patch('security/trusted-devices/{device}/revoke', [SecurityController::class, 'revokeDevice'])->name('security.devices.revoke');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar');
        Route::post('/cover', [ProfileController::class, 'updateCover'])->name('cover');
        Route::post('/password', [ProfileController::class, 'changePassword'])->name('password');
    });

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::post('settings', [SiteSettingController::class, 'update'])->name('settings.update');
    Route::get('settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::get('reports', AdminDashboardController::class)->name('reports.index');
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });
});

Route::prefix('customer')->name('customer.')->middleware(['auth', 'active.user', 'ip.security'])->group(function () {
    Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
    Route::post('/winners/{winner}/withdraw', [CustomerDashboardController::class, 'withdraw'])->name('withdrawals.store');
    Route::post('/charges/{charge}/pay', [CustomerDashboardController::class, 'payCharge'])->name('charges.pay');
});
