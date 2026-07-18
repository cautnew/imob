<?php

use App\Http\Controllers\LesseeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('inquilinos', [LesseeController::class, 'index'])->name('lessees.index');
    Route::get('inquilinos/novo', [LesseeController::class, 'create'])->name('lessees.create');
    Route::post('inquilinos', [LesseeController::class, 'store'])->name('lessees.store');
    Route::get('inquilinos/{lessee}/editar', [LesseeController::class, 'edit'])->name('lessees.edit');
    Route::put('inquilinos/{lessee}', [LesseeController::class, 'update'])->name('lessees.update');
    Route::delete('inquilinos/{lessee}', [LesseeController::class, 'destroy'])->name('lessees.destroy');
});
