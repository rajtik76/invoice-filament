<?php

declare(strict_types=1);

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Traits\HasTranslatedListPageTitleTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    use HasTranslatedListPageTitleTrait;

    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label(trans('label.create_task'))
                ->modalHeading(trans('label.create_task'))
                ->using(function (array $data) {
                    TaskResource::createRecordForCurrentUser($data);
                }),
        ];
    }
}
