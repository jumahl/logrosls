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
use Filament\Notifications\Notification;

class PeriodoResource extends Resource
{
    protected static ?string $model = Periodo::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Períodos';
    
    protected static ?string $modelLabel = 'Período';
    
    protected static ?string $pluralModelLabel = 'Períodos';
    
    protected static ?int $navigationSort = 6;
    
    protected static ?string $navigationGroup = 'Gestión de Estudiantes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required()
                    ->label('Fecha de Inicio'),
                Forms\Components\DatePicker::make('fecha_fin')
                    ->required()
                    ->label('Fecha de Fin'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Período Activo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
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
                Tables\Actions\DeleteAction::make()
                    ->before(function (Periodo $record) {
                        // Desvincular los logros del período
                        $record->logros()->detach();
                    })
                    ->after(function (Periodo $record) {
                        Notification::make()
                            ->title('Período eliminado exitosamente')
                            ->icon('heroicon-o-trash')
                            ->iconColor('danger')
                            ->body('El período ha sido eliminado del sistema.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                // Desvincular los logros de cada período
                                $record->logros()->detach();
                            }
                        })
                        ->after(function () {
                            Notification::make()
                                ->title('Períodos eliminados exitosamente')
                                ->icon('heroicon-o-trash')
                                ->iconColor('danger')
                                ->body('Los períodos seleccionados han sido eliminados del sistema.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Se ha eliminado la referencia al LogrosRelationManager
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
