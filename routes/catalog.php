<?php

use App\Http\Controllers\FeatureCategoryController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\PropertyAttributeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('categorias-caracteristicas', [FeatureCategoryController::class, 'index'])->name('feature-categories.index');
    Route::get('categorias-caracteristicas/novo', [FeatureCategoryController::class, 'create'])->name('feature-categories.create');
    Route::post('categorias-caracteristicas', [FeatureCategoryController::class, 'store'])->name('feature-categories.store');
    Route::get('categorias-caracteristicas/{feature_category}/editar', [FeatureCategoryController::class, 'edit'])->name('feature-categories.edit');
    Route::put('categorias-caracteristicas/{feature_category}', [FeatureCategoryController::class, 'update'])->name('feature-categories.update');
    Route::patch('categorias-caracteristicas/{feature_category}/status', [FeatureCategoryController::class, 'toggle'])->name('feature-categories.toggle');
    Route::delete('categorias-caracteristicas/{feature_category}', [FeatureCategoryController::class, 'destroy'])->name('feature-categories.destroy');

    Route::get('caracteristicas', [FeatureController::class, 'index'])->name('features.index');
    Route::get('caracteristicas/novo', [FeatureController::class, 'create'])->name('features.create');
    Route::post('caracteristicas', [FeatureController::class, 'store'])->name('features.store');
    Route::get('caracteristicas/{feature}/editar', [FeatureController::class, 'edit'])->name('features.edit');
    Route::put('caracteristicas/{feature}', [FeatureController::class, 'update'])->name('features.update');
    Route::patch('caracteristicas/{feature}/status', [FeatureController::class, 'toggle'])->name('features.toggle');
    Route::delete('caracteristicas/{feature}', [FeatureController::class, 'destroy'])->name('features.destroy');

    Route::get('atributos', [PropertyAttributeController::class, 'index'])->name('property-attributes.index');
    Route::get('atributos/novo', [PropertyAttributeController::class, 'create'])->name('property-attributes.create');
    Route::post('atributos', [PropertyAttributeController::class, 'store'])->name('property-attributes.store');
    Route::get('atributos/{property_attribute}/editar', [PropertyAttributeController::class, 'edit'])->name('property-attributes.edit');
    Route::put('atributos/{property_attribute}', [PropertyAttributeController::class, 'update'])->name('property-attributes.update');
    Route::delete('atributos/{property_attribute}', [PropertyAttributeController::class, 'destroy'])->name('property-attributes.destroy');
});
