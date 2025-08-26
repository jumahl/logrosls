# INFORME DETALLADO DE LA BASE DE DATOS - SISTEMA DE LOGROS ACADÉMICOS

## 1. RESUMEN EJECUTIVO

El sistema de gestión de logros académicos es una aplicación Laravel diseñada para administrar el rendimiento estudiantil en instituciones educativas. La base de datos está estructurada para manejar estudiantes, grados, materias, logros académicos y evaluaciones de desempeño a través de múltiples períodos académicos.

## 2. ARQUITECTURA DE LA BASE DE DATOS

### 2.1 Entidades Principales

#### **USERS (Usuarios del Sistema)**

-   **Propósito**: Gestiona usuarios del sistema (administradores, profesores, directores de grupo)
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `name`: Nombre completo del usuario
    -   `email`: Correo electrónico único
    -   `password`: Contraseña encriptada
    -   `director_grado_id`: FK a grados (para directores de grupo)
-   **Relaciones**: Un usuario puede ser docente de múltiples materias y director de un grado

#### **GRADOS (Niveles Académicos)**

-   **Propósito**: Define los niveles educativos de la institución
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `nombre`: Nombre del grado (ej: "Sexto A", "Preescolar B")
    -   `tipo`: Enum('preescolar', 'primaria', 'secundaria', 'media_academica')
    -   `activo`: Boolean para grados disponibles
-   **Justificación del diseño**: El campo `tipo` permite categorizar grados por nivel educativo, facilitando reportes y filtros específicos por categoría

#### **ESTUDIANTES (Estudiantes del Sistema)**

-   **Propósito**: Almacena información de los estudiantes matriculados
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `nombre`, `apellido`: Información personal
    -   `documento`: Número de identificación único
    -   `fecha_nacimiento`: Para cálculos de edad
    -   `grado_id`: FK a grados (relación 1:N)
    -   `activo`: Estado del estudiante
-   **Justificación**: La relación con grados es directa (1:N) ya que un estudiante pertenece a un solo grado por período

#### **MATERIAS (Asignaturas Académicas)**

-   **Propósito**: Define las materias o asignaturas del currículo
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `nombre`: Nombre de la materia
    -   `codigo`: Código único de la materia
    -   `docente_id`: FK a users (profesor asignado)
    -   `descripcion`: Descripción opcional
    -   `activa`: Estado de la materia
-   **Justificación**: Una materia puede tener un docente principal, pero puede ser enseñada en múltiples grados

#### **GRADO_MATERIA (Tabla Pivot)**

-   **Propósito**: Relaciona grados con materias (N:N)
-   **Campos**:
    -   `grado_id`: FK a grados
    -   `materia_id`: FK a materias
    -   Índice único para evitar duplicados
-   **Justificación**: Una materia puede dictarse en varios grados, y un grado tiene múltiples materias

#### **PERIODOS (Períodos Académicos)**

-   **Propósito**: Define los períodos de evaluación académica
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `corte`: Enum('Primer Corte', 'Segundo Corte')
    -   `anio_escolar`: Año académico
    -   `numero_periodo`: 1 o 2 para el año escolar
    -   `fecha_inicio`, `fecha_fin`: Duración del período
    -   `activo`: Solo un período puede estar activo
-   **Justificación**: Permite manejar múltiples períodos académicos con flexibilidad para diferentes sistemas de evaluación

#### **LOGROS (Logros Académicos)**

-   **Propósito**: Define los logros específicos de cada materia
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `codigo`: Código único del logro
    -   `materia_id`: FK a materias
    -   `titulo`: Título del logro
    -   `desempeno`: Descripción detallada del desempeño esperado
    -   `activo`: Estado del logro
    -   `orden`: Para ordenamiento en reportes
-   **Justificación**: Los logros están asociados a materias específicas, permitiendo evaluaciones granulares del aprendizaje

#### **LOGRO_PERIODO (Tabla Pivot)**

-   **Propósito**: Relaciona logros con períodos específicos (N:N)
-   **Campos**:
    -   `logro_id`: FK a logros
    -   `periodo_id`: FK a periodos
-   **Justificación**: Un logro puede evaluarse en múltiples períodos, y un período puede tener múltiples logros activos

#### **DESEMPENOS_MATERIA (Evaluaciones Consolidadas)**

-   **Propósito**: Almacena el desempeño general de un estudiante en una materia por período
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `estudiante_id`: FK a estudiantes
    -   `materia_id`: FK a materias
    -   `periodo_id`: FK a periodos
    -   `nivel_desempeno`: Enum('E', 'S', 'A', 'I') - Excelente, Sobresaliente, Aceptable, Insuficiente
    -   `observaciones_finales`: Comentarios del docente
    -   `fecha_asignacion`: Fecha de evaluación
    -   `estado`: Enum('borrador', 'publicado', 'revisado')
    -   `locked_at`, `locked_by`: Control de bloqueo de calificaciones
-   **Justificación**: Tabla centralizada que consolida la evaluación de un estudiante en una materia, con sistema de versionado y control de acceso

#### **ESTUDIANTE_LOGROS (Logros Específicos por Estudiante)**

-   **Propósito**: Registra logros específicos alcanzados por cada estudiante
-   **Campos principales**:
    -   `id`: Clave primaria
    -   `logro_id`: FK a logros
    -   `desempeno_materia_id`: FK a desempenos_materia
    -   `alcanzado`: Boolean si se alcanzó el logro
-   **Justificación**: Permite evaluación granular de logros específicos dentro del desempeño general de una materia

### 2.2 Tablas del Sistema de Permisos (Spatie Permission)

#### **PERMISSIONS, ROLES, MODEL_HAS_PERMISSIONS, MODEL_HAS_ROLES, ROLE_HAS_PERMISSIONS**

-   **Propósito**: Implementa el sistema de roles y permisos
-   **Justificación**: Proporciona control de acceso granular con roles como admin, profesor, director_grupo

## 3. RELACIONES Y CARDINALIDADES

### 3.1 Relaciones Principales

1. **User → Materia**: 1:N (Un usuario puede ser docente de múltiples materias)
2. **User → Grado**: 1:1 (Un usuario puede ser director de un grado)
3. **Grado → Estudiante**: 1:N (Un grado tiene múltiples estudiantes)
4. **Grado ↔ Materia**: N:N (Grados tienen múltiples materias, materias se dictan en múltiples grados)
5. **Materia → Logro**: 1:N (Una materia tiene múltiples logros)
6. **Logro ↔ Periodo**: N:N (Logros pueden usarse en múltiples períodos)
7. **Estudiante → DesempenoMateria**: 1:N (Un estudiante tiene múltiples desempeños)
8. **DesempenoMateria → EstudianteLogro**: 1:N (Un desempeño puede tener múltiples logros específicos)

### 3.2 Integridad Referencial

-   **Eliminación en cascada**: Grados → Estudiantes, Materias → Logros, DesempenoMateria → EstudianteLogros
-   **SET NULL**: Materias.docente_id cuando se elimina un usuario
-   **Índices únicos**: Previenen duplicados en evaluaciones y relaciones

## 4. ÍNDICES Y OPTIMIZACIONES

### 4.1 Índices Implementados

1. **desempenos_materia**:

    - Único: [`estudiante_id`, `materia_id`, `periodo_id`]
    - Compuestos: [`estudiante_id`, `periodo_id`], [`materia_id`, `periodo_id`]
    - Simples: [`estado`], [`fecha_asignacion`], [`nivel_desempeno`]

2. **grado_materia**:

    - Único: [`grado_id`, `materia_id`]
    - Simples en ambas FK

3. **periodos**:
    - Compuesto: [`anio_escolar`, `numero_periodo`, `corte`]
    - Simple: [`activo`]

### 4.2 Justificación de Índices

-   **Índices únicos**: Previenen inconsistencias de datos
-   **Índices compuestos**: Optimizan consultas frecuentes de reportes
-   **Índices simples**: Mejoran filtros comunes por estado y fechas

## 5. MODELO DE DATOS ACADÉMICOS

### 5.1 Sistema de Calificaciones

**Niveles de Desempeño**:

-   **E (Excelente)**: 5.0 - Supera los logros esperados
-   **S (Sobresaliente)**: 4.0 - Alcanza completamente los logros
-   **A (Aceptable)**: 3.0 - Alcanza los logros mínimos
-   **I (Insuficiente)**: 2.0 - No alcanza los logros esperados

### 5.2 Estados de Evaluación

**Estados del Desempeño**:

-   **borrador**: Evaluación en progreso, editable
-   **publicado**: Evaluación finalizada y visible para estudiantes
-   **revisado**: Evaluación revisada por coordinación académica

### 5.3 Sistema de Bloqueo

-   **locked_at**: Timestamp de bloqueo
-   **locked_by**: Usuario que realizó el bloqueo
-   **Propósito**: Prevenir modificaciones no autorizadas una vez publicadas las calificaciones

## 6. SEGURIDAD Y CONTROL DE ACCESO

### 6.1 Roles del Sistema

1. **admin**: Acceso completo al sistema
2. **profesor**: Gestión de materias asignadas y evaluaciones
3. **director_grupo**: Gestión de estudiantes del grado asignado

### 6.2 Políticas de Seguridad

-   Contraseñas encriptadas con hash
-   Control de acceso basado en roles y permisos
-   Auditoría de cambios con timestamps
-   Sistema de sesiones seguras

## 7. CONSIDERACIONES DE RENDIMIENTO

### 7.1 Optimizaciones Implementadas

1. **Índices estratégicos** en consultas frecuentes
2. **Eager loading** en relaciones complejas
3. **Scopes** para consultas reutilizables
4. **Caché** para consultas costosas de reportes

### 7.2 Consultas Críticas Optimizadas

-   Reportes de grado por período
-   Estadísticas de desempeño por materia
-   Listados de estudiantes con filtros múltiples
-   Generación de boletines académicos

## 8. ESCALABILIDAD Y FUTURAS MEJORAS

### 8.1 Capacidad Actual

-   Soporte para múltiples años académicos
-   Flexibilidad en estructura de grados
-   Sistema de períodos configurable
-   Evaluaciones granulares por logros

### 8.2 Posibles Mejoras

1. **Auditoría completa**: Tracking de cambios en evaluaciones
2. **Notificaciones**: Sistema de alertas para padres/estudiantes
3. **Analytics**: Dashboards de tendencias académicas
4. **API REST**: Integración con otros sistemas educativos
5. **Backup automático**: Respaldo de datos críticos

## 9. DIAGRAMA DE RELACIONES

El diagrama PlantUML detallado se encuentra en el archivo adjunto `database_diagram.puml`.

## 10. CONCLUSIONES

La base de datos está diseñada con los siguientes principios:

1. **Normalización**: Eliminación de redundancia de datos
2. **Flexibilidad**: Adaptable a diferentes sistemas educativos
3. **Integridad**: Constraints y validaciones robustas
4. **Rendimiento**: Índices optimizados para consultas frecuentes
5. **Seguridad**: Control de acceso granular
6. **Escalabilidad**: Estructura preparada para crecimiento

El diseño permite una gestión eficiente del proceso académico, desde la matrícula de estudiantes hasta la generación de reportes detallados de desempeño, manteniendo la trazabilidad y consistencia de los datos en todo momento.
