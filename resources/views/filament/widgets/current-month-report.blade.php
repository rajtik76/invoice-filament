<x-filament-widgets::widget>
    <x-filament::section>
        <div>
            <form wire:submit="create">
                {{ $this->form }}

                <div class="pt-4">
                    <x-filament::button type="submit">
                        {{ __('base.show') }}
                    </x-filament::button>
                </div>
            </form>

            <x-filament-actions::modals/>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
