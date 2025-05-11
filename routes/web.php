<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Both\AuthController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/verify-email/{user_id}', [AuthController::class, 'verify'])->name('verify.email');


