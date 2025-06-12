<x-filament-panels::page>
    <div class="max-w-xl sm:max-w-lg w-full fi-simple-main bg-white px-6 py-12 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:rounded-xl sm:px-12">
        <x-filament-panels::form wire:submit="changePassword">
            {{ $this->form }}

            <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    </div>
</x-filament-panels::page>