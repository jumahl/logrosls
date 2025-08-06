# LogrosLS - Sistema de Gestión Académica por Logros

Sistema de gestión académica diseñado específicamente para instituciones educativas que evalúan por logros de aprendizaje, desarrollado con Laravel 12 y Filament 3.3.

## 🚀 Instalación y Configuración

### Requisitos

-   PHP 8.2+
-   Composer
-   Node.js 18+
-   MySQL/MariaDB

### Instalación

```bash
git clone https://github.com/jumahl/logrosls.git
cd logrosls
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Configuración de Base de Datos

```bash
# Configurar variables en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logrosls
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Ejecutar migraciones
php artisan migrate
php artisan db:seed
```

### Configuración de Filament Shield

```bash
php artisan shield:install --fresh
php artisan shield:generate --all
```

## 🏗️ Arquitectura del Sistema

### Modelos de Dominio Académico

#### Sistema de Evaluación por Logros

-   **Logro**: Objetivo específico de aprendizaje vinculado a una materia
-   **EstudianteLogro**: Registro de evaluación con niveles E/S/A/I
-   **Periodo**: Cortes académicos (bimestres/trimestres)

#### Relaciones Críticas

```php
// Grado ↔ Materia (Muchos a Muchos)
$grado->materias() // A través de tabla pivot grado_materia

// Logros por Grado (Relación Compleja)
$grado->logros() // HasManyThrough con lógica personalizada en RelationManager
```

### Sistema de Roles y Permisos

#### Roles Implementados

-   **Admin**: Acceso completo al sistema
-   **Profesor**: Gestión de sus materias asignadas
-   **Director de Grupo**: Profesor + gestión específica de un grado

#### Políticas Personalizadas

Las políticas controlan el acceso basado en:

-   Relaciones profesor-materia
-   Asignación director-grado
-   Contexto del período académico activo

## 🎯 Panel de Administración (Filament)

### Acceso

-   **URL**: `/liceo`
-   **Configuración**: `LiceoPanelProvider`

### Grupos de Navegación

```php
'Configuración Académica' => [GradoResource, MateriaResource, PeriodoResource]
'Gestión Académica' => [LogroResource]
'Gestión de Estudiantes' => [EstudianteResource, EstudianteLogroResource]
'Reportes' => [BoletinResource]
'Administración' => [UserResource]
```

### Widgets Contextuales

-   **StatsOverview**: Estadísticas dinámicas según rol del usuario
-   **CurrentPeriod**: Información del período académico activo

## 📊 Base de Datos - Aspectos Técnicos

### Tabla Pivot Crítica: `estudiante_logros`

```sql
-- Índices optimizados para consultas frecuentes
KEY `estudiante_periodo` (estudiante_id, periodo_id)
KEY `logro_periodo` (logro_id, periodo_id)
UNIQUE KEY `unique_evaluation` (estudiante_id, logro_id, periodo_id)
```

### Scopes Útiles en Modelos

```php
// Logro.php
->activos() // Solo logros activos
->porGrado($gradoId) // Filtro por grado específico
->porMateria($materiaId) // Filtro por materia

// Periodo.php
->activos() // Períodos activos
->porAñoEscolar($año) // Filtro por año escolar
```

### Soft Deletes y Cascadas

-   **Grado**: Elimina estudiantes en cascada
-   **Materia**: Elimina logros en cascada
-   **Estudiante**: Elimina evaluaciones en cascada

## 🔧 Configuraciones Específicas

### Filament Shield

Permisos granulares configurados para cada Resource:

-   `view_any`, `view`, `create`, `update`, `delete`
-   `restore`, `force_delete` para soft deletes

### Middleware del Panel

```php
// Orden específico requerido para Filament
StartSession::class,
AuthenticateSession::class,
ShareErrorsFromSession::class,
VerifyCsrfToken::class,
```

## 📈 Optimizaciones de Performance

### Consultas Optimizadas

```php
// Eager Loading recomendado para reportes
Estudiante::with(['grado', 'estudianteLogros.logro.materia'])->get()

// Consultas por lotes para evaluaciones masivas
EstudianteLogro::whereIn('estudiante_id', $estudianteIds)
    ->where('periodo_id', $periodoActivo)
    ->get()
```

### Cacheo Estratégico

```php
// Períodos activos (consulta frecuente)
Cache::remember('periodo_activo', 3600, fn() => Periodo::where('activo', true)->first())

// Estadísticas del dashboard
Cache::remember("stats_user_{$userId}", 1800, fn() => $this->calculateStats())
```

## ⚡ Comandos Artisan Personalizados

### Gestión de Períodos

```bash
# Activar nuevo período (desactiva el anterior)
php artisan periodo:activate {periodo_id}

# Generar reportes de período
php artisan reportes:periodo {periodo_id} --format=pdf
```

### Mantenimiento de Datos

```bash
# Limpiar evaluaciones huérfanas
php artisan cleanup:evaluaciones

# Recalcular estadísticas
php artisan stats:refresh
```

## 🐛 Debugging y Monitoreo

### Logs Críticos

-   `storage/logs/filament.log`: Errores del panel admin
-   `storage/logs/evaluaciones.log`: Problemas en asignación de logros

### Queries Problemáticas

```php
// Evitar N+1 en listados de estudiantes
Estudiante::with('grado')->paginate()

// Precarga para reportes de boletines
EstudianteLogro::with(['estudiante', 'logro.materia', 'periodo'])
```

## 🔒 Seguridad

### Validaciones Críticas

-   **UniqueDirectorGrado**: Un usuario solo puede ser director de un grado
-   **Constraint único**: Un estudiante no puede tener el mismo logro evaluado dos veces en el mismo período

### Políticas de Acceso

```php
// Profesor solo ve sus estudiantes
$this->user()->materias()->with('grados.estudiantes')

// Director de grupo ve solo su grado
$this->user()->directorGrado->estudiantes()
```

## 📱 Integraciones

### DomPDF (Reportes)

Configurado para generar boletines académicos con:

-   Logotipo institucional
-   Evaluaciones por período
-   Observaciones detalladas

### Livewire Volt

Componentes reactivos para:

-   Filtros dinámicos en listados
-   Formularios de evaluación en tiempo real

## 🚀 Despliegue

### Variables de Entorno Críticas

```env
APP_ENV=production
APP_DEBUG=false
FILAMENT_SHIELD_ENABLED=true
DB_CONNECTION=mysql
CACHE_DRIVER=redis (recomendado)
SESSION_DRIVER=redis (recomendado)
```

### Optimizaciones de Producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

---

> **Nota**: Esta documentación cubre los aspectos técnicos específicos del sistema. Para documentación de usuario final, consultar la wiki del proyecto.
