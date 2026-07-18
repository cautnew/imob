<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'public/home')->name('home');
Route::inertia('sobre', 'public/about')->name('about');
Route::inertia('contato', 'public/contact')->name('contact');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/onboarding.php';
require __DIR__.'/access.php';
require __DIR__.'/catalog.php';
require __DIR__.'/properties.php';
require __DIR__.'/property-media.php';
require __DIR__.'/owners.php';
require __DIR__.'/lessees.php';
require __DIR__.'/leases.php';
require __DIR__.'/lease-documents.php';
require __DIR__.'/finance.php';
require __DIR__.'/bills.php';
require __DIR__.'/notifications.php';
require __DIR__.'/portal.php';
