<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotaResource\Pages;
use App\Models\EstudianteLogro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotaResource extends Resource
{
    protected static ?string $model = EstudianteLogro::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static ?string $navigationLabel = 'Reporte Materia';
    
    protected static ?string $modelLabel = 'Reporte Materia';
    
    protected static ?string $pluralModelLabel = 'Reportes Materia';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Reportes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('estudiante_id')
                    ->relationship('estudiante', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Estudiante'),
                Forms\Components\Select::make('materia_id')
                    ->relationship('logro.materia', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Materia')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Limpiar el logro seleccionado cuando cambie la materia
                        $set('logro_id', null);
                    }),
                Forms\Components\Select::make('logro_id')
                    ->relationship('logro', 'titulo', function ($query, $get) {
                        $materiaId = $get('materia_id');
                        if ($materiaId) {
                            return $query->where('materia_id', $materiaId)
                                       ->where('activo', true)
                                       ->orderBy('titulo');
                        }
                        return $query->where('id', 0); // No mostrar nada si no hay materia seleccionada
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Logro')
                    ->helperText('Seleccione primero una materia para ver los logros disponibles')
                    ->disabled(fn ($get) => !$get('materia_id'))
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->titulo . ' - ' . substr($record->competencia, 0, 50) . '...';
                    }),
                Forms\Components\Select::make('periodo_id')
                    ->relationship('periodo', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Período')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->nombre . ' - ' . $record->corte . ' ' . $record->año_escolar;
                    }),
                Forms\Components\Select::make('nivel_desempeno')
                    ->options([
                        'Superior' => 'Superior',
                        'Alto' => 'Alto',
                        'Básico' => 'Básico',
                        'Bajo' => 'Bajo',
                    ])
                    ->required()
                    ->label('Nivel de Desempeño')
                    ->helperText('Seleccione el nivel de desempeño alcanzado por el estudiante en este logro'),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones')
                    ->helperText('Comentarios adicionales sobre el desempeño del estudiante'),
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->required()
                    ->label('Fecha de Asignación')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('estudiante.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Estudiante'),
                Tables\Columns\TextColumn::make('logro.materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('logro.materia.docente.name')
                    ->searchable()
                    ->sortable()
                    ->label('Docente'),
                Tables\Columns\TextColumn::make('logro.titulo')
                    ->searchable()
                    ->sortable()
                    ->label('Título del Logro')
                    ->limit(40),
                Tables\Columns\TextColumn::make('logro.competencia')
                    ->searchable()
                    ->sortable()
                    ->label('Competencia')
                    ->limit(50),
                Tables\Columns\TextColumn::make('logro.tema')
                    ->searchable()
                    ->sortable()
                    ->label('Tema')
                    ->limit(40),
                Tables\Columns\TextColumn::make('periodo.nombre')
                    ->formatStateUsing(function ($record) {
                        return $record->periodo->nombre . ' - ' . $record->periodo->corte . ' ' . $record->periodo->año_escolar;
                    })
                    ->searchable()
                    ->sortable()
                    ->label('Período'),
                Tables\Columns\BadgeColumn::make('nivel_desempeno')
                    ->colors([
                        'success' => 'Superior',
                        'info' => 'Alto',
                        'warning' => 'Básico',
                        'danger' => 'Bajo',
                    ])
                    ->searchable()
                    ->sortable()
                    ->label('Nivel de Desempeño'),
                Tables\Columns\TextColumn::make('fecha_asignacion')
                    ->date()
                    ->sortable()
                    ->label('Fecha de Asignación'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periodo_id')
                    ->relationship('periodo', 'nombre')
                    ->label('Período')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->nombre . ' - ' . $record->corte . ' ' . $record->año_escolar;
                    }),
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('logro.materia', 'nombre')
                    ->label('Materia'),
                Tables\Filters\SelectFilter::make('nivel_desempeno')
                    ->options([
                        'Superior' => 'Superior',
                        'Alto' => 'Alto',
                        'Básico' => 'Básico',
                        'Bajo' => 'Bajo',
                    ])
                    ->label('Nivel de Desempeño'),
                Tables\Filters\SelectFilter::make('estudiante_id')
                    ->relationship('estudiante', 'nombre')
                    ->label('Estudiante'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotas::route('/'),
            'create' => Pages\CreateNota::route('/create'),
            'edit' => Pages\EditNota::route('/{record}/edit'),
        ];
    }
} 