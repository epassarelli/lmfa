<?php

namespace App\Filament\Resources\MitoResource\Pages;

use App\Filament\Resources\MitoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMitos extends ListRecords
{
    protected static string $resource = MitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
