<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preinforme Académico</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            margin: 0;
            font-size: 10pt;
        }
        @page {
            size: legal;
            margin: 1.2cm 1.5cm 2.5cm 1.5cm;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-table td {
            padding: 2px 5px;
            vertical-align: middle;
            font-size: 9pt;
        }
        .institucion-titulo {
            font-size: 13pt !important;
            font-weight: bold;
            text-align: center;
            font-style: italic;
        }
        .institucion-info {
            font-size: 9pt;
            text-align: center;
            line-height: 1.3;
        }
        .logo {
            width: 80px;
            height: auto;
        }
        .foto {
            width: 80px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #000;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .student-table td, .student-table th {
            border: 1px solid #000;

            padding: 4px 8px;
        }
        .student-table th {
            background: #fff;
            font-weight: bold;
            text-align: center;
        }
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 12px 0 6px 0; 
        }
        .subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        .asignatura-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        .asignatura-table td {
            border: 1px solid #000;
            padding: 3px 8px;
            font-size: 10pt;
        }

        .asignatura-table th {
            font-size: 9pt;
        }

        .area-titulo-custom, th.area-titulo-custom {
            font-weight: bold;
            padding: 4px 8px;
            margin-top: 10px;
            font-size: 8pt;
        }

        .asignatura-nombre {
            font-weight: bold;
            width: 40%;
        }

        .escala-col {
            width: 15%;
            text-align: center;
            font-weight: bold;
        }

        .nivel-col {
            width: 20%;
            text-align: center;
        }

        .docente-col {
            width: 25%;
            text-align: center;
        }

        /* Logros */
        .logros-cell {
            padding: 5px 10px !important;
            font-size: 9pt;
            line-height: 1.4;
            border-top: 1px solid #000 !important;
            border-left: none !important;
            border-right: none !important;
            border-bottom: none !important;
        }

        .logros-list {
            margin: 0;
            padding-left: 15px;
        }

        .logros-list li {
            margin-bottom: 3px;
        }

        /* Observaciones */
        .observaciones-section {
            margin-top: 15px;
            font-size: 9pt;
        }

        .observaciones-titulo {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .observaciones-texto {
            min-height: 40px;
            padding: 5px;
            font-size: 9pt;
        }

        .sign-table {
            width: 100%;
            margin-top: 40px;
        }
        .sign-table td {
            padding: 30px 20px 5px 20px;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            width: 50%;
        }
        .firma-line {
            border-top: 1px solid #000;
            width: 70%;
            margin: 0 auto 5px auto;
        }

        /* Pie de página */
        .footer-text {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            font-family: 'Arial', Times, serif;
            padding: 5px 1.5cm;
            background: white;
            color: #807e7e;
            z-index: 1000;
            display: block !important;
            height: auto;
            line-height: 1.1;
        }
        
        /* Pie de página duplicado para primera página */
        .footer-text-first {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            font-family: 'Arial', Times, serif;
            padding: 5px 1.5cm; 
            background: white;
            color: #807e7e;
            z-index: 1001;
            display: block !important;
            height: auto;
            line-height: 1.1; 
        }
        
        /* Asegurar espacio para el pie de página */
        body {
            margin-bottom: 40px;
        }

        @media print {
            body {
                font-size: 10pt;
                margin-bottom: 40px !important;
            }
            
            .footer-text, .footer-text-first {
                position: fixed !important;
                bottom: 10px !important;
                left: 0 !important;
                right: 0 !important;
                display: block !important;
                background: white !important;
                z-index: 9999 !important;
                font-size: 9pt !important;
                padding: 3px 1.5cm !important;
            }
            
            /* Agregar contenido de pie usando CSS para tamaño oficio */
            @page {
                size: legal;
                margin: 1.2cm 1.5cm 2.5cm 1.5cm;
                @bottom-center {
                    content: "Es deber de los padres de familia acompañar el proceso educativo en cumplimiento de su responsabilidad como primeros educadores de sus hijos para mejorar la orientación personal y el desarrollo de los valores";
                    font-size: 7pt;
                    font-family: Arial;
                    color: #807e7e;
                }
            }
        }
        
        /* Estilo específico para DomPDF */
        @media dompdf {
            .footer-text, .footer-text-first {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 8pt;
                background: white;
                z-index: 9999;
            }
        }
    </style>
</head>
<body>

<!-- Encabezado institucional -->
<table class="header-table">
    <tr>
        <td rowspan="4" style="width: 10%; text-align: center;">
            <img src="{{ public_path('liceo.png') }}" class="logo">
        </td>
        <td colspan="2" class="institucion-titulo">
            INSTITUCIÓN EDUCATIVA "LICEO DEL SABER"
        </td>
        <td rowspan="4" style="width: 15%; text-align: center;">
            @if(isset($estudiante->foto))
                <img src="{{ public_path('fotos/'.$estudiante->foto) }}" class="foto">
            @else
                <img src="{{ public_path('fotos/default.png') }}" class="foto">
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2" class="institucion-info">
            Aprobado según resolución No. 01199 del 03 de Abril de 2018<br>
            Preescolar, Básica Primaria, Secundaria y Media Académica
        </td>
    </tr>
    <tr>
        <td colspan="2" class="institucion-info">
            Transversal 6 diagonal 3 esquina No. 7 - 05 B/ Los Lagos III etapa Zarzal - Valle del Cauca<br>
            Tel. 2208019 – 3168207306 E-mail: ieliceodelsaber@hotmail.com
        </td>
    </tr>
</table>

<!-- Datos del estudiante -->
<table class="student-table">
    <tr>
        <th style="width: 45%; border-right: 1px solid #000;font-size: 8pt;">APELLIDOS Y NOMBRES DEL ESTUDIANTE</th>
        <th style="width: 20%; border-right: 1px solid #000;font-size: 9pt;">NIVEL</th>
        <th style="width: 15%; border-right: 1px solid #000;font-size: 9pt;">GRADO</th>
        <th style="width: 10%; border-right: 1px solid #000;font-size: 9pt;">PERIODO</th>
        <th style="width: 10%; border-right: 1px solid #000;font-size: 9pt;">AÑO</th>
    </tr>
    <tr>
        <td style="font-weight: bold; border-right: 1px solid #000; font-size: 11pt;">
            @php
                $nombreCompleto = ucwords(strtolower(($estudiante->apellido ?? '') . ' ' . ($estudiante->nombre ?? '')));
            @endphp
            {{ strtoupper($nombreCompleto) }}
        </td>
        <td style="text-align: center; border-right: 1px solid #000; font-size: 10pt;">
            {{ strtoupper($estudiante->grado->tipo ?? '') }}
        </td>
        <td style="text-align: center; border-right: 1px solid #000; font-size: 10pt;">
            @php
                $gradoNombre = $estudiante->grado->nombre ?? '';
                $grupo = $estudiante->grado->grupo ?? '';
                $gradoGrupo = $gradoNombre;
                if ($grupo) {
                    $gradoGrupo .= ' ' . $grupo;
                }
            @endphp
            {{ strtoupper($gradoGrupo) }}
        </td>
        <td style="text-align: center; border-right: 1px solid #000; font-size: 10pt;">
            {{ ($periodo->numero_periodo ?? 'I') }}
        </td>
        <td style="text-align: center; border-right: 1px solid #000; font-size: 10pt;">
            {{ $periodo->anio_escolar ?? now()->year }}
        </td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold; font-size: 9pt;">DIRECTOR(A) DE GRUPO:{{ strtoupper($estudiante->grado->directorGrupo->name ?? 'No asignado') }}</td>
        <td colspan="3" style="font-weight: bold; font-size: 9pt;">INASISTENCIA: {{ $estudiante->inasistencias ?? '' }}</td>
    </tr>
</table>

<!-- Título principal -->
<div class="section-title">PRE-INFORME ACADÉMICO Y DISCIPLINARIO</div>
<div class="section-title">INFORME {{ $periodo->numero_periodo == 1 ? 'PRIMER' : 'SEGUNDO' }} PERIODO</div>
<div class="subtitle">Comprendido entre: el {{ $periodo->fecha_inicio->format('d/m/Y') }} y el {{ $periodo->fecha_fin->format('d/m/Y') }}</div>

<!-- Pie de página para primera página -->
<div class="footer-text-first">
    Es deber de los padres de familia acompañar el proceso educativo en cumplimiento de su responsabilidad como primeros educadores de sus hijos para mejorar la orientación personal y el desarrollo de los valores
</div>

<!-- Áreas y asignaturas -->
@php
// Agrupar materias por área y ordenar áreas alfabéticamente
$materiasPorArea = [];
foreach($desempenosPorMateria as $materia => $desempenos) {
    $desempenoActual = $desempenos->sortByDesc(function($d) {
        return $d->periodo->numero_periodo . '_' . $d->periodo->corte;
    })->first();
    $areaActual = $desempenoActual ? ($desempenoActual->materia->area ?? 'SIN ÁREA') : 'SIN ÁREA';
    $materiasPorArea[$areaActual][$materia] = $desempenos;
}
ksort($materiasPorArea);
@endphp
@foreach($materiasPorArea as $areaActual => $materias)
    @php
        $areaFormateada = $areaActual ? strtoupper(str_replace('_', ' ', $areaActual)) : '';
        $primeraMateria = true;
        $totalMaterias = count($materias);
        $materiaActual = 0;
    @endphp
    @foreach($materias as $materia => $desempenos)
        @php
            $materiaActual++;
            $desempenoActual = $desempenos->sortByDesc(function($d) {
                return $d->periodo->numero_periodo . '_' . $d->periodo->corte;
            })->first();
        @endphp
        @if($desempenoActual)
            <table class="asignatura-table" style="margin-bottom: 15px; page-break-inside: avoid;">
                @if($primeraMateria)
                <tr>
                    <th class="area-titulo-custom" style="width:30%; text-align: left;">ÁREA: {{ $areaFormateada }}</th>
                    <th style="width:20%">Escala Valoración</th>
                    <th style="width:20%">Nivel de Desempeño</th>
                    <th style="width:30%">Docente</th>
                </tr>
                @php $primeraMateria = false; @endphp
                @endif
                <tr>
                    <td class="asignatura-nombre">Asignatura: {{ $materia }}</td>
                    <td class="escala-col">{{ $desempenoActual->nivel_desempeno ?? 'N/A' }}</td>
                    <td class="nivel-col">
                        {{
                            $desempenoActual->nivel_desempeno == 'E' ? 'Superior' :
                            ($desempenoActual->nivel_desempeno == 'S' ? 'Alto' :
                            ($desempenoActual->nivel_desempeno == 'A' ? 'Básico' :
                            ($desempenoActual->nivel_desempeno == 'I' ? 'Bajo' : 'N/A')))
                        }}
                    </td>
                    <td class="docente-col">Doc. {{ $desempenoActual->materia->docente->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="logros-cell">
                        @if($desempenoActual->estudianteLogros->count() > 0)
                            @foreach($desempenoActual->estudianteLogros as $estudianteLogro)
                                @php
                                    $titulo = $estudianteLogro->logro->titulo;
                                    $desempeno = $estudianteLogro->logro->desempeno ?? '';
                                @endphp
                                <div style="margin-bottom: 4px;">
                                    @if($titulo)
                                        <span style="font-weight: bold;">{{ $titulo }}</span>
                                    @endif
                                    @if($titulo && $desempeno)
                                        <br>
                                    @endif
                                    @if($desempeno)
                                        {{ $desempeno }}
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            </table>
            @if($materiaActual % 4 == 0 && $materiaActual < $totalMaterias)
                <!-- Pie de página antes del salto -->
                <div class="footer-text" style="position: absolute; bottom: 10px;">
                    Es deber de los padres de familia acompañar el proceso educativo en cumplimiento de su responsabilidad como primeros educadores de sus hijos para mejorar la orientación personal y el desarrollo de los valores
                </div>
                <!-- Salto de página cada 4 materias (más aprovechamiento del espacio en oficio) -->
                <div style="page-break-after: always;"></div>
                <!-- Pie de página después del salto -->
                <div class="footer-text">
                    Es deber de los padres de familia acompañar el proceso educativo en cumplimiento de su responsabilidad como primeros educadores de sus hijos para mejorar la orientación personal y el desarrollo de los valores
                </div>
            @endif
        @endif
    @endforeach
@endforeach


<!-- Observaciones -->
<div class="observaciones-section">
    <div class="observaciones-titulo">Observaciones y/o Recomendaciones:</div>
    @if(isset($estudiante->observaciones_disciplina))
        <div class="observaciones-texto">
            {{ $estudiante->observaciones_disciplina }}
        </div>
    @else
        <div class="observaciones-texto" style="min-height: 60px;"></div>
    @endif
</div>

<!-- Firmas -->
<table class="sign-table">
    <tr>
        <td>
            <div class="firma-line"></div>
            {{ strtoupper($estudiante->grado->directorGrupo->name ?? 'No asignado') }}
            <br>
            DIRECTOR(A) DE GRUPO
        </td>
        <td>
            <div class="firma-line"></div>
            MELBA ARCO RUIZ
            <br>
            RECTORA

        </td>
    </tr>
</table>

<!-- Pie de página (solo una vez al final) -->
<div class="footer-text">
    Es deber de los padres de familia acompañar el proceso educativo en cumplimiento de su responsabilidad como primeros educadores de sus hijos para mejorar la orientación personal y el desarrollo de los valores
</div>

<!-- Script para asegurar que el pie aparezca en todas las páginas -->
<script type="text/php">
    if (isset($pdf)) {
        $text = "Es deber de los padres de familia acompañar el proceso educativo en cumplimiento de su responsabilidad como primeros educadores de sus hijos para mejorar la orientación personal y el desarrollo de los valores";
        $font = $fontMetrics->get_font("Arial", "normal");
        $size = 7; // Tamaño reducido para mejor aprovechamiento
        $color = array(0.5, 0.5, 0.5);
        
        // Obtener el número total de páginas
        $pageCount = $pdf->get_page_count();
        
        // Aplicar el pie a cada página - optimizado para oficio
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->open_object();
            $y = $pdf->get_height() - 25; // Más cerca del borde para tamaño oficio
            $x = 40; // Margen izquierdo optimizado
            $width = $pdf->get_width() - 80; // Ancho disponible
            
            // Centrar el texto
            $textWidth = $fontMetrics->get_text_width($text, $font, $size);
            $centerX = ($pdf->get_width() - $textWidth) / 2;
            
            $pdf->text($centerX, $y, $text, $font, $size, $color);
            $pdf->close_object();
            $pdf->add_object_to_page($i, $pdf->get_object());
        }
    }
</script>

</body>
</html>
