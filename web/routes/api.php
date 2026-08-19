<?php

use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\ApiDocsController;
use Illuminate\Support\Facades\Route;

Route::get('/docs', [ApiDocsController::class, 'ui'])->name('api.docs');
Route::get('/openapi.yaml', [ApiDocsController::class, 'spec'])->name('api.openapi');

Route::middleware(['auth:sanctum', 'seller.api'])->group(function () {
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::post('/products', [ProductApiController::class, 'store']);
    Route::get('/products/{id}', [ProductApiController::class, 'show']);
    Route::put('/products/{id}', [ProductApiController::class, 'update']);
    Route::delete('/products/{id}', [ProductApiController::class, 'destroy']);

    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{orderNumber}', [OrderApiController::class, 'show']);
});
