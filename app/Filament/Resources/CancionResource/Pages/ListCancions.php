<?php

namespace App\Filament\Resources\CancionResource\Pages;

use App\Filament\Resources\CancionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCancions extends ListRecords
{
    protected static string $resource = CancionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
