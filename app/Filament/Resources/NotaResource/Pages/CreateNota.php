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
use App\Models\Grado;

class CreateNota extends CreateRecord
{
    protected static string $resource = NotaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();
        return $form
            ->schema([
                Forms\Components\Select::make('grado_id')
                    ->options(function () use ($user) {
                        if ($user && $user->hasRole('profesor')) {
                            // Solo mostrar grados donde el profesor tiene materias asignadas
                            $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                            return \App\Models\Grado::where('activo', true)->whereIn('id', $gradoIds)->pluck('nombre', 'id');
                        }
                        return \App\Models\Grado::where('activo', true)->pluck('nombre', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Grado')
                    ->live()
                    ->dehydrated(false)
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Limpiar el estudiante cuando cambie el grado
                        $set('estudiante_id', null);
                    }),
                Forms\Components\Select::make('estudiante_id')
                    ->options(function ($get) use ($user) {
                        $gradoId = $get('grado_id');
                        if ($gradoId) {
                            $query = Estudiante::where('grado_id', $gradoId)->where('activo', true);
                            
                            if ($user && $user->hasRole('profesor')) {
                                // Solo mostrar estudiantes de grados donde el profesor tiene materias asignadas
                                $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                                if (!$gradoIds->contains($gradoId)) {
                                    return [];
                                }
                            }
                            
                            return $query->get()->mapWithKeys(function ($estudiante) {
                                return [$estudiante->id => $estudiante->nombre_completo];
                            });
                        }
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Estudiante')
                    ->disabled(fn ($get) => !$get('grado_id')),
                Forms\Components\Select::make('materia_id')
                    ->options(function () use ($user) {
                        if ($user && $user->hasRole('profesor')) {
                            return $user->materias()->where('activa', true)->pluck('nombre', 'id');
                        }
                        return Materia::where('activa', true)->pluck('nombre', 'id');
                    })
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
                    ->relationship('periodo', 'corte')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->nombre . ' - ' . $record->corte . ' ' . $record->año_escolar;
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Período'),
                Forms\Components\Select::make('logros')
                    ->options(function ($get) use ($user) {
                        $materiaId = $get('materia_id');
                        if ($materiaId) {
                            $query = Logro::where('materia_id', $materiaId)->where('activo', true);
                            
                            if ($user && $user->hasRole('profesor')) {
                                // Solo mostrar logros de materias del profesor
                                $materiaIds = $user->materias()->pluck('id');
                                if (!$materiaIds->contains($materiaId)) {
                                    return [];
                                }
                            }
                            
                            return $query->orderBy('titulo')->pluck('titulo', 'id')->map(function ($titulo, $id) {
                                $logro = Logro::find($id);
                                return $titulo . ' - ' . substr($logro->competencia, 0, 50) . '...';
                            });
                        }
                        return [];
                    })
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->label('Logros a Asignar')
                    ->helperText('Seleccione los logros que desea asignar al estudiante. Puede seleccionar múltiples logros de la materia seleccionada.')
                    ->disabled(fn ($get) => !$get('materia_id')),
                Forms\Components\Select::make('nivel_desempeno')
                    ->options([
                        'E' => 'E - Excelente',
                        'S' => 'S - Sobresaliente',
                        'A' => 'A - Aceptable',
                        'I' => 'I - Insuficiente',
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

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $created = null;
        if (isset($data['logros']) && is_array($data['logros'])) {
            $logros = $data['logros'];
            unset($data['logros']);
            foreach ($logros as $logroId) {
                $existe = \App\Models\EstudianteLogro::where('estudiante_id', $data['estudiante_id'])
                    ->where('logro_id', $logroId)
                    ->where('periodo_id', $data['periodo_id'])
                    ->exists();
                if (!$existe) {
                    $created = \App\Models\EstudianteLogro::create([
                        'estudiante_id' => $data['estudiante_id'],
                        'logro_id' => $logroId,
                        'periodo_id' => $data['periodo_id'],
                        'nivel_desempeno' => $data['nivel_desempeno'],
                        'observaciones' => $data['observaciones'] ?? null,
                        'fecha_asignacion' => $data['fecha_asignacion'],
                    ]);
                }
            }
        }
        return $created;
    }
} 