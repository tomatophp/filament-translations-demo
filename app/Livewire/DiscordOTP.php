<?php

namespace App\Livewire;

use App\Models\Account;
use Carbon\Carbon;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use TomatoPHP\FilamentAccounts\Events\SendOTP;
use TomatoPHP\FilamentAccounts\Livewire\Otp;

class DiscordOTP extends Otp
{
    protected static string $view = 'livewire.discord.otp';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')
                ->label('Please Enter Your Email')
                ->email()
                ->required(),
            TextInput::make('otp')
                ->label('OTP Code')
                ->numeric()
                ->maxLength(6)
                ->autocomplete('current-password')
                ->required()
                ->extraInputAttributes(['tabindex' => 2])
        ])->statePath('data');
    }

    public function mount(): void
    {
        if (auth('accounts')->check()) {
            redirect()->intended(Filament::getCurrentPanel()->getUrl());
        }
    }

    public function authenticate()
    {
        try {
            $this->rateLimit(20);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $findAccountWithEmailAndOTP = Account::query()
            ->where('email', $data['email'])
            ->where('otp_code', $data['otp'])
            ->first();


        if(!$findAccountWithEmailAndOTP){
            $this->throwFailureOtpException();
        }


        if($findAccountWithEmailAndOTP){
            $findAccountWithEmailAndOTP->otp_code = null;
            $findAccountWithEmailAndOTP->otp_activated_at = Carbon::now();
            $findAccountWithEmailAndOTP->is_active = true;
            $findAccountWithEmailAndOTP->last_login = Carbon::now();
            $findAccountWithEmailAndOTP->save();
        }

        Auth::guard('accounts')->login($findAccountWithEmailAndOTP);

        session()->regenerate();

        return redirect()->to('/app');
    }

    protected function getResendAction(): Action
    {
        return Action::make('getResendAction')
            ->requiresConfirmation()
            ->form([
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->label('Please enter your email'),
            ])
            ->link()
            ->label('Resend OTP')
            ->color('warning')
            ->action(function (array $data){
                try {
                    $this->rateLimit(5);
                } catch (TooManyRequestsException $exception) {
                    Notification::make()
                        ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                            'seconds' => $exception->secondsUntilAvailable,
                            'minutes' => ceil($exception->secondsUntilAvailable / 60),
                        ]))
                        ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                            'seconds' => $exception->secondsUntilAvailable,
                            'minutes' => ceil($exception->secondsUntilAvailable / 60),
                        ]) : null)
                        ->danger()
                        ->send();

                    return null;
                }

                $findAccountWithEmail = Account::query()
                    ->where('email', $data['email'])
                    ->first();

                if(!$findAccountWithEmail){
                   Notification::make()
                        ->title('Email Not Found')
                        ->body('Email not found in our database.')
                        ->danger()
                        ->send();
                    return;
                }

                $findAccountWithEmail->otp_code = rand(100000, 999999);
                $findAccountWithEmail->save();

                try {
                    $embeds = [];
                    $embeds['description'] = "your OTP is: ". $findAccountWithEmail->otp_code;
                    $embeds['url'] = url('/otp');

                    $params = [
                        'content' => "@" . $findAccountWithEmail->username,
                        'embeds' => [
                            $embeds
                        ]
                    ];

                    Http::post(config('services.discord.otp-webhook'), $params)->json();

                }catch (\Exception $e){
                    Notification::make()
                        ->title('Something went wrong')
                        ->danger()
                        ->send();
                }

                Notification::make()
                    ->title('OTP Send')
                    ->body('OTP code has been sent to your email address.')
                    ->success()
                    ->send();
            });
    }
}
