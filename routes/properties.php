<?php

use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('imoveis', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('imoveis/novo', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('imoveis', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('imoveis/{property}/editar', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('imoveis/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('imoveis/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
});
