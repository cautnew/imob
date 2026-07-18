<?php

use App\Http\Controllers\LeaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('locacoes', [LeaseController::class, 'index'])->name('leases.index');
    Route::get('locacoes/novo', [LeaseController::class, 'create'])->name('leases.create');
    Route::post('locacoes', [LeaseController::class, 'store'])->name('leases.store');
    Route::get('locacoes/{lease}', [LeaseController::class, 'show'])->name('leases.show');
    Route::get('locacoes/{lease}/editar', [LeaseController::class, 'edit'])->name('leases.edit');
    Route::put('locacoes/{lease}', [LeaseController::class, 'update'])->name('leases.update');
    Route::delete('locacoes/{lease}', [LeaseController::class, 'destroy'])->name('leases.destroy');
    Route::post('locacoes/{lease}/reajustes', [LeaseController::class, 'storeAdjustment'])->name('leases.adjustments.store');
    Route::post('locacoes/{lease}/renovacoes', [LeaseController::class, 'storeRenewal'])->name('leases.renewals.store');
    Route::patch('locacoes/{lease}/situacao', [LeaseController::class, 'updateStatus'])->name('leases.status.update');
});
