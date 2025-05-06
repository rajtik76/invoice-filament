<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Enums\LanguageEnum;
use App\Services\LanguageSwitchService;
use Livewire\Component;

class LanguageSwitch extends Component
{
    public function changeLanguage(LanguageEnum $language, LanguageSwitchService $languageSwitchService)
    {
        $languageSwitchService->setLocale($language);

        return redirect(url()->previous());
    }

    public function render()
    {
        return view('livewire.language-switch');
    }
}
