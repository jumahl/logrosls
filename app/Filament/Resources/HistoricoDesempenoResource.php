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
use Illuminate\Support\Facades\DB;

class HistoricoDesempenoResource extends Resource
{
    protected static ?string $model = HistoricoDesempeno::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Histórico de Desempeños';
    
    protected static ?string $modelLabel = 'Histórico de Desempeño';
    
    protected static ?string $pluralModelLabel = 'Histórico de Desempeños';
    
    protected static ?string $navigationGroup = 'Históricos';

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
                    ->label('Año Escolar')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('estudiante_nombre_completo')
                    ->label('Estudiante')
                    ->getStateUsing(fn ($record) => $record->estudiante_nombre . ' ' . $record->estudiante_apellido . ' - ' . $record->estudiante_documento)
                    ->searchable(['estudiante_nombre', 'estudiante_apellido', 'estudiante_documento'])
                    ->sortable(['estudiante_apellido', 'estudiante_nombre']),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Archivo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('descargar_boletin_historico')
                    ->label('Boletín Histórico')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($record) {
                        try {
                            $service = new BoletinHistoricoService();
                            $pdf = $service->generarBoletinHistorico($record->estudiante_id, $record->anio_escolar);
                            $filename = "boletin_historico_{$record->estudiante_apellido}_{$record->estudiante_nombre}_{$record->anio_escolar}.pdf";
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
                    ->modalDescription(fn ($record) => "¿Descargar el boletín histórico de {$record->estudiante_nombre} {$record->estudiante_apellido} del año {$record->anio_escolar}?")
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('descargar_boletines_historicos')
                        ->label('Boletines Históricos')
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
                        ->modalDescription('¿Descargar boletines históricos de los estudiantes seleccionados?'),
                ]),
            ])
            ->defaultSort('anio_escolar', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('anio_escolar', 'desc')
            ->orderBy('estudiante_apellido')
            ->orderBy('estudiante_nombre');
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
            // No permitir crear o editar datos históricos
        ];
    }
}
