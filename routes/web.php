<?php

use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok']));

// Fallback untuk Vue Router - semua request yang tidak cocok dengan route lain
Route::view('/{any?}', 'app')->where('any', '.*');

