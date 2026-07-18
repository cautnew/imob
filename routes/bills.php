<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\BillPdfController;
use App\Http\Controllers\BillTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('boletos', [BillController::class, 'index'])->name('bills.index');
    Route::get('boletos/novo', [BillController::class, 'create'])->name('bills.create');
    Route::post('boletos', [BillController::class, 'store'])->name('bills.store');
    Route::get('boletos/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::get('boletos/{bill}/editar', [BillController::class, 'edit'])->name('bills.edit');
    Route::put('boletos/{bill}', [BillController::class, 'update'])->name('bills.update');
    Route::delete('boletos/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    Route::patch('boletos/{bill}/situacao', [BillController::class, 'updateStatus'])->name('bills.status.update');

    Route::post('boletos/{bill}/pdf', [BillPdfController::class, 'store'])->name('bills.pdf.store');
    Route::get('boletos/{bill}/download', [BillPdfController::class, 'download'])->name('bills.download');

    Route::post('boletos/{bill}/lancamentos', [BillTransactionController::class, 'store'])->name('bill-transactions.store');
    Route::delete('boletos/{bill}/lancamentos/{transaction}', [BillTransactionController::class, 'destroy'])->name('bill-transactions.destroy');
});
