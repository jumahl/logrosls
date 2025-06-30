<?php

namespace App\Filament\Resources\GradoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Logro;

class LogrosRelationManager extends RelationManager
{
    protected static string $relationship = 'logros';

    protected static ?string $recordTitleAttribute = 'titulo';

    /**
     * SOBRESCRITURA IMPORTANTE:
     * La relación 'logros' en el modelo Grado es incorrecta para nuestra BD,
     * pero necesaria para que Filament se inicie. Aquí la reemplazamos
     * por la consulta correcta que usa la tabla pivote 'grado_materia'.
     * Esto corrige tanto la tabla como el conteo de la pestaña.
     */
    protected function getTableQuery(): Builder
    {
        $grado = $this->getOwnerRecord();
        $materiaIds = $grado->materias()->pluck('materias.id');

        return Logro::query()->whereIn('materia_id', $materiaIds);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('materia_id')
                    ->relationship('materia', 'nombre', function (Builder $query) {
                        // Filtra las materias para mostrar solo las que pertenecen a este grado
                        $gradoId = $this->getOwnerRecord()->id;
                        return $query->whereHas('grados', function ($q) use ($gradoId) {
                            $q->where('grados.id', $gradoId);
                        });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Materia'),
                Forms\Components\TextInput::make('titulo')
                    ->required()
                    ->maxLength(255)
                    ->label('Título del Logro'),
                Forms\Components\TextInput::make('competencia')
                    ->required()
                    ->maxLength(255)
                    ->label('Competencia'),
                Forms\Components\TextInput::make('tema')
                    ->required()
                    ->maxLength(255)
                    ->label('Tema'),
                Forms\Components\TextInput::make('indicador_desempeno')
                    ->required()
                    ->maxLength(255)
                    ->label('Indicador de Desempeño'),
                Forms\Components\Textarea::make('descripcion')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Descripción'),
                Forms\Components\Select::make('nivel_dificultad')
                    ->required()
                    ->options([
                        'bajo' => 'Bajo',
                        'medio' => 'Medio',
                        'alto' => 'Alto',
                    ])
                    ->label('Nivel de Dificultad'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Logro Activo'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable()
                    ->sortable()
                    ->label('Título'),
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('competencia')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->label('Competencia'),
                Tables\Columns\TextColumn::make('nivel_dificultad')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bajo' => 'gray',
                        'medio' => 'warning',
                        'alto' => 'danger',
                    })
                    ->label('Nivel'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->searchable()
                    ->preload()
                    ->label('Materia'),
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Asignar automáticamente la materia del grado
                        $data['materia_id'] = $data['materia_id'] ?? null;
                        return $data;
                    }),
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
} 