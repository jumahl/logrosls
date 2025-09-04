<?php

namespace App\Exports;

use App\Models\Estudiante;
use App\Models\Grado;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EstudiantesExport implements FromCollection, WithHeadings, WithStyles, WithMapping
{
    protected $gradoId;

    public function __construct($gradoId = null)
    {
        $this->gradoId = $gradoId;
    }

    public function collection()
    {
        $query = Estudiante::with('grado')->where('activo', true);

        // Si se especifica un grado, filtrar por ese grado
        if ($this->gradoId) {
            $query->where('grado_id', $this->gradoId);
        }

        // Si es profesor, solo puede ver estudiantes de grados donde enseña
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if ($user->hasRole('profesor')) {
                // Obtener grados donde el usuario es docente de alguna materia
                $gradosDelProfesor = Grado::whereHas('materias', function($q) use ($user) {
                    $q->where('docente_id', $user->id);
                })->pluck('id');

                $query->whereIn('grado_id', $gradosDelProfesor);
            }
        }

        return $query->orderBy('grado_id')->orderBy('apellido')->orderBy('nombre')->get();
    }

    public function map($estudiante): array
    {
        return [
            $estudiante->nombre,
            $estudiante->apellido,
            $estudiante->documento,
            $estudiante->fecha_nacimiento ? $estudiante->fecha_nacimiento->format('Y-m-d') : '',
            $estudiante->grado->nombre ?? '',
            $estudiante->direccion ?? '',
            $estudiante->telefono ?? '',
            $estudiante->email ?? '',
            $estudiante->activo ? 'Activo' : 'Inactivo',
            $estudiante->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'Nombres',
            'Apellidos', 
            'Documento',
            'Fecha Nacimiento',
            'Grado',
            'Dirección',
            'Teléfono',
            'Email',
            'Estado',
            'Fecha Registro'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Auto ajustar ancho de columnas
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0070C0'],
                ],
            ],
        ];
    }
}