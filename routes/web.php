<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('app/login');
});

Route::middleware(['web', 'throttle:10'])->group(function (){
    Route::get('/login/{provider}', [AuthController::class, 'provider'])->name('login.provider');
    Route::get('/login/{provider}/callback', [AuthController::class, 'callback'])->name('login.provider.callback');
});

Route::get('reset', \App\Livewire\ResetPassword::class)
    ->middleware( 'web','throttle:10')
    ->name('app.reset');

Route::get('password', \App\Livewire\UpdatePassword::class)
    ->middleware( 'web','throttle:10')
    ->name('app.password');

Route::get('otp', \App\Livewire\DiscordOTP::class)
    ->middleware('throttle:10')
    ->name('otp');
