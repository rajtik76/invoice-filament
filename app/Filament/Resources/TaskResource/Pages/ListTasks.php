<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\HasTranslatedListPageTitle;
use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    use HasTranslatedListPageTitle;

    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label(trans('base.create_task'))
                ->modalHeading(trans('base.create_task'))
                ->using(function (array $data) {
                    TaskResource::createRecordForCurrentUser($data);
                }),
        ];
    }
}
