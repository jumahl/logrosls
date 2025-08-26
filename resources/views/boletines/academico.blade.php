<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Boletín Académico</title>
    <style>
        body { font-family: Arial, sans-serif; color: #000; margin: 20px; }
        .header-table, .student-table, .area-table, .sign-table { width: 100%; border-collapse: collapse; }
        .header-table td { font-size: 12px; }
        .logo { width: 90px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .student-table td, .student-table th { border: 1px solid #333; font-size: 12px; padding: 3px 6px; }
        .student-table th { background: #e0e0e0; }
        .area-table th, .area-table td { border: 1px solid #333; font-size: 12px; padding: 3px 6px; }
        .area-table th { background: #f5f5f5; }
        .asignatura-row { background: #f9f9f9; font-weight: bold; }
        .logros-list { margin: 0 0 8px 0; padding-left: 18px; font-size: 12px; }
        .logros-list li { margin-bottom: 2px; }
        .section-title { text-align: center; font-weight: bold; font-size: 15px; margin: 10px 0 4px 0; }
        .subtitle { text-align: center; font-size: 13px; margin-bottom: 8px; }
        .observaciones { margin-top: 18px; font-size: 12px; }
        .sign-table td { padding: 30px 10px 0 10px; text-align: center; font-size: 12px; }
        .firma-line { border-top: 1px solid #333; width: 80%; margin: 0 auto 2px auto; }
        .foto { width: 90px; height: 110px; object-fit: cover; border: 1px solid #aaa; }
    </style>
</head>
<body>

<!-- Encabezado institucional -->
<table class="header-table">
    <tr>
        <td rowspan="4" class="center"><img src="{{ public_path('liceo.png') }}" class="logo"></td>
        <td class="center bold" colspan="2" style="font-size:16px;">INSTITUCION EDUCATIVA "LICEO DEL SABER"</td>
        <td rowspan="4" class="center">
            @if(isset($estudiante->foto))
                <img src="{{ public_path('fotos/'.$estudiante->foto) }}" class="foto">
            @else
                <img src="{{ public_path('fotos/default.png') }}" class="foto">
            @endif
        </td>
    </tr>
    <tr>
        <td class="center" colspan="2" style="font-size:12px;">
            Aprobado según resolución No. 01199 del 03 de Abril de 2018<br>
            Preescolar, Básica Primaria, Secundaria y Media Académica
        </td>
    </tr>
    <tr>
        <td class="center" colspan="2" style="font-size:11px;">
            Transversal 6 diagonal 3 esquina No. 7 - 05 B/ Los Lagos III etapa Zarzal - Valle del Cauca<br>
            Tel. 6022208019 – 3168207306 – E-mail: ieliceodelsaber@hotmail.com
        </td>
    </tr>
</table>

<!-- Datos del estudiante -->
<table class="student-table" style="margin-top:10px;">
    <tr>
        <th>APELLIDOS Y NOMBRES DEL ESTUDIANTE</th>
        <th>NIVEL</th>
        <th>GRADO</th>
        <th>PERIODO</th>
        <th>AÑO</th>
    </tr>
    <tr>
        <td class="bold">{{ strtoupper(($estudiante->apellido ?? '') . ' ' . ($estudiante->nombre ?? '')) }}</td>
        <td>{{ strtoupper($estudiante->grado->tipo ?? '') }}</td>
        <td>{{ $estudiante->grado->nombre ?? '' }}</td>
        <td>{{ $periodo->numero_periodo ?? '' }}</td>
    <td>{{ $periodo->anio_escolar ?? now()->year }}</td>
    </tr>
    <tr>
        <th>DIRECTORA DE GRUPO</th>
        <th colspan="2">INASISTENCIA</th>
        <th colspan="2"></th>
    </tr>
    <tr>
        <td>{{ $estudiante->grado->directorGrupo->name ?? 'No asignado' }}</td>
        <td colspan="2">{{ $estudiante->inasistencias ?? '  ' }}</td>
        <td colspan="2"></td>
    </tr>
</table>

<!-- Título del preinforme -->
<div class="section-title">BOLETÍN ACADÉMICO Y DISCIPLINARIO</div>
<div class="subtitle">DEL PRIMER CORTE DEL PERÍODO No. {{ $periodo->numero_periodo ?? '' }}</div>
<div class="subtitle">Comprendido entre: el {{ $periodo->fecha_inicio->format('d/m/Y') }} y el {{ $periodo->fecha_fin->format('d/m/Y') }}</div>

<!-- Materias y logros del segundo corte -->
<table class="area-table" style="margin-top:10px;">
    <tr>
        <th>ASIGNATURA</th>
        <th>Escala Valoración</th>
        <th>Nivel de Desempeño</th>
        <th>Docente</th>
    </tr>
    @foreach($desempenosPorMateria as $materia => $desempenos)
        @php
            // Obtener el desempeño más reciente de la materia
            $desempenoMasReciente = $desempenos->sortByDesc(function($desempeno) {
                return $desempeno->periodo->numero_periodo . '_' . $desempeno->periodo->corte;
            })->first();
        @endphp
        @if($desempenoMasReciente)
        <tr class="asignatura-row">
            <td>{{ $materia }}</td>
            <td class="center">{{ $desempenoMasReciente->nivel_desempeno ?? 'N/A' }}</td>
            <td class="center">{{ 
                $desempenoMasReciente->nivel_desempeno == 'E' ? 'Excelente' :
                ($desempenoMasReciente->nivel_desempeno == 'S' ? 'Sobresaliente' :
                ($desempenoMasReciente->nivel_desempeno == 'A' ? 'Aceptable' :
                ($desempenoMasReciente->nivel_desempeno == 'I' ? 'Insuficiente' : 'N/A')))
            }}</td>
            <td>{{ $desempenoMasReciente->materia->docente->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="4">
                <ul class="logros-list">
                    @foreach($desempenoMasReciente->estudianteLogros as $estudianteLogro)
                        <li>
                            @php
                                $titulo = $estudianteLogro->logro->titulo;
                                $texto = $titulo ? $titulo . ' - ' . ($estudianteLogro->logro->desempeno ?? '') : ($estudianteLogro->logro->desempeno ?? '');
                            @endphp
                            {{ trim($texto, ' -') }}
                        </li>
                    @endforeach
                </ul>
            </td>
        </tr>
        @endif
    @endforeach
</table>

<!-- Título del boletín -->
<div class="section-title">CONSOLIDADO DE VALORACIONES DEL PROCESO FORMATIVO INTEGRAL</div>
<div class="subtitle">Que corresponde a la evaluación por procesos y no por promedios</div>
<div class="subtitle">ESCALA CONCEPTUAL: <b>E: Excelente (Desempeño Superior) = 5</b> &nbsp;&nbsp; <b>S: Sobresaliente (Desempeño Alto) = 4</b> &nbsp;&nbsp; <b>A: Aceptable (Desempeño Básico) = 3</b> &nbsp;&nbsp; <b>I: Insuficiente (Desempeño Bajo) = 2 - 1</b></div>

<!-- Tabla consolidado de valores -->
<table class="area-table" style="margin-top:20px; font-size:13px;">
    <tr>
        <th rowspan="2" style="text-align:center; vertical-align:middle;">AREA</th>
        <th rowspan="2" style="text-align:center; vertical-align:middle;">Asignatura</th>
        <th rowspan="2" style="text-align:center; vertical-align:middle;">IH</th>
        @if($esUltimoPeriodo ?? false)
            <th colspan="2" style="text-align:center;">1 Periodo</th>
            <th colspan="2" style="text-align:center;">2 Periodo</th>
        @else
            @if($periodo->numero_periodo == 1)
                <th colspan="2" style="text-align:center;">1 Periodo</th>
            @else
                <th colspan="2" style="text-align:center;">1 Periodo</th>
                <th colspan="2" style="text-align:center;">2 Periodo</th>
            @endif
        @endif
    </tr>
    <tr>
        @if($esUltimoPeriodo ?? false)
            <th style="text-align:center;">1er Corte</th>
            <th style="text-align:center;">2do Corte</th>
            <th style="text-align:center;">1er Corte</th>
            <th style="text-align:center;">2do Corte</th>
        @else
            @if($periodo->numero_periodo == 1)
                <th style="text-align:center;">1er Corte</th>
                <th style="text-align:center;">2do Corte</th>
            @else
                <th style="text-align:center;">1er Corte</th>
                <th style="text-align:center;">2do Corte</th>
                <th style="text-align:center;">1er Corte</th>
                <th style="text-align:center;">2do Corte</th>
            @endif
        @endif
    </tr>
    @foreach($desempenosPorMateria as $materia => $desempenos)
        @if($desempenos->isNotEmpty())
        <tr>
            <td style="text-align:center;">{{ $desempenos->first()->materia->area ?? '' }}</td>
            <td style="text-align:center;">{{ $materia }}</td>
            <td style="text-align:center;"></td>
            @php
                $nivelesDesempeno = [
                    '1_Primer Corte' => '',
                    '1_Segundo Corte' => '',
                    '2_Primer Corte' => '',
                    '2_Segundo Corte' => ''
                ];
                foreach($desempenos as $desempeno) {
                    $anio = $desempeno->periodo->anio_escolar ?? null;
                    $periodoN = $desempeno->periodo->numero_periodo ?? null;
                    $corte = $desempeno->periodo->corte ?? null;
                    if($anio == ($periodo->anio_escolar ?? null)) {
                        if($periodoN == 1 && $corte == 'Primer Corte') $nivelesDesempeno['1_Primer Corte'] = $desempeno->nivel_desempeno;
                        if($periodoN == 1 && $corte == 'Segundo Corte') $nivelesDesempeno['1_Segundo Corte'] = $desempeno->nivel_desempeno;
                        if($periodoN == 2 && $corte == 'Primer Corte') $nivelesDesempeno['2_Primer Corte'] = $desempeno->nivel_desempeno;
                        if($periodoN == 2 && $corte == 'Segundo Corte') $nivelesDesempeno['2_Segundo Corte'] = $desempeno->nivel_desempeno;
                    }
                }
            @endphp
            @if($esUltimoPeriodo ?? false)
                <td style="text-align:center;">{{ $nivelesDesempeno['1_Primer Corte'] }}</td>
                <td style="text-align:center;">{{ $nivelesDesempeno['1_Segundo Corte'] }}</td>
                <td style="text-align:center;">{{ $nivelesDesempeno['2_Primer Corte'] }}</td>
                <td style="text-align:center;">{{ $nivelesDesempeno['2_Segundo Corte'] }}</td>
            @else
                @if($periodo->numero_periodo == 1)
                    <td style="text-align:center;">{{ $nivelesDesempeno['1_Primer Corte'] }}</td>
                    <td style="text-align:center;">{{ $nivelesDesempeno['1_Segundo Corte'] }}</td>
                @else
                    <td style="text-align:center;">{{ $nivelesDesempeno['1_Primer Corte'] }}</td>
                    <td style="text-align:center;">{{ $nivelesDesempeno['1_Segundo Corte'] }}</td>
                    <td style="text-align:center;">{{ $nivelesDesempeno['2_Primer Corte'] }}</td>
                    <td style="text-align:center;">{{ $nivelesDesempeno['2_Segundo Corte'] }}</td>
                @endif
            @endif
        </tr>
        @endif
    @endforeach
</table>

<!-- Tabla cuadro de valores -->
<table class="area-table" style="margin-top:20px;">
    <tr>
        <th colspan="3">CUADRO DE VALORES</th>
        <th colspan="3"></th>
    </tr>
    <tr>
        <td><b>RESPETO:</b> Escucha con atención a otros y respeta sus opiniones</td>
        <td></td>
        <td><b>RESOLUCIONES DE CONFLICTOS:</b> Buscas soluciones ante situaciones difíciles que se le presentan</td>
        <td></td>
    </tr>
    <tr>
        <td><b>AMOR:</b> Demuestra afecto hacia las personas de su entorno</td>
        <td></td>
        <td><b>CUMPLIMIENTO DE LA NORMA:</b> Acata las normas y acepta los llamados de atención</td>
        <td></td>
    </tr>
    <tr>
        <td><b>TOLERANCIA:</b> Acepta a los demás tal y como son</td>
        <td></td>
        <td><b>CAPACIDAD DE DIALOGO:</b> Manifiesta sus emociones y sentimientos a través del diálogo</td>
        <td></td>
    </tr>
    <tr>
        <td><b>HONESTIDAD:</b> Actúa y habla siempre con la verdad</td>
        <td></td>
        <td><b>GRATITUD:</b> Usa palabras de cortesía y valora lo que otros hacen por su bien</td>
        <td></td>
    </tr>
    <tr>
        <td><b>AUTOESTIMA:</b> Se acepta y valora tal como es</td>
        <td></td>
        <td><b>RESPONSABILIDAD:</b> Es puntual y cumple oportunamente con sus compromisos</td>
        <td></td>
    </tr>
    <tr>
        <td><b>SOLIDARIDAD:</b> Es sensible y compasivo a las necesidades de los demás</td>
        <td></td>
        <td><b>PERSEVERANCIA:</b> Es constante en todo lo que realiza a pesar de los errores</td>
        <td></td>
    </tr>
</table>

<!-- Observaciones disciplina -->
@if(isset($estudiante->observaciones_disciplina))
    <div class="area-title">ÁREA: DISCIPLINA Y CONVIVENCIA ESCOLAR</div>
    <div class="observaciones">
        <b>Observaciones y/o Recomendaciones:</b><br>
        {{ $estudiante->observaciones_disciplina }}
    </div>
@endif

<!-- Firmas -->
<table class="sign-table" style="margin-top:30px;">
    <tr>
        <td>
            <div class="firma-line"></div>
            DIRECTORA DE GRUPO
        </td>
        <td>
            <div class="firma-line"></div>
            RECTORA
        </td>
    </tr>
</table>

</body>
</html> 