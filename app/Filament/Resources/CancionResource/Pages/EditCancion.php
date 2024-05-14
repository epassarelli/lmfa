<?php

namespace App\Filament\Resources\CancionResource\Pages;

use App\Filament\Resources\CancionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCancion extends EditRecord
{
    protected static string $resource = CancionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
