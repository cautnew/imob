<?php

use App\Http\Controllers\LeaseDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('locacoes/{lease}/documentos', [LeaseDocumentController::class, 'store'])->name('lease-documents.store');
    Route::delete('locacoes/{lease}/documentos/{document}', [LeaseDocumentController::class, 'destroy'])->name('lease-documents.destroy');
});
