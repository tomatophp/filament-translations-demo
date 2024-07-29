<div class="fi-simple-page">
    <section class="grid auto-cols-fr gap-y-6">
        <x-filament-panels::header.simple
            :heading="$this->getHeading()"
            :logo="true"
        >
            <x-slot name="subheading">
                Please check our <x-filament::link target="_blank" href="https://discord.gg/vKV9U7gD3c">discord server</x-filament::link> and check <b>#otp</b> channel for the OTP.
            </x-slot>
        </x-filament-panels::header.simple>

        <x-filament-panels::form>
            {{ $this->form }}

            <div
                class="fi-form-actions"
            >
                {{ $this->getSubmitButton }}

                <x-filament-actions::modals />
            </div>
        </x-filament-panels::form>

    </section>
</div>
