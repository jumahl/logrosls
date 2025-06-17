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
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .materia-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .materia-header {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
        }
        .nivel-desempeno {
            font-weight: bold;
        }
        .nivel-superior { color: #28a745; }
        .nivel-alto { color: #17a2b8; }
        .nivel-basico { color: #ffc107; }
        .nivel-bajo { color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Boletín Académico</h1>
        <h2>Periodo: {{ $periodo->nombre }}</h2>
    </div>

    <div class="student-info">
        <h3>Información del Estudiante</h3>
        <p><strong>Nombre:</strong> {{ $estudiante->nombre }} {{ $estudiante->apellido }}</p>
        <p><strong>Documento:</strong> {{ $estudiante->documento }}</p>
        <p><strong>Grado:</strong> {{ $estudiante->grado->nombre }}</p>
    </div>

    @foreach($notas as $materia => $notasMateria)
        <div class="materia-section">
            <div class="materia-header">
                <h3>{{ $materia }}</h3>
                <p><strong>Docente:</strong> {{ $notasMateria->first()->logro->materia->docente->name }}</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Logro</th>
                        <th>Nivel de Desempeño</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notasMateria as $nota)
                        <tr>
                            <td>{{ $nota->logro->competencia }}</td>
                            <td class="nivel-desempeno nivel-{{ strtolower($nota->nivel_desempeno) }}">
                                {{ $nota->nivel_desempeno }}
                            </td>
                            <td>{{ $nota->observaciones }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="footer">
        <p>Fecha de generación: {{ now()->format('d/m/Y') }}</p>
        <p>Este documento es generado automáticamente por el sistema.</p>
    </div>
</body>
</html> 