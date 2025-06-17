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

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Notas';
    
    protected static ?string $modelLabel = 'Nota';
    
    protected static ?string $pluralModelLabel = 'Notas';
    
    protected static ?int $navigationSort = 5;
    
    protected static ?string $navigationGroup = 'Gestión Académica';

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
                Forms\Components\Select::make('logro_id')
                    ->relationship('logro', 'competencia', function ($query) {
                        return $query->with('materia');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Logro')
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        if ($state) {
                            $logro = \App\Models\Logro::with('materia')->find($state);
                            if ($logro && $logro->materia) {
                                $set('materia_id', $logro->materia->id);
                            }
                        }
                    }),
                Forms\Components\Select::make('materia_id')
                    ->relationship('logro.materia', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Materia'),
                Forms\Components\Select::make('periodo_id')
                    ->relationship('periodo', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Periodo'),
                Forms\Components\Select::make('nivel_desempeno')
                    ->options([
                        'Superior' => 'Superior',
                        'Alto' => 'Alto',
                        'Básico' => 'Básico',
                        'Bajo' => 'Bajo',
                    ])
                    ->required()
                    ->label('Nivel de Desempeño'),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones'),
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->required()
                    ->label('Fecha de Asignación'),
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
                Tables\Columns\TextColumn::make('logro.competencia')
                    ->searchable()
                    ->sortable()
                    ->label('Logro'),
                Tables\Columns\TextColumn::make('periodo.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Periodo'),
                Tables\Columns\TextColumn::make('nivel_desempeno')
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
                    ->label('Periodo'),
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