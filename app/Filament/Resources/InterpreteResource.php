<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterpreteResource\Pages;
use App\Filament\Resources\InterpreteResource\RelationManagers;
use App\Models\Interprete;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;


class InterpreteResource extends Resource
{
    protected static ?string $model = Interprete::class;

    protected static ?string $navigationIcon = 'heroicon-s-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('interprete')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->maxLength(100),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->maxLength(100),
                Forms\Components\FileUpload::make('foto')
                    ->image()
                    ->imageEditor()
                    ->required(),
                Forms\Components\RichEditor::make('biografia')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('telefono')
                    ->maxLength(255),
                Forms\Components\TextInput::make('correo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('instagram')
                    ->maxLength(255),
                Forms\Components\TextInput::make('twitter')
                    ->maxLength(255),
                Forms\Components\TextInput::make('youtube')
                    ->maxLength(255),
                Forms\Components\TextInput::make('visitas')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('publicar')
                    ->required(),
                Forms\Components\TextInput::make('estado')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('interprete')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('telefono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('correo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('instagram')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('twitter')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('youtube')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('visitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('publicar')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
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
                Tables\Actions\Action::make('Publicar')
                    ->icon('heroicon-m-check-badge')
                    ->action(function (Interprete $interprete) {
                        $interprete->estado = 1;
                        $interprete->save();
                    }),
                Tables\Actions\Action::make('DesPublicar')
                    ->icon('heroicon-m-check-badge')
                    ->action(function (Interprete $interprete) {
                        $interprete->estado = 0;
                        $interprete->save();
                    })
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
            'index' => Pages\ListInterpretes::route('/'),
            'create' => Pages\CreateInterprete::route('/create'),
            'edit' => Pages\EditInterprete::route('/{record}/edit'),
        ];
    }
}
