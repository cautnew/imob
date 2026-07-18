<?php

use App\Http\Controllers\Portal\Auth\AuthenticatedLesseeSessionController;
use App\Http\Controllers\Portal\Auth\NewPasswordController;
use App\Http\Controllers\Portal\Auth\PasswordResetLinkController;
use App\Http\Controllers\Portal\Auth\RegisteredLesseeController;
use App\Http\Controllers\Portal\BillController;
use App\Http\Controllers\Portal\BillDownloadController;
use App\Http\Controllers\Portal\BillReceiptController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\LeaseController;
use App\Http\Controllers\Portal\NotificationController;
use App\Http\Controllers\Portal\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:lessee')->group(function () {
        Route::get('registro', [RegisteredLesseeController::class, 'create'])->name('register');
        Route::post('registro', [RegisteredLesseeController::class, 'store'])->name('register.store');

        Route::get('entrar', [AuthenticatedLesseeSessionController::class, 'create'])->name('login');
        Route::post('entrar', [AuthenticatedLesseeSessionController::class, 'store'])->name('login.store')->middleware('throttle:lessee-login');

        Route::get('senha/esqueci', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('senha/esqueci', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('senha/redefinir/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('senha/redefinir', [NewPasswordController::class, 'store'])->name('password.update');
    });

    Route::middleware('auth:lessee')->group(function () {
        Route::post('sair', [AuthenticatedLesseeSessionController::class, 'destroy'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('contratos', [LeaseController::class, 'index'])->name('leases.index');
        Route::get('contratos/{lease}', [LeaseController::class, 'show'])->name('leases.show');

        Route::get('boletos', [BillController::class, 'index'])->name('bills.index');
        Route::get('boletos/{bill}', [BillController::class, 'show'])->name('bills.show');
        Route::get('boletos/{bill}/download', [BillDownloadController::class, 'download'])->name('bills.download');
        Route::post('boletos/{bill}/comprovante', [BillReceiptController::class, 'store'])->name('bill-receipts.store');

        Route::get('pagamentos', [PaymentController::class, 'index'])->name('payments.index');

        Route::patch('notificacoes/lidas', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('notificacoes/{notification}/lida', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
});
