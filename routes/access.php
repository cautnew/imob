<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('usuarios/novo', [UserController::class, 'create'])->name('users.create');
    Route::post('usuarios', [UserController::class, 'store'])->name('users.store');
    Route::get('usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
    Route::put('usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('papeis', [RoleController::class, 'index'])->name('roles.index');
    Route::get('papeis/novo', [RoleController::class, 'create'])->name('roles.create');
    Route::post('papeis', [RoleController::class, 'store'])->name('roles.store');
    Route::get('papeis/{role}/editar', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('papeis/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('papeis/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('permissoes', [PermissionController::class, 'index'])->name('permissions.index');
});
