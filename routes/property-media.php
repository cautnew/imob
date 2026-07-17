<?php

use App\Http\Controllers\PropertyMediaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('imoveis/{property}/midias', [PropertyMediaController::class, 'index'])->name('property-media.index');
    Route::post('imoveis/{property}/midias', [PropertyMediaController::class, 'store'])->name('property-media.store');
    Route::post('imoveis/{property}/midias/reordenar', [PropertyMediaController::class, 'reorder'])->name('property-media.reorder');
    Route::patch('imoveis/{property}/midias/{media}', [PropertyMediaController::class, 'update'])->name('property-media.update');
    Route::post('imoveis/{property}/midias/{media}/capa', [PropertyMediaController::class, 'cover'])->name('property-media.cover');
    Route::delete('imoveis/{property}/midias/{media}', [PropertyMediaController::class, 'destroy'])->name('property-media.destroy');
});
