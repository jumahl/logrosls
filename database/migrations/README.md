# Migraciones Consolidadas - Sistema de Logros Académicos

## Descripción

Este directorio contiene las migraciones consolidadas del sistema de gestión de logros académicos. Todas las migraciones originales han sido combinadas en migraciones únicas para cada tabla, eliminando la necesidad de múltiples migraciones de modificación.

## Estructura de Migraciones

### 1. Migraciones del Sistema Laravel

-   `0001_01_01_000000_create_users_table.php` - Tabla de usuarios del sistema
-   `0001_01_01_000001_create_cache_table.php` - Tablas de caché
-   `0001_01_01_000002_create_jobs_table.php` - Tablas de trabajos en cola
-   `2025_06_17_042659_create_permission_tables.php` - Sistema de permisos y roles

### 2. Migraciones del Sistema Académico

-   `2025_06_17_044132_create_grados_table.php` - Grados escolares
-   `2025_06_17_044143_create_periodos_table.php` - Períodos académicos
-   `2025_06_17_044146_create_estudiantes_table.php` - Estudiantes
-   `2025_06_17_044148_create_materias_table.php` - Materias académicas
-   `2025_06_17_044151_create_logros_table.php` - Logros académicos
-   `2025_06_17_044153_create_estudiante_logros_table.php` - Evaluaciones de estudiantes
-   `2025_06_17_143500_create_logro_periodo_table.php` - Relación logros-períodos

## Campos Consolidados por Tabla

### GRADOS

-   `id` - Identificador único
-   `nombre` - Nombre del grado
-   `tipo` - Tipo de grado (preescolar, primaria, secundaria, media_academica)
-   `activo` - Estado activo del grado
-   `media_academica` - Promedio académico del grado
-   `timestamps` - Fechas de creación y actualización

### PERIODOS

-   `id` - Identificador único
-   `corte` - Tipo de corte (Primer Corte, Segundo Corte)
-   `año_escolar` - Año escolar
-   `numero_periodo` - Número del período (1 o 2)
-   `fecha_inicio` - Fecha de inicio del período
-   `fecha_fin` - Fecha de fin del período
-   `activo` - Estado activo del período
-   `timestamps` - Fechas de creación y actualización
-   **Índices**: `[año_escolar, numero_periodo, corte]`, `[activo]`

### ESTUDIANTES

-   `id` - Identificador único
-   `nombre` - Nombre del estudiante
-   `apellido` - Apellido del estudiante
-   `documento` - Número de documento (único)
-   `fecha_nacimiento` - Fecha de nacimiento
-   `direccion` - Dirección del estudiante
-   `telefono` - Teléfono del estudiante
-   `email` - Correo electrónico del estudiante
-   `grado_id` - Referencia al grado
-   `activo` - Estado activo del estudiante
-   `timestamps` - Fechas de creación y actualización

### MATERIAS

-   `id` - Identificador único
-   `nombre` - Nombre de la materia
-   `codigo` - Código único de la materia
-   `grado_id` - Referencia al grado
-   `docente_id` - Referencia al docente (nullable)
-   `activa` - Estado activo de la materia
-   `descripcion` - Descripción de la materia
-   `timestamps` - Fechas de creación y actualización

### LOGROS

-   `id` - Identificador único
-   `codigo` - Código único del logro
-   `materia_id` - Referencia a la materia
-   `titulo` - Título del logro
-   `competencia` - Competencia que evalúa
-   `tema` - Tema específico
-   `indicador_desempeno` - Indicador de desempeño
-   `descripcion` - Descripción detallada
-   `nivel_dificultad` - Nivel de dificultad (bajo, medio, alto)
-   `tipo` - Tipo de logro (conocimiento, habilidad, actitud, valor)
-   `activo` - Estado activo del logro
-   `orden` - Orden de presentación
-   `dimension` - Dimensión del aprendizaje
-   `timestamps` - Fechas de creación y actualización

### ESTUDIANTE_LOGROS

-   `id` - Identificador único
-   `estudiante_id` - Referencia al estudiante
-   `logro_id` - Referencia al logro
-   `periodo_id` - Referencia al período
-   `nivel_desempeno` - Nivel de desempeño (E=Excelente, S=Sobresaliente, A=Aceptable, I=Insuficiente)
-   `observaciones` - Observaciones adicionales
-   `fecha_asignacion` - Fecha de asignación
-   `timestamps` - Fechas de creación y actualización
-   **Restricción única**: `[estudiante_id, logro_id, periodo_id]`
-   **Índices**: `[estudiante_id, periodo_id]`, `[logro_id, periodo_id]`

### LOGRO_PERIODO

-   `id` - Identificador único
-   `logro_id` - Referencia al logro
-   `periodo_id` - Referencia al período
-   `timestamps` - Fechas de creación y actualización
-   **Restricción única**: `[logro_id, periodo_id]`

## Cambios Realizados

### Eliminaciones

-   ❌ Campo `nombre` de períodos (redundante con `corte` y `numero_periodo`)
-   ❌ Campo `genero` de estudiantes (no usado en formularios)
-   ❌ Tabla `grado_logro` (innecesaria, logros se asocian a materias)

### Mejoras

-   ✅ Índices de rendimiento agregados
-   ✅ Validaciones de negocio implementadas
-   ✅ Relaciones optimizadas
-   ✅ Campos consolidados en una sola migración por tabla

## Instalación

Para aplicar las migraciones consolidadas:

```bash
php artisan migrate:fresh
```

## Notas Importantes

1. **Backup**: Las migraciones originales se encuentran en `database/migrations_backup/`
2. **Datos**: Al usar `migrate:fresh` se perderán todos los datos existentes
3. **Seeders**: Ejecutar los seeders después de las migraciones si es necesario
4. **Validaciones**: Las validaciones de negocio están implementadas en los modelos

## Relaciones del Sistema

```
USERS (1) ←→ (N) MATERIAS
GRADOS (1) ←→ (N) ESTUDIANTES
GRADOS (1) ←→ (N) MATERIAS
MATERIAS (1) ←→ (N) LOGROS
LOGROS (N) ←→ (N) PERIODOS (via logro_periodo)
ESTUDIANTES (N) ←→ (N) LOGROS (via estudiante_logros)
```

Esta estructura optimizada proporciona una base de datos limpia, eficiente y fácil de mantener.
