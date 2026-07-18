<?php

use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('categorias-financeiras', [TransactionCategoryController::class, 'index'])->name('transaction-categories.index');
    Route::get('categorias-financeiras/novo', [TransactionCategoryController::class, 'create'])->name('transaction-categories.create');
    Route::post('categorias-financeiras', [TransactionCategoryController::class, 'store'])->name('transaction-categories.store');
    Route::get('categorias-financeiras/{transaction_category}/editar', [TransactionCategoryController::class, 'edit'])->name('transaction-categories.edit');
    Route::put('categorias-financeiras/{transaction_category}', [TransactionCategoryController::class, 'update'])->name('transaction-categories.update');
    Route::delete('categorias-financeiras/{transaction_category}', [TransactionCategoryController::class, 'destroy'])->name('transaction-categories.destroy');

    Route::get('financeiro', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('financeiro/novo', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('financeiro', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('financeiro/{transaction}/editar', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('financeiro/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('financeiro/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::patch('financeiro/{transaction}/status', [TransactionController::class, 'toggleStatus'])->name('transactions.status.toggle');
});
