<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletín Histórico - {{ $historicoEstudiante->estudiante_nombre }} {{ $historicoEstudiante->estudiante_apellido }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
        }
        .info-estudiante {
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .materias {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .materias th, .materias td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .materias th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .desempeno-E { background-color: #d4edda; color: #155724; }
        .desempeno-S { background-color: #cce5ff; color: #0066cc; }
        .desempeno-A { background-color: #fff3cd; color: #856404; }
        .desempeno-I { background-color: #f8d7da; color: #721c24; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .historico-badge {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('liceo.png') }}" alt="Logo" style="width: 80px; height: 80px;">
        </div>
        <h1>BOLETÍN ACADÉMICO HISTÓRICO</h1>
        <div class="historico-badge">AÑO ESCOLAR {{ $anioEscolar }}</div>
        <p><strong>Institución Educativa</strong></p>
    </div>

    <div class="info-estudiante">
        <div class="info-row">
            <span><strong>Estudiante:</strong> {{ $historicoEstudiante->estudiante_nombre }} {{ $historicoEstudiante->estudiante_apellido }}</span>
            <span><strong>Documento:</strong> {{ $historicoEstudiante->estudiante_documento }}</span>
        </div>
        <div class="info-row">
            <span><strong>Grado:</strong> {{ $historicoEstudiante->grado_nombre }}</span>
            <span><strong>Año Escolar:</strong> {{ $anioEscolar }}</span>
        </div>
        <div class="info-row">
            <span><strong>Estado:</strong> {{ strtoupper($historicoEstudiante->resultado_final ?? 'PROMOVIDO') }}</span>
            <span><strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y') }}</span>
        </div>
    </div>

    <table class="materias">
        <thead>
            <tr>
                <th style="width: 30%;">Materia</th>
                <th style="width: 20%;">Período</th>
                <th style="width: 15%;">Desempeño</th>
                <th style="width: 10%;">Promedio</th>
                <th style="width: 25%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($desempenosPorMateria as $materia => $desempenos)
                @php
                    $primerDesempeno = $desempenos->first();
                    $promedio = $promediosPorMateria[$materia] ?? 0;
                @endphp
                <tr>
                    <td rowspan="{{ $desempenos->count() }}"><strong>{{ $materia }}</strong></td>
                    <td>{{ $primerDesempeno->periodo_nombre }}</td>
                    <td class="desempeno-{{ $primerDesempeno->nivel_desempeno }}">
                        {{ $primerDesempeno->nivel_desempeno }} - 
                        @switch($primerDesempeno->nivel_desempeno)
                            @case('E') Excelente @break
                            @case('S') Sobresaliente @break
                            @case('A') Aceptable @break
                            @case('I') Insuficiente @break
                            @default Sin calificar
                        @endswitch
                    </td>
                    <td rowspan="{{ $desempenos->count() }}">{{ number_format($promedio, 1) }}</td>
                    <td>{{ $primerDesempeno->observaciones_finales ?? 'Sin observaciones' }}</td>
                </tr>
                
                @foreach($desempenos->skip(1) as $desempeno)
                <tr>
                    <td>{{ $desempeno->periodo_nombre }}</td>
                    <td class="desempeno-{{ $desempeno->nivel_desempeno }}">
                        {{ $desempeno->nivel_desempeno }} - 
                        @switch($desempeno->nivel_desempeno)
                            @case('E') Excelente @break
                            @case('S') Sobresaliente @break
                            @case('A') Aceptable @break
                            @case('I') Insuficiente @break
                            @default Sin calificar
                        @endswitch
                    </td>
                    <td>{{ $desempeno->observaciones_finales ?? 'Sin observaciones' }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    @if($promediosPorMateria->isNotEmpty())
    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
        <h3>Resumen de Promedios</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            @foreach($promediosPorMateria as $materia => $promedio)
                <div>
                    <strong>{{ $materia }}:</strong> {{ number_format($promedio, 1) }}
                    @if($promedio >= 4.5)
                        <span style="color: #155724;">(Excelente)</span>
                    @elseif($promedio >= 3.5)
                        <span style="color: #0066cc;">(Sobresaliente)</span>
                    @elseif($promedio >= 3.0)
                        <span style="color: #856404;">(Aceptable)</span>
                    @else
                        <span style="color: #721c24;">(Insuficiente)</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        <p><strong>DOCUMENTO HISTÓRICO</strong> - Generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Este boletín corresponde a datos archivados del año escolar {{ $anioEscolar }}</p>
        <p>Para consultas, contacte con la administración académica</p>
    </div>
</body>
</html>
