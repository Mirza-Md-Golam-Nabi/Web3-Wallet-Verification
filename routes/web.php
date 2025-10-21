<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetaMaskController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/metamask/login', [MetaMaskController::class, 'login'])->name('metamask.login');
Route::post('/metamask/verify', [MetaMaskController::class, 'verify'])->name('metamask.verify');
