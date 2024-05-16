<?php

namespace App\Filament\Resources\FestivalResource\Pages;

use App\Filament\Resources\FestivalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFestivals extends ListRecords
{
    protected static string $resource = FestivalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
