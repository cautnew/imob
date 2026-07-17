<?php

use App\Http\Controllers\Team\MemberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('team', [MemberController::class, 'index'])->name('team.index');
    Route::post('team', [MemberController::class, 'store'])->name('team.store');
});
