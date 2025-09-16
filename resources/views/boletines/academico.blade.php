<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Boletín Académico</title>
    <style>
        @page {
            size: letter;
            margin: 1.5cm 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            margin: 0;
            font-size: 10pt;
        }

        /* Encabezado institucional */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-table td {
            padding: 2px 5px;
            vertical-align: middle;
        }

        .institucion-titulo {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            font-style: italic; /* Cursiva como en el Word */
        }

        .institucion-info {
            font-size: 9pt;
            text-align: center;
            line-height: 1.3;
        }

        .logo {
            width: 70px;
            height: auto;
        }

        .foto {
            width: 80px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #000;
        }

        /* Tabla de datos del estudiante */
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .student-table td, .student-table th {
            border: 1px solid #000;
            font-size: 10pt;
            padding: 4px 8px;
        }

        .student-table th {
            font-size: 8pt !important;
        }

        .student-table th {
            background: #fff;
            font-weight: bold;
            text-align: center;
        }

        /* Títulos de secciones */
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 15px 0 8px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        /* Tabla de áreas y asignaturas */
        .area-header {
            font-weight: bold;
            font-size: 9pt;
            margin-top: 12px;
            margin-bottom: 3px;
        }

        .asignatura-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .asignatura-table td {
            border: 1px solid #000;
            padding: 3px 8px;
            font-size: 10pt;
        }

        .area-titulo-custom, th.area-titulo-custom {
            font-weight: bold !important;
            padding: 4px 8px !important;
            margin-top: 10px !important;
            font-size: 8pt !important;
        }

        .asignatura-table th {
            font-size: 9pt;
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

        /* Tabla consolidado */
        .consolidado-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9pt;
        }

        .consolidado-table th, .consolidado-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            text-align: center;
        }

        .consolidado-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }

        .consolidado-area {
            text-align: left !important;
            font-weight: lighter;
        }

        .consolidado-asignatura {
            text-align: left !important;
            padding-left: 10px !important;
            font-size: 9pt;
            font-weight: bold;
        }

        /* Cuadro de valores */
        .valores-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 8pt;
        }

        .valores-table td {
            border: 1px solid #000;
            padding: 4px 8px;
        }

        .valores-table .titulo-celda {
            border: none;
            font-weight: bold;
            text-align: left;
            font-size: 9pt;
        }

        .valor-descripcion {
            width: 45%;
            font-size: 9pt;
        }

        .valor-espacio {
            width: 10%;
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

        /* Firmas */
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

        /* Ajustes para impresión */
        @media print {
            body {
                font-size: 10pt;
            }

            .page-break {
                page-break-after: always;
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
                    $gradoGrupo .= ' ' ($grupo);
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
        <td colspan="2" style="font-weight: bold; font-size: 9pt;">DIRECTOR(A) DE GRUPO: DOC. {{ strtoupper($estudiante->grado->directorGrupo->name ?? 'No asignado') }}</td>
        <td colspan="3" style="font-weight: bold; font-size: 9pt;">INASISTENCIA: {{ $estudiante->inasistencias ?? '' }}</td>
    </tr>
</table>

<!-- Título principal -->
<div class="section-title">INFORME DE DESEMPEÑO ACADÉMICO Y CONVIVENCIAL</div>

<!-- Informe del periodo -->
<div class="section-title">INFORME {{ $periodo->numero_periodo == 1 ? 'PRIMER' : 'SEGUNDO' }} PERIODO</div>

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
    @endphp
    @foreach($materias as $materia => $desempenos)
        @php
            $desempenoActual = $desempenos->sortByDesc(function($d) {
                return $d->periodo->numero_periodo . '_' . $d->periodo->corte;
            })->first();
        @endphp
        @if($desempenoActual)
            <table class="asignatura-table" style="margin-bottom: 18px;">
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
        @endif
    @endforeach
@endforeach



<!-- Consolidado de valoraciones -->
<div class="section-title" style="margin-top: 30px;">CONSOLIDADO DE VALORACIONES DEL PROCESO FORMATIVO INTEGRAL</div>
<div class="subtitle">Que corresponde a la evaluación por procesos y no por promedios</div>
<table style="width:100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 10px;">
    <tr>
        <td rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: left; padding: 4px 8px;">
            ESCALA CONCEPTUAL: E.C
        </td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px 8px;">
            E: Excelente (Desempeño Superior) = 5
        </td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px 8px;">
            S: Sobresaliente (Desempeño Alto) = 4
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px 8px;">
            A: Aceptable (Desempeño Básico) = 3
        </td>
        <td style="border: 1px solid #000; text-align: center; font-weight: bold; padding: 4px 8px;">
            I: Insuficiente (Desempeño Bajo) = 2 - 1
        </td>
    </tr>
</table>

@php
    $esOnce = false;
    $gradoNombre = strtolower($estudiante->grado->nombre ?? '');
    if (strpos($gradoNombre, 'once') !== false || strpos($gradoNombre, '11') !== false || strpos($gradoNombre, 'undécimo') !== false) {
        $esOnce = true;
    }
@endphp
<table class="consolidado-table">
    <thead>
        <tr>
            <th rowspan="2" style="width: 25%;">ÁREA</th>
            <th rowspan="2" style="width: 35%;">Asignatura</th>
            <th rowspan="2" style="width: 5%;">IH</th>
            @if($esOnce)
                <th colspan="2" style="width: 17.5%;">1 Periodo</th>
                <th colspan="2" style="width: 17.5%;">2 Periodo</th>
            @else
                <th>1 Periodo</th>
                <th>2 Periodo</th>
            @endif
        </tr>
        <tr>
            @if($esOnce)
                <th>Desempeño</th>
                <th>Valoración</th>
                <th>Desempeño</th>
                <th>Valoración</th>
            @else
                <th>Desempeño</th>
                <th>Desempeño</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php
            $areasConsolidado = [];
            foreach($desempenosPorMateria as $materia => $desempenos) {
                if($desempenos->isNotEmpty()) {
                    $area = $desempenos->first()->materia->area ?? 'SIN ÁREA';
                    if(!isset($areasConsolidado[$area])) {
                        $areasConsolidado[$area] = [];
                    }
                    $areasConsolidado[$area][$materia] = $desempenos;
                }
            }
        @endphp

        @foreach($areasConsolidado as $area => $materias)
            @php
                $isFirstRow = true;
                $areaFormateada = $area ? ucwords(str_replace('_', ' ', strtolower($area))) : '';
            @endphp
            @foreach($materias as $materia => $desempenos)
                <tr>
                    @if($isFirstRow)
                        <td rowspan="{{ count($materias) }}" class="consolidado-area" style="font-size:8pt;">{{ $areaFormateada }}</td>
                        @php $isFirstRow = false; @endphp
                    @endif
                    <td class="consolidado-asignatura">{{ $materia }}</td>
                    <td>{{ $desempenos->first()->materia->horas_semanales ?? '' }}</td>
                    @php
                        $periodo1 = '';
                        $valoracion1 = '';
                        $periodo2 = '';
                        $valoracion2 = '';
                        foreach($desempenos as $desempeno) {
                            if($desempeno->periodo->numero_periodo == 1 && $desempeno->periodo->corte == 'Segundo Corte') {
                                $periodo1 = $desempeno->nivel_desempeno;
                                $valoracion1 = $desempeno->valor_numerico ?? '';
                            }
                            if($desempeno->periodo->numero_periodo == 2 && $desempeno->periodo->corte == 'Segundo Corte') {
                                $periodo2 = $desempeno->nivel_desempeno;
                                $valoracion2 = $desempeno->valor_numerico ?? '';
                            }
                        }
                    @endphp
                    @if($esOnce)
                        <td>{{ $periodo1 }}</td>
                        <td>{{ $valoracion1 }}</td>
                        <td>{{ $periodo2 }}</td>
                        <td>{{ $valoracion2 }}</td>
                    @else
                        <td>{{ $periodo1 }}</td>
                        <td>{{ $periodo2 }}</td>
                    @endif
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<!-- Cuadro de valores -->
<table class="valores-table">
    <tr>
        <td colspan="4" class="titulo-celda">CUADRO DE VALORES</td>
    </tr>
    <tr>
        <td class="valor-descripcion"><strong>RESPETO:</strong> Escucha con atención a otros y respeta sus opiniones</td>
        <td class="valor-espacio"></td>
        <td class="valor-descripcion"><strong>RESOLUCIONES DE CONFLICTOS:</strong> Buscas soluciones ante situaciones difíciles que se le presentan</td>
        <td class="valor-espacio"></td>
    </tr>
    <tr>
        <td class="valor-descripcion"><strong>AMOR:</strong> Demuestra afecto hacia las personas de su entorno</td>
        <td class="valor-espacio"></td>
        <td class="valor-descripcion"><strong>CUMPLIMIENTO DE LA NORMA:</strong> Acata las normas y acepta los llamados de atención</td>
        <td class="valor-espacio"></td>
    </tr>
    <tr>
        <td class="valor-descripcion"><strong>TOLERANCIA:</strong> Acepta a los demás tal y como son</td>
        <td class="valor-espacio"></td>
        <td class="valor-descripcion"><strong>CAPACIDAD DE DIÁLOGO:</strong> Manifiesta sus emociones y sentimientos a través del diálogo</td>
        <td class="valor-espacio"></td>
    </tr>
    <tr>
        <td class="valor-descripcion"><strong>HONESTIDAD:</strong> Actúa y habla siempre con la verdad</td>
        <td class="valor-espacio"></td>
        <td class="valor-descripcion"><strong>GRATITUD:</strong> Usa palabras de cortesía y valora lo que otros hacen por su bien</td>
        <td class="valor-espacio"></td>
    </tr>
    <tr>
        <td class="valor-descripcion"><strong>AUTOESTIMA:</strong> Se acepta y valora tal como es</td>
        <td class="valor-espacio"></td>
        <td class="valor-descripcion"><strong>RESPONSABILIDAD:</strong> Es puntual y cumple oportunamente con sus compromisos</td>
        <td class="valor-espacio"></td>
    </tr>
    <tr>
        <td class="valor-descripcion"><strong>SOLIDARIDAD:</strong> Es sensible y compasivo a las necesidades de los demás</td>
        <td class="valor-espacio"></td>
        <td class="valor-descripcion"><strong>PERSEVERANCIA:</strong> Es constante en todo lo que realiza a pesar de los errores</td>
        <td class="valor-espacio"></td>
    </tr>
</table>

<!-- Observaciones -->
<div class="observaciones-section">
    <div class="observaciones-titulo">Observaciones y/o Recomendaciones:</div>
    @if(isset($estudiante->observaciones_disciplina))
        <div class="observaciones-texto">
            {{ $estudiante->observaciones_disciplina }}
        </div>
    @else
        <div class="observaciones-texto" style="min-height: 60px;">
            <!-- Espacio para observaciones -->
        </div>
    @endif
</div>

<!-- Firmas -->
<table class="sign-table">
    <tr>
        <td>
            <div class="firma-line"></div>
            {{strtoupper($estudiante->grado->directorGrupo->name ?? 'No asignado') }}
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

</body>
</html>
