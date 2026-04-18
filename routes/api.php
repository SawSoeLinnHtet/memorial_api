<?php

use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\FeaturedImageController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function (): void {
    Route::apiResource('collections', CollectionController::class);
    Route::apiResource('featured-images', FeaturedImageController::class);
});
