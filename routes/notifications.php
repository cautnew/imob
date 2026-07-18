<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::patch('notificacoes/lidas', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('notificacoes/{notification}/lida', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});
