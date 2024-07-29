<?php

namespace App\Livewire;

use App\Models\Account;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ResetPassword extends SimplePage implements HasActions, HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;
    use InteractsWithActions;

    protected static string $view = "livewire.reset-password";

    public array $data = [];

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),
        ])->statePath('data');
    }

    public function getSubmitButton(): Action
    {
        return Action::make('getSubmitButton')
            ->label('Send Reset OTP')
            ->action(function (){
                $data = $this->form->getState();

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


                return redirect()->to('password');
            });
    }
}
