<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use TomatoPHP\FilamentAccounts\Models\AccountsMeta;
use TomatoPHP\FilamentAlerts\Services\SendNotification;

class AuthController extends Controller
{
    public function provider($provider)
    {
        try {
            return Socialite::driver($provider)
                ->redirect();
        }catch (\Exception $exception){
            Notification::make()
                ->title('Error')
                ->body('Something went wrong!')
                ->danger()
                ->send();

            return redirect()->to('app/login');
        }
    }

    public function callback($provider)
    {
        try {
            $providerHasToken = config('services.'.$provider.'.client_token');
            if($providerHasToken){
                $socialUser = Socialite::driver($provider)->userFromToken($providerHasToken);
            }
            else {
                $socialUser = Socialite::driver($provider)->user();
            }

            if(auth('accounts')->user()){
                AccountsMeta::where('key', $provider . '_id')->where('value', $socialUser->id)->delete();

                $account = auth('accounts')->user();
                $account->meta($provider . '_id', $socialUser->id);
                if ($socialUser->token) {
                    $account->meta($provider . '_token', $socialUser->token);
                }
                if ($socialUser->refreshToken) {
                    $account->meta($provider . '_refresh_token', $socialUser->refreshToken);
                }

                if (isset($socialUser->attributes['avatar']) && !$account->getMedia('avatar')->first()) {
                    $account->addMediaFromUrl($socialUser->attributes['avatar'])->toMediaCollection('avatar');
                }

                Notification::make()
                    ->title('Account Connected')
                    ->body('Account connected successfully!')
                    ->success()
                    ->send();

                return redirect()->to('app');
            }
            else {
                $findUserByProvider = Account::whereHas('accountsMetas', function ($q) use ($socialUser, $provider){
                    $q->where('key', $provider . "_id")->where('value', $socialUser->id);
                })->first();

                if($findUserByProvider){
                    if(isset($socialUser->attributes['avatar']) && !$findUserByProvider->getMedia('avatar')->first()){
                        $findUserByProvider->addMediaFromUrl($socialUser->attributes['avatar'])->toMediaCollection('avatar');
                    }

                    Notification::make()
                        ->title('Account Connected')
                        ->body('Account connected successfully!')
                        ->success()
                        ->send();

                    auth('accounts')->login($findUserByProvider);
                    return redirect()->to('app');
                }
                else {
                    if($socialUser->email){
                        $findUserByEmail = Account::withTrashed()->where('email', $socialUser->email)->first();
                        if($findUserByEmail){
                            if($findUserByEmail->deleted_at){
                                $findUserByEmail->deleted_at = null;
                                $findUserByEmail->save();
                            }
                            $findUserByEmail->meta($provider . '_id', $socialUser->id);
                            if ($socialUser->token) {
                                $findUserByEmail->meta($provider . '_token', $socialUser->token);
                            }
                            if ($socialUser->refreshToken) {
                                $findUserByEmail->meta($provider . '_refresh_token', $socialUser->refreshToken);
                            }

                            if (isset($socialUser->attributes['avatar']) && !$findUserByEmail->getMedia('avatar')->first()) {
                                $findUserByEmail->addMediaFromUrl($socialUser->attributes['avatar'])->toMediaCollection('avatar');
                            }

                            Notification::make()
                                ->title('Account Connected')
                                ->body('Account connected successfully!')
                                ->success()
                                ->send();

                            auth('accounts')->login($findUserByEmail);
                            return redirect()->to('app');
                        }
                        else {
                            $account = new Account();
                            $account->name = $socialUser->name;
                            $account->email = $socialUser->email;
                            if(isset($socialUser->attributes['nickname'])){
                                $username = $socialUser->attributes['nickname'];
                            }
                            else {
                                $username = str($socialUser->name)->slug('_');
                            }
                            $checkIfUserNameExists = Account::where('username', "@" . $username)->first();
                            if($checkIfUserNameExists){
                                $username = $username . rand(1000, 9999);
                            }

                            $account->username = "@" . $username;
                            $password = Str::random(8);
                            $account->password = bcrypt($password);
                            $account->otp_activated_at = Carbon::now()->toDateTimeString();
                            $account->is_active = true;
                            $account->save();

                            Notification::make()
                                ->title('New Notes Demo User')
                                ->body(collect([
                                    'NAME: '.$account->name,
                                    'EMAIL: '.$account->email,
                                    'USERNAME: '.$account->username,
                                    'DATE: '.Carbon::now()->diffForHumans(),
                                    'URL: '.url('/'),
                                ])->implode("\n"))
                                ->sendToDiscord();

                            $account->meta($provider . '_id', $socialUser->id);
                            if($socialUser->token){
                                $account->meta($provider . '_token', $socialUser->token);
                            }
                            if($socialUser->refreshToken){
                                $account->meta($provider . '_refresh_token', $socialUser->refreshToken);
                            }

                            if(isset($socialUser->attributes['avatar'])){
                                $account->addMediaFromUrl($socialUser->attributes['avatar'])->toMediaCollection('avatar');
                            }

                            Notification::make()
                                ->title('Account Connected')
                                ->body('Account connected successfully!')
                                ->success()
                                ->send();

                            auth('accounts')->login($account);
                            return redirect()->to('app');
                        }
                    }
                    else {
                        Notification::make()
                            ->title('Error')
                            ->body('Something went wrong!')
                            ->danger()
                            ->send();
                        return redirect()->to('app/login');
                    }
                }
            }
        }
        catch (\Exception $exception){
            Notification::make()
                ->title('Error')
                ->body('Something went wrong!')
                ->danger()
                ->send();
            return redirect()->to('app/login');
        }
    }
}
