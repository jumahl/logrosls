# GUÍA DE TROUBLESHOOTING - LOGROSLS

## 📋 **OVERVIEW**

Esta guía proporciona soluciones sistemáticas para los problemas más comunes que pueden surgir en LogrosLS, desde errores de configuración hasta problemas de performance y datos.

---

## 🚨 **PROBLEMAS COMUNES**

### **1. ERRORES DE INSTALACIÓN**

#### **Error: "Class 'PDO' not found"**

**Síntomas:**

```
Fatal error: Class 'PDO' not found in vendor/laravel/framework/src/Illuminate/Database/Connection.php
```

**Causa:** Extensión PDO de PHP no está instalada

**Solución:**

```bash
# Ubuntu/Debian
sudo apt-get install php8.3-mysql php8.3-pdo

# CentOS/RHEL
sudo yum install php-pdo php-mysql

# Windows (XAMPP)
# Descomentar en php.ini:
extension=pdo_mysql

# Reiniciar servidor web
sudo systemctl restart apache2
# o
sudo systemctl restart nginx
```

#### **Error: "Specified key was too long"**

**Síntomas:**

```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes
```

**Causa:** Versión antigua de MySQL con charset utf8mb4

**Solución:**

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => 'InnoDB ROW_FORMAT=DYNAMIC', // Agregar esta línea
],
```

O en el AppServiceProvider:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Schema;

public function boot()
{
    Schema::defaultStringLength(191);
}
```

#### **Error: "No application encryption key has been specified"**

**Síntomas:**

```
RuntimeException: No application encryption key has been specified.
```

**Solución:**

```bash
# Generar nueva clave
php artisan key:generate

# Si persiste, verificar .env
cat .env | grep APP_KEY

# Debe mostrar algo como:
# APP_KEY=base64:abcd1234...
```

---

### **2. PROBLEMAS DE AUTENTICACIÓN**

#### **Error: "403 Forbidden" en Panel de Filament**

**Síntomas:**

-   Usuario logueado pero no puede acceder a recursos
-   Mensaje "403 This action is unauthorized"

**Diagnóstico:**

```bash
# Verificar roles del usuario
php artisan tinker
>>> $user = User::find(1);
>>> $user->roles;
>>> $user->permissions;
```

**Solución:**

```bash
# Regenerar permisos de Shield
php artisan shield:generate --all

# Asignar rol admin a usuario
php artisan tinker
>>> $user = User::find(1);
>>> $user->assignRole('admin');
>>> $user->givePermissionTo(Permission::all());
```

#### **Sesiones no Persistentes**

**Síntomas:**

-   Usuario se deslogea constantemente
-   Sesiones no se mantienen entre requests

**Diagnóstico:**

```bash
# Verificar configuración de sesión
php artisan config:show session

# Verificar permisos de storage
ls -la storage/framework/sessions/
```

**Solución:**

```bash
# Verificar configuración en .env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Limpiar cache de configuración
php artisan config:clear

# Verificar permisos
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

---

### **3. PROBLEMAS DE BASE DE DATOS**

#### **Error: "SQLSTATE[HY000] [2002] Connection refused"**

**Síntomas:**

```
SQLSTATE[HY000] [2002] Connection refused
```

**Diagnóstico:**

```bash
# Verificar estado de MySQL
sudo systemctl status mysql

# Verificar conexión
mysql -u root -p

# Verificar configuración
php artisan config:show database.connections.mysql
```

**Solución:**

```bash
# Reiniciar MySQL
sudo systemctl restart mysql

# Verificar usuarios y permisos
mysql -u root -p
mysql> SELECT User, Host FROM mysql.user;
mysql> GRANT ALL PRIVILEGES ON logrosls.* TO 'logrosls_user'@'localhost';
mysql> FLUSH PRIVILEGES;
```

#### **Migraciones Fallando**

**Síntomas:**

```
Migration table not found
SQLSTATE[42S02]: Base table or view not found
```

**Solución:**

```bash
# Verificar estado de migraciones
php artisan migrate:status

# Recrear tabla de migraciones
php artisan migrate:install

# Ejecutar migraciones paso a paso
php artisan migrate --step

# Si hay problemas, rollback y retry
php artisan migrate:rollback
php artisan migrate
```

#### **Datos Inconsistentes**

**Síntomas:**

-   Estudiantes sin grado
-   Desempeños sin logros asociados
-   Referencias huérfanas

**Diagnóstico:**

```sql
-- Verificar integridad referencial
SELECT COUNT(*) FROM estudiantes WHERE grado_id NOT IN (SELECT id FROM grados);
SELECT COUNT(*) FROM desempenos_materia WHERE estudiante_id NOT IN (SELECT id FROM estudiantes);
SELECT COUNT(*) FROM estudiante_logros WHERE desempeno_materia_id NOT IN (SELECT id FROM desempenos_materia);
```

**Solución:**

```bash
# Comando personalizado para limpiar datos
php artisan db:clean-orphaned-records

# O manualmente:
php artisan tinker
>>> EstudianteLogro::whereNotIn('desempeno_materia_id', DesempenoMateria::pluck('id'))->delete();
>>> DesempenoMateria::whereNotIn('estudiante_id', Estudiante::pluck('id'))->delete();
```

---

### **4. PROBLEMAS DE PERFORMANCE**

#### **Consultas N+1**

**Síntomas:**

-   Páginas lentas para cargar
-   Alto número de consultas en debug bar

**Diagnóstico:**

```php
// Habilitar query log
DB::enableQueryLog();
// ... ejecutar operación lenta
dd(DB::getQueryLog());
```

**Solución:**

```php
// Usar eager loading apropiado
$estudiantes = Estudiante::with([
    'grado',
    'desempenosMateria.materia',
    'desempenosMateria.estudianteLogros.logro'
])->get();

// En lugar de:
$estudiantes = Estudiante::all();
foreach ($estudiantes as $estudiante) {
    echo $estudiante->grado->nombre; // N+1 query
}
```

#### **Memoria Insuficiente**

**Síntomas:**

```
Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Solución:**

```php
// Para exports grandes, usar chunks
// EstudiantesExport.php
public function collection()
{
    return Estudiante::with('grado')->lazy(); // Usa lazy loading
}

// O aumentar memoria temporalmente
ini_set('memory_limit', '512M');

// En comandos Artisan
protected function handle()
{
    ini_set('memory_limit', '1G');
    // ... lógica del comando
}
```

#### **Redis Out of Memory**

**Síntomas:**

```
MISCONF Redis is configured to save RDB snapshots, but is currently not able to persist on disk
```

**Solución:**

```bash
# Verificar uso de memoria Redis
redis-cli info memory

# Limpiar cache si es necesario
php artisan cache:clear
redis-cli flushall

# Configurar política de evicción
redis-cli config set maxmemory-policy allkeys-lru
```

---

### **5. PROBLEMAS DE FILAMENT**

#### **Resources no Aparecen en Navegación**

**Síntomas:**

-   Resources creados pero no visibles en menú
-   Usuario con permisos pero sin acceso

**Diagnóstico:**

```bash
# Verificar registro de resources
php artisan route:list | grep filament

# Verificar permisos
php artisan shield:check-permissions
```

**Solución:**

```bash
# Regenerar permisos
php artisan shield:generate --all

# Verificar en el Resource
public static function shouldRegisterNavigation(): bool
{
    return auth()->user()->can('viewAny', static::getModel());
}

# Limpiar cache
php artisan filament:optimize
```

#### **Formularios no Guardan**

**Síntomas:**

-   Formulario se envía pero no guarda datos
-   Validación pasa pero modelo no se actualiza

**Diagnóstico:**

```php
// En el Resource, agregar debug
protected function mutateFormDataBeforeCreate(array $data): array
{
    logger('Form data before create:', $data);
    return $data;
}
```

**Solución:**

```php
// Verificar fillable en modelo
protected $fillable = [
    'campo1',
    'campo2',
    // Agregar campos faltantes
];

// Verificar políticas
public function create(User $user): bool
{
    return $user->hasRole(['admin', 'profesor']);
}
```

#### **RelationManagers no Funcionan**

**Síntomas:**

-   Relación existe en modelo pero no se muestra
-   Error al cargar datos relacionados

**Solución:**

```php
// Verificar relación en modelo
public function logros(): HasManyThrough
{
    return $this->hasManyThrough(
        Logro::class,
        Materia::class,
        'id', // Foreign key en tabla intermedia
        'materia_id', // Foreign key en tabla final
        'id', // Local key en tabla actual
        'id'  // Local key en tabla intermedia
    );
}

// En RelationManager, sobreescribir query si es necesario
public function getEloquentQuery(): Builder
{
    return Logro::whereHas('materia.grados', function ($query) {
        $query->where('grados.id', $this->getOwnerRecord()->id);
    });
}
```

---

### **6. PROBLEMAS DE IMPORTACIÓN/EXPORTACIÓN**

#### **Error en Import de Excel**

**Síntomas:**

```
Maatwebsite\Excel\Validators\ValidationException: The given data was invalid.
```

**Diagnóstico:**

```php
// En la clase Import, agregar debug
public function model(array $row)
{
    logger('Import row:', $row);

    try {
        return new Estudiante([
            'nombre' => $row['nombre'],
            // ...
        ]);
    } catch (\Exception $e) {
        logger('Import error:', ['error' => $e->getMessage(), 'row' => $row]);
        throw $e;
    }
}
```

**Solución:**

```php
// Agregar validación en Import
public function rules(): array
{
    return [
        'nombre' => 'required|string|max:255',
        'documento' => 'required|string|unique:estudiantes,documento',
        'grado_id' => 'required|exists:grados,id',
    ];
}

// Manejo de errores personalizado
public function customValidationMessages()
{
    return [
        'documento.unique' => 'El documento :input ya existe en el sistema.',
    ];
}
```

#### **Export Timeout**

**Síntomas:**

```
Maximum execution time of 30 seconds exceeded
```

**Solución:**

```php
// En Export class, usar queued exports para grandes volúmenes
class EstudiantesExport implements FromCollection, ShouldQueue
{
    use Queueable;

    public function collection()
    {
        return Estudiante::with('grado')->lazy();
    }
}

// O aumentar timeout para exports específicos
public function export()
{
    set_time_limit(300); // 5 minutos
    return Excel::download(new EstudiantesExport, 'estudiantes.xlsx');
}
```

---

## 🔧 **HERRAMIENTAS DE DIAGNÓSTICO**

### **Comandos Útiles**

```bash
# Verificar configuración completa
php artisan config:show

# Verificar estado de la aplicación
php artisan about

# Verificar rutas
php artisan route:list

# Limpiar todos los caches
php artisan optimize:clear

# Verificar permisos de archivos
php artisan storage:link

# Debug de cola de trabajos
php artisan queue:work --verbose

# Verificar logs en tiempo real
tail -f storage/logs/laravel.log
```

### **Comandos de Base de Datos**

```bash
# Verificar conexión
php artisan db:show

# Estado de migraciones
php artisan migrate:status

# Verificar seeders
php artisan db:seed --class=DatabaseSeeder --verbose

# Backup rápido
php artisan backup:run --only-db
```

### **Debugging con Tinker**

```php
// Verificar usuario y permisos
php artisan tinker
>>> $user = User::find(1);
>>> $user->roles->pluck('name');
>>> $user->getAllPermissions()->pluck('name');

// Verificar relaciones
>>> $estudiante = Estudiante::first();
>>> $estudiante->grado;
>>> $estudiante->desempenosMateria->count();

// Verificar datos específicos
>>> DesempenoMateria::where('estado', 'borrador')->count();
>>> EstudianteLogro::where('alcanzado', false)->count();
```

---

## 📊 **LOGS Y MONITOREO**

### **Configuración de Logs Específicos**

```php
// config/logging.php - Canales personalizados
'channels' => [
    'evaluaciones' => [
        'driver' => 'daily',
        'path' => storage_path('logs/evaluaciones.log'),
        'level' => 'debug',
        'days' => 14,
    ],

    'imports' => [
        'driver' => 'daily',
        'path' => storage_path('logs/imports.log'),
        'level' => 'info',
        'days' => 30,
    ],

    'performance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/performance.log'),
        'level' => 'info',
        'days' => 7,
    ],
]
```

### **Logging en Puntos Críticos**

```php
// En modelos importantes
class DesempenoMateria extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            Log::channel('evaluaciones')->info('Creando desempeño', [
                'estudiante_id' => $model->estudiante_id,
                'materia_id' => $model->materia_id,
                'user_id' => auth()->id()
            ]);
        });
    }
}

// En imports
public function model(array $row)
{
    try {
        $estudiante = new Estudiante($this->mapRowToAttributes($row));

        Log::channel('imports')->info('Estudiante importado', [
            'documento' => $estudiante->documento,
            'nombre' => $estudiante->nombre
        ]);

        return $estudiante;
    } catch (\Exception $e) {
        Log::channel('imports')->error('Error en import', [
            'row' => $row,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
```

---

## 🚨 **PROCEDIMIENTOS DE EMERGENCIA**

### **Restaurar desde Backup**

```bash
# 1. Parar aplicación
php artisan down

# 2. Restaurar base de datos
mysql -u root -p logrosls < backup_logrosls_20240915.sql

# 3. Restaurar archivos
tar -xzf files_backup_20240915.tar.gz -C /var/www/logrosls/

# 4. Verificar permisos
sudo chown -R www-data:www-data /var/www/logrosls/storage
sudo chmod -R 755 /var/www/logrosls/storage

# 5. Limpiar caches
php artisan optimize:clear

# 6. Verificar funcionamiento
php artisan migrate:status
php artisan shield:check-permissions

# 7. Reactivar aplicación
php artisan up
```

### **Recuperación de Datos**

```php
// Script de recuperación de datos corruptos
php artisan tinker

// Buscar registros huérfanos
>>> $huerfanos = EstudianteLogro::whereDoesntHave('desempenoMateria')->get();

// Recrear relaciones perdidas
>>> foreach ($huerfanos as $logro) {
>>>     $desempeno = DesempenoMateria::where('estudiante_id', $logro->estudiante_id)
>>>         ->where('materia_id', $logro->logro->materia_id)
>>>         ->first();
>>>     if ($desempeno) {
>>>         $logro->update(['desempeno_materia_id' => $desempeno->id]);
>>>     }
>>> }
```

### **Contactos de Emergencia**

```
📞 ESCALACIÓN DE INCIDENTES

Nivel 1 - Soporte Técnico:
- Email: soporte@liceo.edu.co
- Tel: +57 300 123 4567
- Horario: 7:00 AM - 6:00 PM

Nivel 2 - Desarrollador Principal:
- Email: dev@liceo.edu.co
- Tel: +57 300 765 4321
- Horario: 24/7 para emergencias críticas

Nivel 3 - Administrador de Sistemas:
- Email: admin@liceo.edu.co
- Tel: +57 300 987 6543
- Horario: 24/7 para caídas del sistema

Hosting Provider:
- Panel: https://panel.hosting.com
- Usuario: liceo_admin
- Soporte: +57 1 234 5678
```

---

## 📋 **CHECKLIST DE RESOLUCIÓN**

### **Para Cualquier Problema**

1. **Identificar el Problema**

    - [ ] Describir síntomas exactos
    - [ ] Reproducir el error
    - [ ] Capturar mensajes de error completos

2. **Recopilar Información**

    - [ ] Verificar logs de aplicación
    - [ ] Verificar logs de servidor web
    - [ ] Verificar logs de base de datos
    - [ ] Verificar uso de recursos (CPU, memoria, disco)

3. **Diagnóstico Inicial**

    - [ ] Verificar configuración de entorno
    - [ ] Verificar estado de servicios
    - [ ] Verificar conectividad de red
    - [ ] Verificar permisos de archivos

4. **Aplicar Solución**

    - [ ] Implementar fix más simple primero
    - [ ] Documentar cambios realizados
    - [ ] Verificar que el problema se resolvió
    - [ ] Verificar que no se crearon problemas nuevos

5. **Seguimiento**
    - [ ] Monitorear por 24-48 horas
    - [ ] Documentar solución en knowledge base
    - [ ] Identificar mejoras preventivas
    - [ ] Comunicar resolución a stakeholders

---

**Última actualización**: Septiembre 2025  
**Versión**: 1.0  
**Mantenido por**: Equipo de Soporte LogrosLS
