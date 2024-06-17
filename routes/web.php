<?php

use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
