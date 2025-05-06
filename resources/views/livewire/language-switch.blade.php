<x-filament::dropdown>
    <x-slot name="trigger">
        <x-filament::button color="info">
            {{ str(app(\App\Services\LanguageSwitchService::class)->getLocale())->upper() }}
        </x-filament::button>
    </x-slot>

    <x-filament::dropdown.list>
        @foreach(\App\Enums\LanguageEnum::cases() as $language)
            <x-filament::dropdown.list.item wire:click="changeLanguage('{{ $language }}')">
                {{ $language->translation() }}
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
