<?php

use App\Http\Controllers\v1\ProductSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [ProductSyncController::class, 'index']);
Route::get('/products', [ProductSyncController::class, 'sync']);
