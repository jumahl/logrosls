<?php

namespace App\Filament\Resources\NotaResource\Pages;

use App\Filament\Resources\NotaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use Filament\Forms\Form;
use App\Models\EstudianteLogro;
use App\Models\Estudiante;
use App\Models\Periodo;
use App\Models\Logro;
use App\Models\Materia;

class CreateNota extends CreateRecord
{
    protected static string $resource = NotaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function form(Form $form): Form
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
                    ->options(Materia::where('activa', true)->pluck('nombre', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Materia')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Limpiar los logros cuando cambie la materia
                        $set('logros', []);
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
                Forms\Components\Repeater::make('logros')
                    ->schema([
                        Forms\Components\Select::make('logro_id')
                            ->options(function ($get) {
                                $materiaId = $get('../../materia_id');
                                if ($materiaId) {
                                    return Logro::where('materia_id', $materiaId)
                                               ->where('activo', true)
                                               ->orderBy('titulo')
                                               ->pluck('titulo', 'id')
                                               ->map(function ($titulo, $id) {
                                                   $logro = Logro::find($id);
                                                   return $titulo . ' - ' . substr($logro->competencia, 0, 50) . '...';
                                               });
                                }
                                return [];
                            })
                            ->required()
                            ->searchable()
                            ->label('Logro')
                            ->disabled(fn ($get) => !$get('../../materia_id')),
                    ])
                    ->columns(1)
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => 
                        isset($state['logro_id']) ? Logro::find($state['logro_id'])?->titulo : null
                    )
                    ->label('Logros a Asignar')
                    ->helperText('Agregue los logros que desea asignar al estudiante. Puede agregar múltiples logros de la materia seleccionada.')
                    ->disabled(fn ($get) => !$get('materia_id')),
                Forms\Components\Select::make('nivel_desempeno')
                    ->options([
                        'Superior' => 'Superior',
                        'Alto' => 'Alto',
                        'Básico' => 'Básico',
                        'Bajo' => 'Bajo',
                    ])
                    ->required()
                    ->label('Nivel de Desempeño')
                    ->helperText('Seleccione el nivel de desempeño general del estudiante en esta materia'),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones')
                    ->helperText('Comentarios adicionales sobre el desempeño general del estudiante en esta materia'),
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->required()
                    ->label('Fecha de Asignación')
                    ->default(now()),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si hay múltiples logros, crear un registro por cada logro
        if (isset($data['logros']) && is_array($data['logros'])) {
            $logros = $data['logros'];
            unset($data['logros']); // Remover el array de logros del data principal
            
            // Crear múltiples registros
            foreach ($logros as $logroData) {
                $this->createRecord([
                    'estudiante_id' => $data['estudiante_id'],
                    'logro_id' => $logroData['logro_id'],
                    'periodo_id' => $data['periodo_id'],
                    'nivel_desempeno' => $data['nivel_desempeno'],
                    'observaciones' => $data['observaciones'] ?? null,
                    'fecha_asignacion' => $data['fecha_asignacion'],
                ]);
            }
            
            // Retornar null para evitar crear un registro adicional
            return null;
        }
        
        return $data;
    }
} 