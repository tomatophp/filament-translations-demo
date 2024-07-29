<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

        @error('data.email')
            @if($message === trans('filament-accounts::messages.login.active'))
                <div class="text-center text-red-500 font-bold">Your account is not active. <x-filament::link href="{{ url('otp') }}">Active it now</x-filament::link>.</div>
            @endif
        @enderror
    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />

    </x-filament-panels::form>
    <div class="text-center">
        <span class="text-gray-400">Do you forget your password?</span> <x-filament::link href="{{ url('reset') }}">Reset It</x-filament::link>
    </div>


    <div class="font-bold text-center capitalize">OR</div>

    <div class="flex justify-center items-center gap-2">
        <a href="{{ route('login.provider', ['provider' => 'github']) }}" x-tooltip="{
            content: 'Login With Github'
        }">
            <img class="w-8 h-8" src="{{ url('icons/github.svg') }}" alt="Github" />
        </a>
        <a href="{{ route('login.provider', ['provider' => 'discord']) }}" x-tooltip="{
            content: 'Login With Discord'
        }">
            <img class="w-8 h-8" src="{{ url('icons/discord.svg') }}" alt="Discord" />
        </a>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
