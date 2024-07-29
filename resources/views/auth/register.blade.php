<x-filament-panels::page.simple>
    @if (filament()->hasLogin())
        <x-slot name="subheading">
            Before create an account please join our <x-filament::link target="_blank" href="https://discord.gg/vKV9U7gD3c">discord server</x-filament::link> because it's required for verification.
            {{ __('filament-panels::pages/auth/register.actions.login.before') }}

            {{ $this->loginAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
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


        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
