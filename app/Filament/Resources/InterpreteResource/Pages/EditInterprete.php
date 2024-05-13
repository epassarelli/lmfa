<?php

namespace App\Filament\Resources\InterpreteResource\Pages;

use App\Filament\Resources\InterpreteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInterprete extends EditRecord
{
    protected static string $resource = InterpreteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
