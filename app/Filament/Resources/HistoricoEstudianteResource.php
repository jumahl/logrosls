<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistoricoEstudianteResource\Pages;
use App\Filament\Resources\HistoricoEstudianteResource\RelationManagers;
use App\Models\HistoricoEstudiante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HistoricoEstudianteResource extends Resource
{
    protected static ?string $model = HistoricoEstudiante::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Histórico de Estudiantes';
    
    protected static ?string $modelLabel = 'Histórico de Estudiante';
    
    protected static ?string $pluralModelLabel = 'Histórico de Estudiantes';
    
    protected static ?string $navigationGroup = 'Reportes';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
    
    public static function canCreate(): bool
    {
        return false; // No permitir crear registros históricos manualmente
    }
    
    public static function canEdit($record): bool
    {
        return false; // No permitir editar registros históricos
    }
    
    public static function canDelete($record): bool
    {
        return false; // No permitir eliminar registros históricos
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Año Escolar')
                    ->schema([
                        Forms\Components\TextInput::make('anio_escolar')
                            ->label('Año Escolar')
                            ->disabled(),
                    ])->columns(1),
                    
                Forms\Components\Section::make('Datos del Estudiante')
                    ->schema([
                        Forms\Components\TextInput::make('estudiante_nombre')
                            ->label('Nombres')
                            ->disabled(),
                        Forms\Components\TextInput::make('estudiante_apellido')
                            ->label('Apellidos')
                            ->disabled(),
                        Forms\Components\TextInput::make('estudiante_documento')
                            ->label('Documento')
                            ->disabled(),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Información Académica')
                    ->schema([
                        Forms\Components\TextInput::make('grado_nombre')
                            ->label('Grado')
                            ->disabled(),
                        Forms\Components\Select::make('resultado_final')
                            ->label('Resultado Final')
                            ->options([
                                'promovido' => 'Promovido',
                                'reprobado' => 'Reprobado',
                                'graduado' => 'Graduado',
                                'retirado' => 'Retirado',
                            ])
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('anio_escolar')
                    ->label('Año')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('estudiante_nombre')
                    ->label('Nombres')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estudiante_apellido')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estudiante_documento')
                    ->label('Documento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grado_nombre')
                    ->label('Grado')
                    ->sortable(),
                Tables\Columns\TextColumn::make('resultado_final')
                    ->label('Resultado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'promovido' => 'success',
                        'graduado' => 'info',
                        'reprobado' => 'danger',
                        'retirado' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Archivo')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anio_escolar')
                    ->label('Año Escolar')
                    ->options(function () {
                        return \App\Models\HistoricoEstudiante::select('anio_escolar')
                            ->distinct()
                            ->orderBy('anio_escolar', 'desc')
                            ->pluck('anio_escolar', 'anio_escolar');
                    }),
                Tables\Filters\SelectFilter::make('grado_nombre')
                    ->label('Grado')
                    ->options(function () {
                        return \App\Models\HistoricoEstudiante::select('grado_nombre')
                            ->distinct()
                            ->orderBy('grado_nombre')
                            ->pluck('grado_nombre', 'grado_nombre');
                    }),
                Tables\Filters\SelectFilter::make('resultado_final')
                    ->label('Resultado Final')
                    ->options([
                        'promovido' => 'Promovido',
                        'reprobado' => 'Reprobado',
                        'graduado' => 'Graduado',
                        'retirado' => 'Retirado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('anio_escolar', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No permitir eliminaciones masivas de datos históricos
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
            'index' => Pages\ListHistoricoEstudiantes::route('/'),
            'view' => Pages\ViewHistoricoEstudiante::route('/{record}'),
            // No permitir crear o editar datos históricos
        ];
    }
}
