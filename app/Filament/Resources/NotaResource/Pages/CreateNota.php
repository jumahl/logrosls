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
use Filament\Notifications\Notification;
use App\Rules\FechaNoPosterior;

class CreateNota extends CreateRecord
{
    protected static string $resource = NotaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Nota creada')
            ->body('La nota ha sido creada exitosamente.');
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
                        // Limpiar todos los campos dependientes cuando cambie el grado
                        $set('estudiante_id', null);
                        $set('materia_id', null);
                        $set('logros', []);
                    }),
                Forms\Components\Select::make('estudiante_id')
                    ->options(function ($get) use ($user) {
                        $gradoId = $get('grado_id');
                        if (!$gradoId) {
                            return [];
                        }
                        
                        $query = Estudiante::where('grado_id', $gradoId)->where('activo', true);
                        
                        if ($user && $user->hasRole('profesor')) {
                            // Verificar que el profesor puede enseñar en este grado
                            $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                            if (!$gradoIds->contains($gradoId)) {
                                return [];
                            }
                        }
                        
                        return $query->get()->mapWithKeys(function ($estudiante) {
                            return [$estudiante->id => $estudiante->nombre . ' ' . $estudiante->apellido];
                        });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Estudiante')
                    ->disabled(fn ($get) => !$get('grado_id'))
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Limpiar materia y logros cuando cambie el estudiante
                        $set('materia_id', null);
                        $set('logros', []);
                    }),
                Forms\Components\Select::make('materia_id')
                    ->options(function ($get) use ($user) {
                        $gradoId = $get('grado_id');
                        if (!$gradoId) {
                            return [];
                        }
                        
                        // Obtener materias del grado seleccionado
                        $grado = \App\Models\Grado::find($gradoId);
                        if (!$grado) {
                            return [];
                        }
                        
                        $materias = $grado->materias()->where('materias.activa', true);
                        
                        // Si es profesor, filtrar solo las materias que él imparte en este grado
                        if ($user && $user->hasRole('profesor')) {
                            $materias = $materias->whereIn('materias.id', $user->materias()->pluck('id'));
                        }
                        
                        return $materias->pluck('materias.nombre', 'materias.id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Materia')
                    ->disabled(fn ($get) => !$get('grado_id'))
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
                        $gradoId = $get('grado_id');
                        
                        if (!$materiaId || !$gradoId) {
                            return [];
                        }
                        
                        $query = Logro::where('materia_id', $materiaId)
                            ->where('activo', true)
                            ->whereHas('materia.grados', function ($q) use ($gradoId) {
                                $q->where('grado_id', $gradoId);
                            })
                            ->select('id', 'codigo', 'titulo', 'desempeno', 'orden')
                            ->orderBy('orden')
                            ->orderBy('titulo');
                        
                        if ($user && $user->hasRole('profesor')) {
                            // Solo mostrar logros de materias del profesor
                            $materiaIds = $user->materias()->pluck('id');
                            if (!$materiaIds->contains($materiaId)) {
                                return [];
                            }
                        }
                        
                        // Construir opciones con código, título y desempeño de manera más clara
                        return $query->get()->mapWithKeys(function ($logro) {
                            // Formato más claro y completo
                            $codigo = $logro->codigo ? "[{$logro->codigo}] " : "";
                            $titulo = $logro->titulo ? "{$logro->titulo}" : "";
                            
                            // Limpiar y formatear la descripción del desempeño
                            $desempeno = trim($logro->desempeno ?? '');
                            $desempeno = preg_replace('/\s+/', ' ', $desempeno);
                            
                            // Construir la etiqueta del selector con formato mejorado
                            $label = $codigo;
                            
                            if (!empty($titulo)) {
                                $label .= $titulo;
                                
                                // Agregar desempeño solo si hay título y desempeño
                                if (!empty($desempeno)) {
                                    $label .= " — {$desempeno}";
                                }
                            } else {
                                // Si no hay título, mostrar solo el desempeño
                                $label .= $desempeno;
                            }
                            
                            return [$logro->id => $label];
                        });
                    })
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->label('Logros a Asignar')
                    ->helperText('Seleccione los logros que desea asignar al estudiante. Puede seleccionar múltiples logros de la materia seleccionada.')
                    ->disabled(fn ($get) => !$get('materia_id') || !$get('grado_id')),
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
                    ->default(now())
                    ->rules([new FechaNoPosterior()])
                    ->helperText('No puede ser una fecha futura'),
            ]);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $created = null;
        $createdCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        if (isset($data['logros']) && is_array($data['logros'])) {
            $logros = $data['logros'];
            unset($data['logros']);
            
            foreach ($logros as $logroId) {
                // Verificar si ya existe
                $existe = \App\Models\EstudianteLogro::where('estudiante_id', $data['estudiante_id'])
                    ->where('logro_id', $logroId)
                    ->where('periodo_id', $data['periodo_id'])
                    ->exists();
                    
                if (!$existe) {
                    // Validaciones adicionales
                    $logro = \App\Models\Logro::with('materia.grados')->find($logroId);
                    $estudiante = \App\Models\Estudiante::find($data['estudiante_id']);
                    
                    // Verificar que el logro existe y está activo
                    if (!$logro || !$logro->activo) {
                        $errorCount++;
                        continue;
                    }
                    
                    // Verificar que el estudiante existe y está activo
                    if (!$estudiante || !$estudiante->activo) {
                        $errorCount++;
                        continue;
                    }
                    
                    // Verificar que la materia del logro pertenece al grado del estudiante
                    if (!$logro->materia->grados->contains($estudiante->grado_id)) {
                        $errorCount++;
                        continue;
                    }
                    
                    // Si es profesor, verificar que puede enseñar esta materia
                    $user = auth()->user();
                    if ($user && $user->hasRole('profesor')) {
                        $puedeEnsenar = $user->materias()->where('id', $logro->materia_id)->exists();
                        if (!$puedeEnsenar) {
                            $errorCount++;
                            continue;
                        }
                    }
                    
                    // Si todas las validaciones pasan, crear el registro
                    $created = \App\Models\EstudianteLogro::create([
                        'estudiante_id' => $data['estudiante_id'],
                        'logro_id' => $logroId,
                        'periodo_id' => $data['periodo_id'],
                        'nivel_desempeno' => $data['nivel_desempeno'],
                        'observaciones' => $data['observaciones'] ?? null,
                        'fecha_asignacion' => $data['fecha_asignacion'],
                    ]);
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            // Notificación personalizada
            if ($createdCount > 0) {
                $message = "Se crearon {$createdCount} nota(s) exitosamente.";
                if ($skippedCount > 0) {
                    $message .= " Se omitieron {$skippedCount} nota(s) que ya existían.";
                }
                if ($errorCount > 0) {
                    $message .= " {$errorCount} nota(s) no se pudieron crear debido a errores de validación.";
                }
                
                Notification::make()
                    ->success()
                    ->title('Notas procesadas')
                    ->body($message)
                    ->send();
            } else {
                // Si no se creó ningún registro, mostrar error
                $message = 'No se pudo crear ninguna nota.';
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} nota(s) ya existían.";
                }
                if ($errorCount > 0) {
                    $message .= " {$errorCount} nota(s) fallaron las validaciones (verifique que la materia pertenezca al grado del estudiante y que tenga permisos).";
                }
                
                Notification::make()
                    ->danger()
                    ->title('Error al crear notas')
                    ->body($message)
                    ->send();
            }
        }
        
        return $created ?: new \App\Models\EstudianteLogro();
    }
} 