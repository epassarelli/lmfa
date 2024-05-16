<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CancionResource\Pages;
use App\Filament\Resources\CancionResource\RelationManagers;
use App\Models\Cancion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CancionResource extends Resource
{
    protected static ?string $model = Cancion::class;

    protected static ?string $navigationIcon = 'heroicon-c-musical-note';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cancion')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('letra')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('youtube')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('spotify')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('interprete_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('visitas')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('publicar')
                    ->required(),
                Forms\Components\TextInput::make('estado')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cancion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('youtube')
                    ->searchable(),
                Tables\Columns\TextColumn::make('spotify')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interprete_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('publicar')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCancions::route('/'),
            'create' => Pages\CreateCancion::route('/create'),
            'edit' => Pages\EditCancion::route('/{record}/edit'),
        ];
    }
}
