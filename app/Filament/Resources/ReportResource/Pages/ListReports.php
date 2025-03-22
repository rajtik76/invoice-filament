<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\HasTranslatedListPageTitle;
use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    use HasTranslatedListPageTitle;

    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('base.create_report'))
                ->modalHeading(trans('base.create_report'))
                ->slideOver()
                ->using(function (array $data): void {
                    $data['user_id'] = auth()->id();
                    $data['content'] = [];
                    Report::create($data);
                }),
        ];
    }
}
