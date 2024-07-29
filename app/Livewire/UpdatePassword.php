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

class UpdatePassword extends SimplePage implements HasActions, HasForms
{
    use InteractsWithForms;
    use InteractsWithActions;
    use InteractsWithFormActions;

    protected static string $view = 'livewire.update-password';


    public array $data = [];

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
                ->extraInputAttributes(['tabindex' => 2]),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(),
            TextInput::make('password_confirmation')
                ->label('Confirm Password')
                ->password()
                ->required(),
        ])->statePath('data');
    }

    public function getSubmitButton(): Action
    {
        return Action::make('getSubmitButton')
            ->label('Update Password')
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

                if($findAccountWithEmail->otp_code !== $data['otp']){
                    Notification::make()
                        ->title('OTP Code is not valid')
                        ->body('Please enter a valid OTP code.')
                        ->danger()
                        ->send();
                    return;
                }

                $findAccountWithEmail->update([
                    'password' => bcrypt($data['password'])
                ]);

                Notification::make()
                    ->title('Password Updated')
                    ->body('Your password has been updated successfully.')
                    ->success()
                    ->send();

                return redirect()->to('app/login');
            });
    }
}
