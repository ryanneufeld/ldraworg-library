<?php

namespace App\Filament\Resources\ReviewSummaryResource\Pages;

use App\Filament\Resources\ReviewSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReviewSummaries extends ListRecords
{
    protected static string $resource = ReviewSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
