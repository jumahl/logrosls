<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletín Segundo Periodo - {{ $historicoEstudiante->estudiante_nombre }} {{ $historicoEstudiante->estudiante_apellido }}</title>
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
        .promedio {
            font-weight: bold;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .periodo-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('liceo.png') }}" alt="Logo Liceo" style="width: 80px; height: 80px;">
        </div>
        <h1>LICEO ACADÉMICO</h1>
        <h2>BOLETÍN ACADÉMICO HISTÓRICO</h2>
        <h3>{{ $periodo }} - Año {{ $anioEscolar }}</h3>
    </div>

    <div class="periodo-header">
        <h2>{{ $periodo }} - {{ $anioEscolar }}</h2>
    </div>

    <div class="info-estudiante">
        <div class="info-row">
            <span><strong>Estudiante:</strong> {{ $historicoEstudiante->estudiante_nombre }} {{ $historicoEstudiante->estudiante_apellido }}</span>
            <span><strong>Documento:</strong> {{ $historicoEstudiante->estudiante_documento }}</span>
        </div>
        <div class="info-row">
            <span><strong>Grado:</strong> {{ $historicoEstudiante->grado_nombre }} {{ $historicoEstudiante->grado_grupo }}</span>
            <span><strong>Año Escolar:</strong> {{ $anioEscolar }}</span>
        </div>
        <div class="info-row">
            <span><strong>Resultado Final:</strong> {{ ucfirst($historicoEstudiante->resultado_final) }}</span>
        </div>
    </div>

    <table class="materias">
        <thead>
            <tr>
                <th>Materia</th>
                <th>Desempeño</th>
                <th>Observaciones</th>
                <th>Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($desempenosPorMateria as $materia => $desempenos)
                @foreach($desempenos as $index => $desempeno)
                    <tr>
                        @if($index == 0)
                            <td rowspan="{{ $desempenos->count() }}">{{ $materia }}</td>
                        @endif
                        <td class="desempeno-{{ $desempeno->nivel_desempeno }}">
                            @switch($desempeno->nivel_desempeno)
                                @case('E')
                                    Excelente
                                    @break
                                @case('S')
                                    Sobresaliente
                                    @break
                                @case('A')
                                    Aceptable
                                    @break
                                @case('I')
                                    Insuficiente
                                    @break
                                @default
                                    {{ $desempeno->nivel_desempeno }}
                            @endswitch
                        </td>
                        <td>{{ $desempeno->observaciones_finales ?: 'Sin observaciones' }}</td>
                        @if($index == 0)
                            <td rowspan="{{ $desempenos->count() }}" class="promedio">
                                {{ $promediosPorMateria[$materia] ?? 'N/A' }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    @if($logrosHistoricos->isNotEmpty())
        <div style="margin-top: 30px;">
            <h3>Logros Alcanzados</h3>
            <ul>
                @foreach($logrosHistoricos as $logro)
                    <li>{{ $logro->logro_descripcion ?: $logro->logro_titulo }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Sistema de Gestión Académica - Liceo Académico</p>
        <p><em>Este es un documento histórico correspondiente al {{ $periodo }} del año {{ $anioEscolar }}</em></p>
    </div>
</body>
</html>
