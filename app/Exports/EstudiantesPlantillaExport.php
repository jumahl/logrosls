<?php

namespace App\Exports;

use App\Models\Grado;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EstudiantesPlantillaExport implements FromArray, WithHeadings, WithStyles
{
    protected $incluirGrado;
    protected $gradoNombre;

    public function __construct($incluirGrado = true, $gradoNombre = null)
    {
        $this->incluirGrado = $incluirGrado;
        $this->gradoNombre = $gradoNombre;
    }

    public function array(): array
    {
        // Ejemplos de datos para la plantilla
        $ejemplos = [
            [
                'Juan Carlos',
                'Pérez González',
                '1234567890',
                '2010-05-15',
                $this->gradoNombre ?? 'Séptimo',
                'Calle 123 #45-67',
                '3001234567',
                'juan.perez@email.com'
            ],
            [
                'María Fernanda',
                'López Martínez',
                '0987654321',
                '2009-12-20',
                $this->gradoNombre ?? 'Octavo',
                'Carrera 45 #12-34',
                '3009876543',
                'maria.lopez@email.com'
            ],
            [
                'Carlos Andrés',
                'Rodríguez Silva',
                '1122334455',
                '2011-03-10',
                $this->gradoNombre ?? 'Sexto',
                'Avenida 67 #89-12',
                '3001122334',
                'carlos.rodriguez@email.com'
            ]
        ];

        // Si no se debe incluir grado, quitarlo de los ejemplos
        if (!$this->incluirGrado) {
            foreach ($ejemplos as $key => $ejemplo) {
                unset($ejemplos[$key][4]); // Quitar columna de grado
                $ejemplos[$key] = array_values($ejemplos[$key]); // Reindexar
            }
        }

        return $ejemplos;
    }

    public function headings(): array
    {
        $headings = [
            'nombres',
            'apellidos', 
            'documento',
            'fecha_nacimiento',
        ];

        if ($this->incluirGrado) {
            $headings[] = 'grado';
        }

        $headings = array_merge($headings, [
            'direccion',
            'telefono',
            'email'
        ]);

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Auto ajustar ancho de columnas
        $lastColumn = $this->incluirGrado ? 'H' : 'G';
        foreach (range('A', $lastColumn) as $column) {
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