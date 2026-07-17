<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'public/home')->name('home');
Route::inertia('sobre', 'public/about')->name('about');
Route::inertia('contato', 'public/contact')->name('contact');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/onboarding.php';
require __DIR__.'/team.php';
