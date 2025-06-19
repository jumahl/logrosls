<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preinforme Académico</title>
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
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
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
        .subject-section {
            margin-bottom: 30px;
            border: 1px solid #bdc3c7;
            border-radius: 8px;
            overflow: hidden;
        }
        .subject-header {
            background-color: #3498db;
            color: white;
            padding: 15px;
            font-weight: bold;
            font-size: 16px;
        }
        .achievements-table {
            width: 100%;
            border-collapse: collapse;
        }
        .achievements-table th,
        .achievements-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        .achievements-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .performance-level {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
            min-width: 80px;
        }
        .superior { background-color: #d4edda; color: #155724; }
        .alto { background-color: #d1ecf1; color: #0c5460; }
        .basico { background-color: #fff3cd; color: #856404; }
        .bajo { background-color: #f8d7da; color: #721c24; }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #ecf0f1;
            padding-top: 20px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PREINFORME ACADÉMICO</h1>
            <h2>{{ $periodo->periodo_completo }}</h2>
        </div>

        <div class="warning">
            <strong>IMPORTANTE:</strong> Este es un preinforme del primer corte. Los logros mostrados corresponden únicamente a la primera mitad del período académico.
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

        @foreach($logros as $materia => $logrosMateria)
        <div class="subject-section">
            <div class="subject-header">
                {{ $materia }}
                @if($logrosMateria->first()->logro->materia->docente)
                <span style="font-size: 14px; font-weight: normal;"> - Docente: {{ $logrosMateria->first()->logro->materia->docente->name }}</span>
                @endif
            </div>
            <table class="achievements-table">
                <thead>
                    <tr>
                        <th>Título del Logro</th>
                        <th>Competencia</th>
                        <th>Tema</th>
                        <th>Indicador de Desempeño</th>
                        <th>Nivel de Desempeño</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logrosMateria as $logro)
                    <tr>
                        <td><strong>{{ $logro->logro->titulo }}</strong></td>
                        <td>{{ $logro->logro->competencia }}</td>
                        <td>{{ $logro->logro->tema ?: 'No especificado' }}</td>
                        <td>{{ $logro->logro->indicador_desempeno }}</td>
                        <td>
                            <span class="performance-level {{ strtolower($logro->nivel_desempeno) }}">
                                {{ $logro->nivel_desempeno }}
                            </span>
                        </td>
                        <td>{{ $logro->observaciones ?: 'Sin observaciones' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach

        <div class="footer">
            <p><strong>Nota:</strong> Este preinforme muestra el progreso del estudiante hasta la mitad del período. 
            El boletín final incluirá todos los logros del período completo.</p>
            <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html> 