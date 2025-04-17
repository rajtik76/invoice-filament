<?php

declare(strict_types=1);

namespace App\Filament\Resources\TaskHourResource\Pages;

use App\Filament\Resources\TaskHourResource;
use App\Models\TaskHour;
use App\Traits\HasTranslatedListPageTitleTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;

class ListTaskHours extends ListRecords
{
    use HasTranslatedListPageTitleTrait;

    protected static string $resource = TaskHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('label.create_task_hour'))
                ->modalHeading(trans('label.create_task_hour'))
                ->slideOver()
                ->using(function (array $data): void {
                    TaskHour::create(Arr::add($data, 'user_id', auth()->id()));
                }),
        ];
    }
}
