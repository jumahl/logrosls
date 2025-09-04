<?php

namespace App\Imports;

use App\Models\Estudiante;
use App\Models\Grado;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Carbon\Carbon;

class EstudiantesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $gradoId;
    protected $importResults = [
        'created' => 0,
        'updated' => 0,
        'errors' => []
    ];

    public function __construct($gradoId = null)
    {
        $this->gradoId = $gradoId;
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Si se especificó un grado para la importación, usarlo
        if ($this->gradoId) {
            $grado = Grado::find($this->gradoId);
        } else {
            // Buscar por el nombre en el Excel
            $grado = Grado::where('nombre', $row['grado'])->first();
        }
        
        if (!$grado) {
            $gradoInfo = $this->gradoId ? "ID: {$this->gradoId}" : "Nombre: '{$row['grado']}'";
            $this->importResults['errors'][] = "Grado {$gradoInfo} no encontrado para estudiante {$row['nombres']} {$row['apellidos']}";
            return null;
        }

        // Verificar si el estudiante ya existe por documento
        $estudiante = Estudiante::where('documento', $row['documento'])->first();

        if ($estudiante) {
            // Actualizar estudiante existente
            $estudiante->update([
                'nombre' => $row['nombres'],
                'apellido' => $row['apellidos'],
                'fecha_nacimiento' => $this->parseDate($row['fecha_nacimiento']),
                'direccion' => $row['direccion'] ?? null,
                'telefono' => $row['telefono'] ?? null,
                'email' => $row['email'] ?? null,
                'grado_id' => $grado->id,
                'activo' => true,
            ]);
            $this->importResults['updated']++;
            return null;
        } else {
            // Crear nuevo estudiante
            $this->importResults['created']++;
            return new Estudiante([
                'nombre' => $row['nombres'],
                'apellido' => $row['apellidos'],
                'documento' => $row['documento'],
                'fecha_nacimiento' => $this->parseDate($row['fecha_nacimiento']),
                'direccion' => $row['direccion'] ?? null,
                'telefono' => $row['telefono'] ?? null,
                'email' => $row['email'] ?? null,
                'grado_id' => $grado->id,
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
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'documento' => 'required|string|max:255',
            'fecha_nacimiento' => 'required',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ];

        // Si no se especificó grado en el constructor, validar que existe en el Excel
        if (!$this->gradoId) {
            $rules['grado'] = 'required|string|exists:grados,nombre';
        }

        return $rules;
    }

    /**
     * Mensajes de validación personalizados
     */
    public function customValidationMessages()
    {
        return [
            'nombres.required' => 'El campo nombres es obligatorio.',
            'apellidos.required' => 'El campo apellidos es obligatorio.',
            'documento.required' => 'El campo documento es obligatorio.',
            'fecha_nacimiento.required' => 'El campo fecha de nacimiento es obligatorio.',
            'grado.required' => 'El campo grado es obligatorio.',
            'grado.exists' => 'El grado especificado no existe.',
            'email.email' => 'El formato del email no es válido.',
        ];
    }

    /**
     * Parsear fecha desde diferentes formatos
     */
    private function parseDate($date)
    {
        if (!$date) {
            return null;
        }

        // Si ya es una instancia de Carbon
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d');
        }

        // Si es un número (Excel date)
        if (is_numeric($date)) {
            try {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($date - 2)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Intentar parsear diferentes formatos de fecha
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
        
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
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
