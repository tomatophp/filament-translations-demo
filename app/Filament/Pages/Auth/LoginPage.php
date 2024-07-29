<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Validation\ValidationException;
use TomatoPHP\FilamentAccounts\Filament\Pages\Auth\LoginAccount;

class LoginPage extends LoginAccount
{

    protected static string $view = 'auth.login';

    protected function throwFailureActivatedException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => trans('filament-accounts::messages.login.active'),
        ]);
    }
}
