<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LocaleEnum;

final class LanguageSwitchService
{
    protected const string LOCALE_KEY = 'language_switch_service_locale';

    public function getLocale(): string
    {
        $locale = session(self::LOCALE_KEY) ??
            request()->cookie(self::LOCALE_KEY) ??
            request()->get('locale') ??
            LocaleEnum::English->value;

        if ($locale instanceof LocaleEnum) {
            $locale = $locale->value;
        }

        return $locale;
    }

    public function setLocale(LocaleEnum $language): void
    {
        session()->put(self::LOCALE_KEY, $language->value);

        cookie()->queue(cookie()->forever(self::LOCALE_KEY, $language->value));
    }
}
