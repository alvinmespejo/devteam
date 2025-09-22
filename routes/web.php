<?php

use App\Http\Controllers\v1\ProductSyncController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductSyncController::class, 'sync']);
