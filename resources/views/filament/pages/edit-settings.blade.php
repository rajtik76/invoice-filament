<x-filament-panels::page>
    <div>
        <form wire:submit="create">
            {{ $this->form }}

            <div class="flex items-center justify-start gap-4 mt-6">
                @foreach ($this->getFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </form>

        <x-filament-actions::modals/>
    </div>
</x-filament-panels::page>
