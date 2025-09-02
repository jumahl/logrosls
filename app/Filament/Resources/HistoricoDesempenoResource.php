<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistoricoDesempenoResource\Pages;
use App\Filament\Resources\HistoricoDesempenoResource\RelationManagers;
use App\Models\HistoricoDesempeno;
use App\Services\BoletinHistoricoService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HistoricoDesempenoResource extends Resource
{
    protected static ?string $model = HistoricoDesempeno::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Histórico de Desempeños';
    
    protected static ?string $modelLabel = 'Histórico de Desempeño';
    
    protected static ?string $pluralModelLabel = 'Histórico de Desempeños';
    
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
                        Forms\Components\TextInput::make('materia_nombre')
                            ->label('Materia')
                            ->disabled(),
                        Forms\Components\TextInput::make('periodo_nombre')
                            ->label('Período')
                            ->disabled(),
                        Forms\Components\Select::make('nivel_desempeno')
                            ->label('Desempeño')
                            ->options([
                                'E' => 'Excelente',
                                'S' => 'Sobresaliente',
                                'A' => 'Aceptable',
                                'I' => 'Insuficiente',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('observaciones_finales')
                            ->label('Observaciones')
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
                    ->label('Estudiante')
                    ->formatStateUsing(fn ($record) => $record->estudiante_nombre . ' ' . $record->estudiante_apellido)
                    ->searchable(['estudiante_nombre', 'estudiante_apellido'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('materia_nombre')
                    ->label('Materia')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('periodo_nombre')
                    ->label('Período')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nivel_desempeno')
                    ->label('Desempeño')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'E' => 'success',
                        'S' => 'warning', 
                        'A' => 'primary',
                        'I' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'E' => 'Excelente',
                        'S' => 'Sobresaliente',
                        'A' => 'Aceptable',
                        'I' => 'Insuficiente',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('observaciones_finales')
                    ->label('Observaciones')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Archivo')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anio_escolar')
                    ->label('Año Escolar')
                    ->options(function () {
                        return \App\Models\HistoricoDesempeno::select('anio_escolar')
                            ->distinct()
                            ->orderBy('anio_escolar', 'desc')
                            ->pluck('anio_escolar', 'anio_escolar');
                    }),
                Tables\Filters\SelectFilter::make('materia_nombre')
                    ->label('Materia')
                    ->options(function () {
                        return \App\Models\HistoricoDesempeno::select('materia_nombre')
                            ->distinct()
                            ->orderBy('materia_nombre')
                            ->pluck('materia_nombre', 'materia_nombre');
                    }),
                Tables\Filters\SelectFilter::make('nivel_desempeno')
                    ->label('Desempeño')
                    ->options([
                        'E' => 'Excelente',
                        'S' => 'Sobresaliente',
                        'A' => 'Aceptable',
                        'I' => 'Insuficiente',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('descargar_boletin')
                    ->label('Descargar Boletín')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($record) {
                        try {
                            $service = new BoletinHistoricoService();
                            $pdf = $service->generarBoletinHistorico($record->estudiante_id, $record->anio_escolar);
                            
                            $filename = "boletin_historico_{$record->estudiante_nombre}_{$record->estudiante_apellido}_{$record->anio_escolar}.pdf";
                            
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, $filename);
                            
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al generar boletín')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Descargar Boletín Histórico')
                    ->modalDescription(fn ($record) => "¿Descargar el boletín de {$record->estudiante_nombre} {$record->estudiante_apellido} del año {$record->anio_escolar}?"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('descargar_boletines_masivo')
                        ->label('Descargar Boletines')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($records) {
                            try {
                                $service = new BoletinHistoricoService();
                                $zipPath = $service->generarBoletinesHistoricosMasivo($records);
                                
                                $zipName = "boletines_historicos_" . now()->format('Y-m-d_H-i-s') . ".zip";
                                
                                return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
                                
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error al generar boletines')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Descargar Boletines Históricos')
                        ->modalDescription('¿Descargar boletines de los registros seleccionados?'),
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
            'index' => Pages\ListHistoricoDesempenos::route('/'),
            'view' => Pages\ViewHistoricoDesempeno::route('/{record}'),
            // No permitir crear o editar datos históricos
        ];
    }
}
