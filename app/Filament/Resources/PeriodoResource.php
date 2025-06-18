<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodoResource\Pages;
use App\Filament\Resources\PeriodoResource\RelationManagers;
use App\Models\Periodo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PeriodoResource extends Resource
{
    protected static ?string $model = Periodo::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Periodos';
    
    protected static ?string $modelLabel = 'Periodo';
    
    protected static ?string $pluralModelLabel = 'Periodos';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Gestión Académica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre del Periodo'),
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required()
                    ->label('Fecha de Inicio'),
                Forms\Components\DatePicker::make('fecha_fin')
                    ->required()
                    ->label('Fecha de Fin')
                    ->after('fecha_inicio'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Periodo Activo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre del Periodo'),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Fecha de Inicio'),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Fecha de Fin'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activo')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ])
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\LogrosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodos::route('/'),
            'create' => Pages\CreatePeriodo::route('/create'),
            'edit' => Pages\EditPeriodo::route('/{record}/edit'),
        ];
    }
}
