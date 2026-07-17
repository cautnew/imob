<?php

use App\Http\Controllers\Onboarding\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('primeiro-acesso', [CompanyController::class, 'edit'])->name('onboarding.edit');
    Route::put('primeiro-acesso', [CompanyController::class, 'update'])->name('onboarding.update');
});
