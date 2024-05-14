<?php

namespace App\Filament\Resources\MitoResource\Pages;

use App\Filament\Resources\MitoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMito extends EditRecord
{
    protected static string $resource = MitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
