<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Boletín Académico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            color: #7f8c8d;
            margin: 10px 0 0 0;
            font-size: 18px;
        }
        .student-info {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .student-info h3 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item {
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            color: #34495e;
            min-width: 120px;
        }
        .info-value {
            color: #2c3e50;
        }
        .materia-section {
            margin-bottom: 30px;
            border: 1px solid #bdc3c7;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .materia-header {
            background-color: #3498db;
            color: white;
            padding: 15px;
            font-weight: bold;
            font-size: 16px;
        }
        .materia-summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            text-align: center;
        }
        .summary-item {
            padding: 10px;
            border-radius: 5px;
        }
        .summary-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .promedio-final {
            background-color: #28a745;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .nivel-desempeno {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
            min-width: 80px;
        }
        .e { background-color: #d4edda; color: #155724; } /* Excelente */
        .s { background-color: #d1ecf1; color: #0c5460; } /* Sobresaliente */
        .a { background-color: #fff3cd; color: #856404; } /* Aceptable */
        .i { background-color: #f8d7da; color: #721c24; } /* Insuficiente */
        .corte-indicator {
            font-size: 12px;
            color: #6c757d;
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #ecf0f1;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BOLETÍN ACADÉMICO</h1>
            <h2>{{ $periodo->periodo_completo }}</h2>
        </div>

        <div class="student-info">
            <h3>Información del Estudiante</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $estudiante->nombre }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Documento:</span>
                    <span class="info-value">{{ $estudiante->documento }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Grado:</span>
                    <span class="info-value">{{ $estudiante->grado->nombre ?? 'No asignado' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value">{{ now()->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        @foreach($logrosPorMateria as $materia => $logros)
        <div class="materia-section">
            <div class="materia-header">
                {{ $materia }}
                @if($logros->isNotEmpty() && $logros->first()->logro->materia->docente)
                <span style="font-size: 14px; font-weight: normal;"> - Docente: {{ $logros->first()->logro->materia->docente->name }}</span>
                @endif
            </div>
            
            <div class="materia-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Logros Primer Corte</div>
                        <div class="summary-value">{{ $logros->where('periodo_id', $periodoAnterior->id ?? 0)->count() }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Logros Segundo Corte</div>
                        <div class="summary-value">{{ $logros->where('periodo_id', $periodo->id)->count() }}</div>
                    </div>
                    <div class="summary-item promedio-final">
                        <div class="summary-label">Promedio Final</div>
                        <div class="summary-value">{{ number_format($promediosPorMateria[$materia], 1) }}</div>
                    </div>
                </div>
            </div>

            @if($logros->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Título del Logro</th>
                        <th>Competencia</th>
                        <th>Tema</th>
                        <th>Indicador de Desempeño</th>
                        <th>Nivel de Desempeño</th>
                        <th>Corte</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logros as $logro)
                        <tr>
                            <td><strong>{{ $logro->logro->titulo }}</strong></td>
                            <td>{{ $logro->logro->competencia }}</td>
                            <td>{{ $logro->logro->tema ?: 'No especificado' }}</td>
                            <td>{{ $logro->logro->indicador_desempeno }}</td>
                            <td>
                                <span class="nivel-desempeno {{ strtolower($logro->nivel_desempeno) }}">
                                    {{ $logro->nivel_desempeno_completo }}
                                </span>
                            </td>
                            <td>
                                <span class="corte-indicator">
                                    {{ $logro->periodo->corte }}
                                </span>
                            </td>
                            <td>{{ $logro->observaciones ?: 'Sin observaciones' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="padding: 20px; text-align: center; color: #6c757d; font-style: italic;">
                <p>No hay logros registrados para esta materia en el período seleccionado.</p>
            </div>
            @endif
        </div>
        @endforeach

        <div class="footer">
            <p><strong>Nota:</strong> Este boletín incluye todos los logros del período completo, 
            combinando los resultados del primer y segundo corte.</p>
            <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html> 