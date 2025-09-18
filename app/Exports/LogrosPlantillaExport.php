<?php

namespace App\Exports;

use App\Models\Materia;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LogrosPlantillaExport implements FromArray, WithHeadings, WithStyles
{
    protected $incluirMateria;
    protected $materiaNombre;
    protected $materiaCodigo;

    public function __construct($incluirMateria = true, $materiaNombre = null, $materiaCodigo = null)
    {
        $this->incluirMateria = $incluirMateria;
        $this->materiaNombre = $materiaNombre;
        $this->materiaCodigo = $materiaCodigo;
    }

    public function array(): array
    {
        // Ejemplos de datos para la plantilla
        $ejemplos = [
            [
                'LOG001',
                'Comprensión Lectora',
                'Identifica ideas principales y secundarias en textos narrativos',
                $this->materiaNombre ?? 'Lengua Castellana',
                $this->materiaCodigo ?? 'ESP001',
            ],
            [
                'LOG002',
                'Análisis Textual',
                'Analiza elementos gramaticales y sintácticos en oraciones compuestas',
                $this->materiaNombre ?? 'Lengua Castellana',
                $this->materiaCodigo ?? 'ESP001',
            ],
            [
                'LOG003',
                'Producción Textual',
                'Produce textos argumentativos con coherencia y cohesión adecuadas',
                $this->materiaNombre ?? 'Lengua Castellana',
                $this->materiaCodigo ?? 'ESP001',
            ]
        ];

        // Si no se debe incluir materia, quitar las columnas correspondientes
        if (!$this->incluirMateria) {
            foreach ($ejemplos as $key => $ejemplo) {
                unset($ejemplos[$key][3]); // Quitar columna materia
                unset($ejemplos[$key][4]); // Quitar columna código materia
                $ejemplos[$key] = array_values($ejemplos[$key]); // Reindexar
            }
        }

        return $ejemplos;
    }

    public function headings(): array
    {
        $headings = [
            'codigo',
            'titulo',
            'desempeno',
        ];

        if ($this->incluirMateria) {
            $headings[] = 'materia';
            $headings[] = 'codigo_materia';
        }

        // Columna 'orden' eliminada
        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Auto ajustar ancho de columnas
        $lastColumn = $this->incluirMateria ? 'E' : 'D';
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Ajuste específico para columna de desempeño
        $sheet->getColumnDimension('C')->setWidth(50);

        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF28A745'],
                ],
            ],
        ];
    }
}