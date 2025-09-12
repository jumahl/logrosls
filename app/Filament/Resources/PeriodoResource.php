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
use App\Rules\FechaFinPosteriorInicio;
use App\Rules\PeriodoUnicoActivo;
use App\Rules\PeriodoUnico;

class PeriodoResource extends Resource
{
    protected static ?string $model = Periodo::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Períodos';
    
    protected static ?string $modelLabel = 'Período';
    
    protected static ?string $pluralModelLabel = 'Períodos';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Configuración Académica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('numero_periodo')
                    ->options([
                        1 => 'Primer Período',
                        2 => 'Segundo Período',
                    ])
                    ->required()
                    ->label('Número de Período')
                    ->helperText('Seleccione si es el primer o segundo período del año escolar')
                    ->live(),
                Forms\Components\Select::make('corte')
                    ->options([
                        'Primer Corte' => 'Primer Corte',
                        'Segundo Corte' => 'Segundo Corte',
                    ])
                    ->required()
                    ->label('Corte')
                    ->helperText('Primer Corte: Preinforme, Segundo Corte: Boletín final')
                    ->live(),
                Forms\Components\TextInput::make('anio_escolar')
                    ->required()
                    ->numeric()
                    ->minValue(date('Y') - 1)
                    ->maxValue(date('Y') + 10)
                    ->default(date('Y'))
                    ->label('Año Escolar')
                    ->helperText('Año escolar al que pertenece este período. Se creará automáticamente si no existe.')
                    ->live()
                    ->rules([
                        function ($get, $record) {
                            return new PeriodoUnico(
                                $get('numero_periodo'),
                                $get('corte'),
                                $get('anio_escolar'),
                                $record?->id
                            );
                        }
                    ]),
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required()
                    ->label('Fecha de Inicio')
                    ->live(),
                Forms\Components\DatePicker::make('fecha_fin')
                    ->required()
                    ->label('Fecha de Fin')
                    ->rules([
                        function ($get) {
                            return new FechaFinPosteriorInicio($get('fecha_inicio'));
                        }
                    ]),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Período Activo')
                    ->helperText('Solo un período puede estar activo a la vez')
                    ->rules([
                        function ($record) {
                            return new PeriodoUnicoActivo($record?->id);
                        }
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->formatStateUsing(fn (Periodo $record): string => $record->nombre),
                Tables\Columns\TextColumn::make('numero_periodo')
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? 'Primer Período' : 'Segundo Período')
                    ->sortable()
                    ->label('Período'),
                Tables\Columns\TextColumn::make('corte')
                    ->searchable()
                    ->sortable()
                    ->label('Corte'),
                Tables\Columns\TextColumn::make('anio_escolar')
                    ->searchable()
                    ->sortable()
                    ->label('Año Escolar'),
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
        Tables\Filters\SelectFilter::make('anio_escolar')
                    ->options(function () {
            $anios = Periodo::distinct()->pluck('anio_escolar')->sort()->toArray();
            return array_combine($anios, $anios);
                    })
                    ->label('Año Escolar'),
                Tables\Filters\SelectFilter::make('numero_periodo')
                    ->options([
                        1 => 'Primer Período',
                        2 => 'Segundo Período',
                    ])
                    ->label('Período'),
                Tables\Filters\SelectFilter::make('corte')
                    ->options([
                        'Primer Corte' => 'Primer Corte',
                        'Segundo Corte' => 'Segundo Corte',
                    ])
                    ->label('Corte'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Periodo $record) {
                        // Desvincular los logros del período
                        $record->logros()->detach();
                    })

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
