<?php

namespace App\Filament\Resources\ComidaResource\Pages;

use App\Filament\Resources\ComidaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComida extends EditRecord
{
    protected static string $resource = ComidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
