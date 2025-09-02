# Sistema de Transición de Año Escolar

## 📋 Funcionalidad Implementada

### 🎯 Objetivo Principal

Implementar un sistema para conservar los datos académicos históricos por año escolar, permitiendo generar boletines de años anteriores y facilitar la transición entre períodos académicos.

### 🏗️ Estructura de Base de Datos

#### Tablas Históricas Creadas:

1. **`anios_escolares`** - Manejo de años académicos

    - `id`, `anio`, `activo`, `finalizado`, `fecha_inicio`, `fecha_fin`, `observaciones`

2. **`historico_estudiantes`** - Estudiantes por año escolar

    - Información completa del estudiante y su grado por año
    - Estado de promoción

3. **`historico_desempenos`** - Desempeños académicos históricos

    - Todas las notas y desempeños por materia, período y año
    - Información del docente y observaciones

4. **`historico_logros`** - Logros académicos históricos
    - Registro de logros alcanzados por estudiante y año

### 🛠️ Modelos Creados

-   `AnioEscolar`
-   `HistoricoEstudiante`
-   `HistoricoDesempeno`
-   `HistoricoLogro`

### 💻 Interfaz de Administración

#### Página de Transición (`/liceo/transicion-anual`)

-   **Acceso**: Solo para usuarios con rol `admin`
-   **Funciones**:
    -   Ver estadísticas actuales del sistema
    -   Configurar transición entre años
    -   Ejecutar simulación (sin cambios reales)
    -   Ejecutar transición real
    -   Instrucciones claras del proceso

#### Recursos de Consulta Histórica

1. **Histórico de Estudiantes** (`/liceo/historico-estudiantes`)
    - Ver estudiantes archivados por año
    - Filtros por año escolar, grado, estado de promoción
2. **Histórico de Desempeños** (`/liceo/historico-desempenos`)
    - Ver todas las notas históricas
    - Filtros por año, materia, nivel de desempeño
    - Visualización con badges de colores por desempeño

### ⚙️ Comando Artisan

#### `php artisan transicion:anual {anio_finalizar} {anio_nuevo} {--simular}`

**Funcionalidades**:

-   **Validaciones**: Verifica que los años sean consecutivos y válidos
-   **Estadísticas**: Muestra datos actuales antes de proceder
-   **Modo Simulación**: Permite probar sin hacer cambios reales
-   **Confirmación**: Solicita confirmación antes de ejecutar cambios reales

**Proceso de Transición**:

1. **Verificar Años Escolares**: Crea los registros de años si no existen
2. **Archivar Datos**:
    - Guarda todos los estudiantes activos con su grado
    - Archiva todos los desempeños académicos
    - Preserva todos los logros registrados
3. **Promover Estudiantes**: Automáticamente promueve estudiantes al siguiente grado
4. **Finalizar Año**:
    - Marca el año anterior como finalizado
    - Activa el nuevo año
    - Limpia las tablas de datos actuales para empezar limpio

### 🎯 Flujo de Uso

1. **Preparación**:

    - Asegurar que todos los datos del año estén completos
    - Hacer respaldo de la base de datos

2. **Simulación**:

    - Ejecutar desde la interfaz web o comando con `--simular`
    - Revisar los logs y estadísticas

3. **Ejecución Real**:

    - Ejecutar la transición real desde la interfaz
    - Verificar que los datos se archivaron correctamente

4. **Post-Transición**:
    - Revisar estudiantes promovidos
    - Marcar como inactivos a estudiantes que no deben continuar
    - Verificar datos históricos en las secciones correspondientes

### 🔒 Seguridad y Permisos

-   Solo usuarios con rol `admin` pueden acceder a la transición
-   Los datos históricos son de solo lectura (no se pueden editar/eliminar)
-   Confirmaciones múltiples antes de ejecutar cambios irreversibles

### 📊 Beneficios

-   **Conservación de Datos**: Todos los datos académicos se preservan por año
-   **Boletines Históricos**: Posibilidad de generar boletines de años anteriores
-   **Auditoría**: Rastro completo de la información académica
-   **Flexibilidad**: Sistema robusto que se adapta a cambios en la estructura

### 🚀 Próximos Pasos Sugeridos

1. Implementar generación de boletines desde datos históricos
2. Crear reportes estadísticos por año escolar
3. Añadir validaciones adicionales para casos específicos
4. Implementar notificaciones por email del proceso de transición

---

## 📝 Notas Técnicas

-   **Base de datos**: MySQL
-   **Framework**: Laravel con Filament para la interfaz
-   **Autenticación**: Spatie Laravel Permission
-   **Comandos**: Artisan commands para automatización
-   **UI**: Filament Admin Panel con diseño responsive

El sistema está completamente funcional y listo para usar en producción.
