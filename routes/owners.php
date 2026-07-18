<?php

use App\Http\Controllers\OwnerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('proprietarios', [OwnerController::class, 'index'])->name('owners.index');
    Route::get('proprietarios/novo', [OwnerController::class, 'create'])->name('owners.create');
    Route::post('proprietarios', [OwnerController::class, 'store'])->name('owners.store');
    Route::get('proprietarios/{owner}/editar', [OwnerController::class, 'edit'])->name('owners.edit');
    Route::put('proprietarios/{owner}', [OwnerController::class, 'update'])->name('owners.update');
    Route::delete('proprietarios/{owner}', [OwnerController::class, 'destroy'])->name('owners.destroy');
});
