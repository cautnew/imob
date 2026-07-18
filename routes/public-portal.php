<?php

use App\Http\Controllers\PublicPortal\CompanyLandingController;
use App\Http\Controllers\PublicPortal\ComparisonController;
use App\Http\Controllers\PublicPortal\FavoriteController;
use App\Http\Controllers\PublicPortal\PropertyController;
use App\Http\Controllers\PublicPortal\SearchController;
use App\Http\Controllers\PublicPortal\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');

// Public property portal, one per company, resolved from the {companySlug}
// segment (see ResolvePublicTenant). Registered last: this single-segment
// wildcard prefix must never shadow any literal route registered above it.
Route::prefix('{companySlug}')->middleware('public.tenant')->name('public.')->group(function () {
    Route::get('/', [CompanyLandingController::class, 'show'])->name('home');

    Route::get('imoveis', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('imoveis/busca', [SearchController::class, 'show'])->name('search');
    Route::get('imoveis/{propertySlug}', [PropertyController::class, 'show'])->name('properties.show');

    Route::get('favoritos', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('favoritos/{propertySlug}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('favoritos/{propertySlug}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    Route::get('comparacao', [ComparisonController::class, 'index'])->name('comparison.index');
    Route::post('comparacao/{propertySlug}', [ComparisonController::class, 'store'])->name('comparison.store');
    Route::delete('comparacao/{propertySlug}', [ComparisonController::class, 'destroy'])->name('comparison.destroy');
});
