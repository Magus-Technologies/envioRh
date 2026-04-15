# 📘 MANUAL DE INSTALACIÓN Y USO - Sistema RHE

## Sistema de Emisión Masiva de Recibos por Honorarios Electrónicos

---

## 📋 REQUERIMIENTOS

### Servidor Local (Laragon)
- ✅ Laragon instalado (con Apache, MySQL, PHP 8.1+)
- ✅ Composer instalado
- ✅ Navegador web moderno (Chrome, Firefox, Edge)

### Requisitos Técnicos
- PHP >= 8.1
- MySQL >= 8.0
- Apache (incluido en Laragon)
- Extensiones PHP: mbstring, xml, json, curl

---

## 🚀 INSTALACIÓN

### Paso 1: Verificar Instalación de Laravel

El proyecto ya tiene Laravel instalado en:
```
C:\laragon\www\envioRH
```

Si necesitas reinstalar:
```bash
cd C:\laragon\www\envioRH
composer create-project laravel/laravel .
```

### Paso 2: Configurar Base de Datos

La base de datos ya está configurada en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_rhe
DB_USERNAME=root
DB_PASSWORD=
```

### Paso 3: Ejecutar Migraciones

Las migraciones YA se ejecutaron correctamente. Si necesitas re-ejecutar:
```bash
php artisan migrate:fresh --seed
```

Esto creará:
- 8 tablas principales
- Usuario administrador por defecto
- Configuración de empresa por defecto

### Paso 4: Acceder al Sistema

1. Iniciar Laragon
2. Verificar que Apache y MySQL estén activos
3. Abrir navegador en: `http://localhost/envioRH`

---

## 🏗️ ESTRUCTURA DEL SISTEMA

### Base de Datos (8 Tablas)

| Tabla | Descripción |
|-------|-------------|
| `configuracion_empresa` | Datos de la empresa emisora |
| `clientes` | Clientes que reciben el servicio |
| `lote_emision` | Lotes de emisión masiva |
| `recibos` | Recibos individuales |
| `historial_emision` | Trazabilidad de acciones |
| `retenciones_mensuales` | Control de retenciones 8% |
| `usuarios` | Usuarios del sistema |
| `archivos_importados` | Registro de importaciones |

### Modelos y Controladores

✅ 8 Modelos con relaciones
✅ 5 Controladores principales
✅ 3 Servicios de negocio

---

## 🎯 FUNCIONALIDADES PRINCIPALES

### 1. Gestión de Lotes

**Crear un lote:**
```
GET /lotes/create
```

- Define período (mes/año)
- Descripción opcional
- Código se genera automáticamente

**Ver detalle de lote:**
```
GET /lotes/{id}
```

Muestra:
- Estadísticas (total, montos, retenciones)
- Lista de recibos
- Historial de actividades
- Botones para generar/descargar archivo

### 2. Importación Masiva

**Importar desde Excel/CSV:**
```
GET /lotes/{id}/importar
```

**Descargar plantilla:**
```
GET /importar/plantilla
```

**Formato de la plantilla Excel:**
| Columna | Descripción | Ejemplo |
|---------|-------------|---------|
| A | Tipo Doc Emisor | DNI |
| B | Doc Emisor | 12345678 |
| C | Nombre Emisor | Juan Pérez |
| D | Tipo Doc Cliente | RUC |
| E | Doc Cliente | 20123456789 |
| F | Nombre Cliente | Empresa SAC |
| G | Descripción | Servicio de consultoría |
| H | Monto | 1500.00 |
| I | Fecha Emisión | 2026-04-01 |
| J | Fecha Vencimiento | 2026-04-30 |
| K | Moneda | PEN |
| L | N° Continuación | CONT-001 (opcional) |

### 3. Generación de Archivo SUNAT

**Generar archivo:**
```
POST /lotes/{id}/generar-archivo
```

**Formato del archivo (TXT):**
```
E|1|12345678|2026-04-01|PEN
D|6|20123456789|Servicio de consultoría|1500.00|2026-04-30
D|6|20123456789|Servicio de desarrollo|2500.00|2026-04-30
```

**Descargar archivo:**
```
GET /lotes/{id}/descargar
```

### 4. Cálculo Automático de Retención

**Regla:**
- Tope mensual exonerado: S/ 1,500
- Porcentaje de retención: 8%
- Se aplica cuando el acumulado mensual supera el tope

**Ejemplo:**
```
Emisor: DNI 12345678
Mes: Abril 2026

Recibo 1: S/ 800  → No aplica retención (acumulado: 800 < 1500)
Recibo 2: S/ 900  → Aplica retención (acumulado: 1700 > 1500)
                    Retención: 900 × 8% = S/ 72
                    Neto: 900 - 72 = S/ 828
```

### 5. Reportes

**Reporte PLAME:**
```
GET /reportes/plame?mes=4&anio=2026
```

**Reporte de Retenciones:**
```
GET /reportes/retenciones?mes=4&anio=2026
```

**Resumen de Lotes:**
```
GET /reportes/resumen-lotes
```

**Exportar a Excel:**
```
GET /reportes/exportar?tipo=plame&mes=4&anio=2026
```

---

## 📁 ARCHIVOS GENERADOS

### Archivos SUNAT

Se guardan en: `storage/app/exports/sunat/`

Nombre de archivo: `RHE_LOTE-202604-001_20260413153045.txt`

### Importaciones

Se guardan en: `storage/app/importaciones/`

---

## 🔐 USUARIO POR DEFECTO

| Campo | Valor |
|-------|-------|
| Email | admin@sistema-rhe.com |
| Password | admin123 |

---

## 🎨 VISTAS DISPONIBLES

### ✅ Implementadas
- ✅ Página de inicio (welcome)
- ✅ Lista de lotes (con filtros)
- ✅ Crear lote
- ✅ Detalle de lote (con recibos e historial)

### ⏳ Pendientes
- ⏳ CRUD de clientes
- ⏳ Formulario de importación
- ⏳ Reportes (PLAME, retenciones)
- ⏳ Autenticación (login/registro)

---

## 🧪 PRUEBAS RÁPIDAS

### Test 1: Crear y Poblar un Lote

```bash
# 1. Crear lote desde la interfaz
http://localhost/envioRH/lotes/create

# 2. Importar Excel (usar plantilla)
http://localhost/envioRH/lotes/{id}/importar

# 3. Generar archivo SUNAT
# Botón en detalle del lote

# 4. Descargar archivo
http://localhost/envioRH/lotes/{id}/descargar
```

### Test 2: Verificar Retenciones

```bash
# Ver acumulado de retenciones
http://localhost/envioRH/reportes/retenciones?mes=4&anio=2026
```

---

## ⚠️ LIMITACIONES IMPORTANTES

### Lo que el sistema SÍ hace:
✅ Registra recibos masivamente
✅ Calcula retenciones automáticamente
✅ Genera archivo en formato SUNAT
✅ Genera reportes PLAME/PDT
✅ Lleva historial completo

### Lo que el sistema NO hace:
❌ NO emite recibos directamente en SUNAT
❌ NO se conecta automáticamente a SUNAT
❌ NO firma electrónicamente
❌ NO requiere OSE/PSE

**El usuario debe:**
1. Descargar el archivo generado
2. Entrar a SUNAT con clave SOL
3. Subir archivo a "Emisión Masiva de RH"
4. Confirmar emisión

---

## 🛠️ COMANDOS ÚTILES

```bash
# Ver rutas
php artisan route:list

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Ver logs
storage/logs/laravel.log

# Crear controlador
php artisan make:controller NombreController

# Crear modelo
php artisan make:model NombreModelo

# Crear migración
php artisan make:migration create_nombre_table
```

---

## 📞 SOPORTE

### Problemas Comunes

**Error: "Table doesn't exist"**
```bash
php artisan migrate:fresh --seed
```

**Error: "Key column doesn't exist"**
- Verificar orden de migraciones
- Ejecutar `php artisan migrate:fresh`

**Error de permisos**
```bash
# En Linux
chmod -R 775 storage bootstrap/cache

# En Windows (Laragon)
# Verificar que Apache tenga permisos de escritura
```

**Error al importar Excel**
- Verificar formato de la plantilla
- Asegurar que los montos sean numéricos
- Verificar fechas en formato YYYY-MM-DD

---

## 🎯 PRÓXIMAS MEJORAS

- [ ] Implementar vistas faltantes (clientes, reportes)
- [ ] Sistema de autenticación completo
- [ ] Validación de RUC/DNI peruano
- [ ] Tests unitarios
- [ ] Dashboard con gráficos
- [ ] Envío automático por email
- [ ] Integración con API SUNAT (si disponible)

---

## 📄 LICENCIA

Este proyecto es de uso interno para la empresa.

---

**Fecha de última actualización:** 13 de Abril, 2026
**Versión:** 1.0.0 (MVP)
