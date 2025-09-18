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
            size: letter;
            margin: 1.5cm 2cm;
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
            width: 70px;
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
            text-align: left;
        }
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
        .asignatura-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
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

        .area-titulo-custom {
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
            border: 1px solid #ccc;
            padding: 5px;
            font-size: 9pt;
        }
        .observaciones {
            margin-top: 18px;
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
        @media print {
            body {
                font-size: 10pt;
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
        <th style="width: 40%; border-right: 1px solid #000;font-size: 8pt;">APELLIDOS Y NOMBRES DEL ESTUDIANTE</th>
        <th style="width: 20%; border-right: 1px solid #000;font-size: 8pt;">GRADO</th>
        <th style="width: 20%; border-right: 1px solid #000;font-size: 8pt;">PERIODO</th>
        <th style="width: 20%; border-right: 1px solid #000;font-size: 8pt;">AÑO</th>
    </tr>
    <tr>
        <td style="font-weight: bold; border-right: 1px solid #000; font-size: 11pt;">
            @php
                $nombreCompleto = ucwords(strtolower(($estudiante->apellido ?? '') . ' ' . ($estudiante->nombre ?? '')));
            @endphp
            {{ $nombreCompleto }}
        </td>
        <td style="text-align: center; border-right: 1px solid #000; font-size: 11pt;">
            @php
                $gradoNombre = $estudiante->grado->nombre ?? '';
                $grupo = $estudiante->grado->grupo ?? '';
                $gradoGrupo = $gradoNombre;
                if ($grupo) {
                    $gradoGrupo .= ' ' . strtoupper($grupo);
                }
            @endphp
            {{ $gradoGrupo }}
        </td>
        <td style="text-align: center; border-right: 1px solid #000; font-size: 11pt;">
            {{ ($periodo->corte ?? '') . ' - ' . ($periodo->numero_periodo ?? 'I') }}
        </td>
        <td style="text-align: center; border-right: 1px solid #000; font-size: 11pt;">
            {{ $periodo->anio_escolar ?? now()->year }}
        </td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold; font-size: 9pt;">Director de grupo: {{ $estudiante->grado->directorGrupo->name ?? 'No asignado' }}</td>
        <td colspan="2" style="font-weight: bold; font-size: 9pt;">Inasistencia: {{ $estudiante->inasistencias ?? '' }}</td>
    </tr>
</table>

<!-- Título principal -->
<div class="section-title">PRE-INFORME ACADÉMICO Y DISCIPLINARIO</div>
<div class="section-title">INFORME {{ $periodo->numero_periodo == 1 ? 'PRIMER' : 'SEGUNDO' }} PERIODO</div>
<div class="subtitle">Comprendido entre: el {{ $periodo->fecha_inicio->format('d/m/Y') }} y el {{ $periodo->fecha_fin->format('d/m/Y') }}</div>

<!-- Áreas y asignaturas -->
@php $areaAnterior = null; @endphp
@foreach($desempenosPorMateria as $materia => $desempenos)
    @php
        $desempenoActual = $desempenos->sortByDesc(function($d) {
            return $d->periodo->numero_periodo . '_' . $d->periodo->corte;
        })->first();
        $areaActual = $desempenoActual ? ($desempenoActual->materia->area ?? 'SIN ÁREA') : null;
        $areaFormateada = $areaActual ? strtoupper(str_replace('_', ' ', $areaActual)) : '';
    @endphp
    @if($desempenoActual)
        @if($areaActual !== $areaAnterior)
            <div class="area-titulo-custom">ÁREA: {{ $areaFormateada }}</div>
            @php $areaAnterior = $areaActual; @endphp
        @endif
        <table class="asignatura-table" style="margin-bottom: 18px;">
            <tr>
                <th style="width:30%"></th>
                <th style="width:20%">Escala Valoración</th>
                <th style="width:20%">Nivel de Desempeño</th>
                <th style="width:30%">Docente</th>
            </tr>
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
                <td class="docente-col">{{ $desempenoActual->materia->docente->name ?? 'N/A' }}</td>
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
            {{ $estudiante->grado->directorGrupo->name ?? 'No asignado' }}
            <br>
            DIRECTOR DE GRUPO
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
