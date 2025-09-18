<?php

namespace App\Imports;

use App\Models\Logro;
use App\Models\Materia;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class LogrosImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $materiaId;
    protected $importResults = [
        'created' => 0,
        'updated' => 0,
        'errors' => []
    ];

    public function __construct($materiaId = null)
    {
        $this->materiaId = $materiaId;
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Si se especificó una materia para la importación, usarla
        if ($this->materiaId) {
            $materia = Materia::find($this->materiaId);
        } else {
            // Buscar por el código de materia en el Excel
            $materia = Materia::where('codigo', $row['codigo_materia'])->first();
            
            // Si no se encuentra por código, buscar por nombre
            if (!$materia) {
                $materia = Materia::where('nombre', $row['materia'])->first();
            }
        }
        
        if (!$materia) {
            $materiaInfo = $this->materiaId ? "ID: {$this->materiaId}" : "Código: '{$row['codigo_materia']}' o Nombre: '{$row['materia']}'";
            $this->importResults['errors'][] = "Materia {$materiaInfo} no encontrada para logro {$row['codigo']}";
            return null;
        }

        // Verificar permisos: si es profesor, solo puede importar en sus materias
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if ($user->hasRole('profesor') && $materia->docente_id !== $user->id) {
                $this->importResults['errors'][] = "Sin permisos para importar logros en la materia {$materia->nombre} ({$materia->codigo})";
                return null;
            }
        }

        // Verificar si el logro ya existe por código
        $logro = Logro::where('codigo', $row['codigo'])->first();

        if ($logro) {
            // Actualizar logro existente
            $logro->update([
                'titulo' => $row['titulo'] ?? null,
                'desempeno' => $row['desempeno'],
                'materia_id' => $materia->id,
                'orden' => (int)($row['orden'] ?? 0),
                'activo' => true,
            ]);
            $this->importResults['updated']++;
            return null;
        } else {
            // Crear nuevo logro
            $this->importResults['created']++;
            return new Logro([
                'codigo' => $row['codigo'],
                'titulo' => $row['titulo'] ?? null,
                'desempeno' => $row['desempeno'],
                'materia_id' => $materia->id,
                'orden' => (int)($row['orden'] ?? 0),
                'activo' => true,
            ]);
        }
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        $rules = [
            'codigo' => 'required|string|max:255',
            'titulo' => 'nullable|string|max:255',
            'desempeno' => 'required|string',
            'orden' => 'nullable|integer|min:0',
        ];

        // Si no se especificó materia en el constructor, validar que existe en el Excel
        if (!$this->materiaId) {
            $rules['codigo_materia'] = 'required|string';
            $rules['materia'] = 'required|string';
        }

        return $rules;
    }

    /**
     * Mensajes de validación personalizados
     */
    public function customValidationMessages()
    {
        return [
            'codigo.required' => 'El campo código es obligatorio.',
            'codigo.unique' => 'El código del logro ya existe.',
            'desempeno.required' => 'El campo desempeño es obligatorio.',
            'codigo_materia.required' => 'El código de materia es obligatorio.',
            'materia.required' => 'El nombre de materia es obligatorio.',
            'orden.integer' => 'El orden debe ser un número entero.',
            'orden.min' => 'El orden debe ser mayor o igual a 0.',
        ];
    }

    /**
     * Obtener resultados de la importación
     */
    public function getImportResults()
    {
        return $this->importResults;
    }

    /**
     * Heading row para mapear columnas
     */
    public function headingRow(): int
    {
        return 1;
    }
}
