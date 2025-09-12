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

    protected function getTableQuery(): Builder
    {
        $grado = $this->getOwnerRecord();
        $materiaIds = $grado->materias()->pluck('materias.id');

        return Logro::query()->whereIn('materia_id', $materiaIds);
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->label('Código')
                    ->helperText('Código único del logro'),
                Forms\Components\TextInput::make('titulo')
                    ->maxLength(255)
                    ->label('Identificador / Subrama (opcional)')
                    ->helperText('Identificador para diferenciar subramas dentro de la materia (opcional)'),
                Forms\Components\Select::make('materia_id')
                    ->relationship('materia', 'nombre', function (Builder $query) {
                        $gradoId = $this->getOwnerRecord()->id;
                        return $query->whereHas('grados', function ($q) use ($gradoId) {
                            $q->where('grados.id', $gradoId);
                        });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm($user && $user->hasRole('admin') ? [
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre'),
                        Forms\Components\TextInput::make('codigo')
                            ->required()
                            ->maxLength(20)
                            ->label('Código'),
                        Forms\Components\Select::make('grados')
                            ->relationship('grados', 'nombre')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Grados'),
                    ] : null)
                    ->label('Materia'),
                Forms\Components\Textarea::make('desempeno')
                    ->required()
                    ->maxLength(65535)
                    ->label('Desempeño')
                    ->helperText('Descripción del desempeño esperado'),
                Forms\Components\Select::make('periodos')
                    ->relationship('periodos', 'corte')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Períodos')
                    ->helperText('Debe seleccionar al menos un período')
                    ->createOptionForm($user && $user->hasRole('admin') ? [
                        Forms\Components\Select::make('numero_periodo')
                            ->options([
                                1 => 'Primer Período',
                                2 => 'Segundo Período',
                            ])
                            ->required()
                            ->label('Número de Período'),
                        Forms\Components\Select::make('corte')
                            ->options([
                                'Primer Corte' => 'Primer Corte',
                                'Segundo Corte' => 'Segundo Corte',
                            ])
                            ->required()
                            ->label('Corte'),
                        Forms\Components\TextInput::make('anio_escolar')
                            ->required()
                            ->numeric()
                            ->default(date('Y'))
                            ->label('Año Escolar'),
                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->required()
                            ->label('Fecha de Inicio'),
                        Forms\Components\DatePicker::make('fecha_fin')
                            ->required()
                            ->label('Fecha de Fin'),
                    ] : null)
                    ->label('Períodos'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Logro Activo')
                    ->helperText('Indica si el logro está disponible para asignar'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => $state ?: \Illuminate\Support\Str::limit($record->desempeno, 40)),
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('desempeno')
                    ->searchable()
                    ->limit(60)
                    ->label('Desempeño'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('orden')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Orden'),
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