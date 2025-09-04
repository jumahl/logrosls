<?php

namespace App\Exports;

use App\Models\Logro;
use App\Models\Materia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LogrosExport implements FromCollection, WithHeadings, WithStyles, WithMapping
{
    protected $materiaId;

    public function __construct($materiaId = null)
    {
        $this->materiaId = $materiaId;
    }

    public function collection()
    {
        $query = Logro::with(['materia', 'materia.docente'])->where('activo', true);

        // Si se especifica una materia, filtrar por esa materia
        if ($this->materiaId) {
            $query->where('materia_id', $this->materiaId);
        }

        // Si es profesor, solo puede ver logros de materias que enseña
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if ($user->hasRole('profesor')) {
                $materiasDelProfesor = Materia::where('docente_id', $user->id)->pluck('id');
                $query->whereIn('materia_id', $materiasDelProfesor);
            }
        }

        return $query->orderBy('materia_id')->orderBy('orden')->orderBy('codigo')->get();
    }

    public function map($logro): array
    {
        return [
            $logro->codigo,
            $logro->titulo ?? '',
            $logro->desempeno,
            $logro->materia->nombre ?? '',
            $logro->materia->codigo ?? '',
            $logro->materia->docente->name ?? '',
            $logro->orden,
            $logro->activo ? 'Activo' : 'Inactivo',
            $logro->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'Código',
            'Título',
            'Desempeño/Descripción',
            'Materia',
            'Código Materia',
            'Docente',
            'Orden',
            'Estado',
            'Fecha Registro'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Auto ajustar ancho de columnas
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Ajuste específico para columnas de texto largo
        $sheet->getColumnDimension('C')->setWidth(50); // Desempeño
        $sheet->getColumnDimension('D')->setWidth(25); // Materia

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
