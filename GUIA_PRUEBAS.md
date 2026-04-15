# Guía de pruebas — envíoRH

Sistema de gestión de Recibos por Honorarios Electrónicos (RHE) para declaración PLAME en SUNAT.

---

## 1. Acceso al sistema

**URL local:** `http://enviorh.test/`
**URL VPS:** `http://84.247.162.204/enviorh`

**Credenciales demo:**
| Rol | Usuario | Contraseña |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| Usuario | `usuario@example.com` | `password` |

---

## 2. Flujo de prueba completo (local)

El sistema sigue 4 pasos: **Lote → Importar/Crear recibos → Generar archivo SUNAT → Subir a SUNAT**.

### Paso 1 — Registrar clientes

1. Ir a **Clientes → Nuevo cliente**.
2. Llenar:
   - Tipo documento: `RUC` (o DNI/CE)
   - Número: `20123456789` (RUC válido de prueba)
   - Razón social: `Empresa Demo SAC`
   - Dirección, correo, teléfono (opcionales)
3. Guardar.

**Qué validar:**
- Aparece toast "Cliente creado exitosamente".
- Se lista en `/clientes` con estado ACTIVO.
- Botones VER / EDITAR / BORRAR funcionan.

### Paso 2 — Crear un lote mensual

1. Ir a **Lotes → Nuevo lote**.
2. Seleccionar **Mes** y **Año** (el código se autogenera: `LOTE-YYYYMM-###`).
3. Guardar.

**Qué validar:**
- Redirige a la vista del lote nuevo en estado `PENDIENTE`.
- Totales en 0 (Recibos, Bruto, Retención, Neto).

### Paso 3 — Agregar recibos (dos opciones)

#### A) Importación masiva Excel

1. En la vista del lote → **Importar**.
2. Click en **Descargar plantilla Excel** (ahora con loader para evitar doble click).
3. Llenar el Excel con columnas: fecha emisión, serie, número, cliente, monto bruto, concepto, etc.
4. Subir el archivo.
5. Verificar resultado en **Historial** del lote.

**Qué validar:**
- Clientes nuevos se crean automáticamente si no existen.
- Registros exitosos / fallidos se muestran en el historial.
- Los totales del lote se actualizan.

#### B) Registro manual

1. Desde la vista del lote → **Agregar recibo** (si aplica).
2. Llenar serie/número, seleccionar cliente, monto bruto.
3. La **retención del 8%** se calcula automáticamente si supera el tope acumulado mensual de **S/ 1,500**.

**Qué validar:**
- Monto neto = bruto − retención.
- Cliente con acumulado ≤ S/ 1,500 NO retiene.
- Cliente con acumulado > S/ 1,500 SÍ retiene 8% sobre el exceso.

### Paso 4 — Generar archivo SUNAT

1. En la vista del lote → **Generar archivo SUNAT**.
2. Descargar el `.txt` o `.xlsx` generado.

**Qué validar:**
- Archivo se genera sin errores.
- Formato de línea respeta la estructura PLAME (ver sección 4).
- Estado del lote cambia a `GENERADO`.

### Paso 5 — Reportes

- **Panel → Reportes → PLAME**: agrupación por emisor para el mes.
- **Retenciones mensuales**: control del tope S/ 1,500 por emisor.
- **Resumen de lotes**: vista consolidada anual.
- Cada reporte tiene botón **Exportar Excel**.

---

## 3. Pruebas en SUNAT (PLAME)

SUNAT no tiene un "ambiente sandbox" público para RHE como sí lo tiene CPE (Facturación electrónica). Para RHE/PLAME se usa directamente el portal real con tu **Clave SOL**.

### 3.1 — Ambiente de pruebas: SUNAT Operaciones en Línea (SOL)

1. Ingresar a: https://www.sunat.gob.pe/
2. Clave SOL → **Opciones personales** → **Mis declaraciones informativas**.
3. Menú: **Planilla Electrónica → PLAME**.

### 3.2 — Importar archivo generado por envíoRH

El PLAME acepta:
- Formato **.txt** separado por `|`.
- Formato **.xlsx** con estructura definida por SUNAT.

**Pasos:**

1. Desde envíoRH, generar archivo SUNAT del lote y descargarlo.
2. En el PDT PLAME (aplicativo de escritorio) o en el formulario virtual 601:
   - Abrir la declaración del período correspondiente.
   - Ir a **4ta Categoría → Importar desde archivo**.
   - Seleccionar el archivo descargado de envíoRH.
3. Validar que los recibos se importen correctamente:
   - Número de RUC del prestador.
   - Monto bruto, retención, neto.
   - Fecha de emisión y período.

### 3.3 — Validar antes de enviar

Antes de presentar la declaración a SUNAT, verificar:

- [ ] Todos los recibos del mes están incluidos (comparar con reporte PLAME interno).
- [ ] RUCs / DNIs de emisores son válidos (11 o 8 dígitos).
- [ ] Montos coinciden con los recibos físicos/digitales del SEE-SOL.
- [ ] La retención aplica solo cuando el acumulado del emisor supera S/ 1,500 en el mes.
- [ ] Los estados de pago son coherentes (pagado / pendiente).

### 3.4 — Presentar a SUNAT

1. Una vez validada la declaración en el PDT/formulario:
2. **Enviar** → SUNAT devuelve **Constancia de presentación** con número de orden.
3. Descargar y archivar la constancia.
4. En envíoRH: marcar el lote como **EMITIDO** (si hay esa acción disponible).

### 3.5 — Plazos SUNAT (referencia)

El PLAME se presenta **mensualmente** según último dígito de RUC, típicamente entre el día 8 y 22 del mes siguiente. Consultar el cronograma anual de SUNAT.

> ⚠️ No presentar en plazo genera multa (UIT fraccionada). No subir recibos con retención pendiente puede generar observaciones en fiscalización.

---

## 4. Formato PLAME — estructura mínima (referencia)

Cada línea del archivo TXT representa un recibo del mes:

```
TIPO_DOC|NUM_DOC|SERIE|NUMERO|FECHA_EMISION|MONTO_BRUTO|RETENCION|MONTO_NETO|MONEDA|ESTADO
```

Ejemplo:
```
1|12345678|E001|00000123|2026-04-10|2500.00|200.00|2300.00|PEN|EMITIDO
6|20123456789|E001|00000124|2026-04-12|800.00|0.00|800.00|PEN|EMITIDO
```

Códigos TIPO_DOC: `1`=DNI, `4`=Carnet de extranjería, `6`=RUC, `7`=Pasaporte.

---

## 5. Casos de prueba recomendados

| # | Caso | Esperado |
|---|---|---|
| 1 | Emisor nuevo con 1 recibo de S/ 1,000 | Sin retención (no supera S/ 1,500) |
| 2 | Mismo emisor, 2do recibo de S/ 800 en el mes | Retención 8% solo sobre el exceso (S/ 300 × 8% = S/ 24) |
| 3 | Emisor con 1 recibo de S/ 2,000 en un mes nuevo | Retención 8% sobre todo (S/ 160) |
| 4 | Importar Excel con 50 recibos | Todos creados, clientes auto-registrados |
| 5 | Generar archivo de lote vacío | Error claro, no genera archivo |
| 6 | Presentar lote ya emitido | Bloqueado, no permite regenerar |

---

## 6. Checklist de salida a producción (VPS)

- [ ] `.env` con `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=http://84.247.162.204/enviorh`.
- [ ] `SESSION_PATH=/enviorh`, `SESSION_SECURE_COOKIE=false` (o true si HTTPS).
- [ ] `npm run build` ejecutado (manifest en `public/build/`).
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
- [ ] Permisos 775 en `storage/` y `bootstrap/cache/`.
- [ ] `.htaccess` raíz redirige a `public/`.
- [ ] Base de datos migrada: `php artisan migrate --force`.
- [ ] Seeder ejecutado una vez: `php artisan db:seed --force`.
- [ ] Backup automatizado de DB (cron diario).

---

## 7. Problemas comunes

| Problema | Causa | Solución |
|---|---|---|
| "View [x] not found" | Cache de vistas desactualizado | `php artisan view:clear` |
| CSS sin estilos en VPS | Manifest no compilado | `npm run build` en el servidor |
| Login redirige a `/` infinito | `APP_URL` mal o `SESSION_PATH` incorrecto | Revisar `.env` y `SESSION_DOMAIN` |
| Error `updated_at` en insert | Modelo con timestamps en tabla sin ellos | `public $timestamps = false;` |
| "Undefined array key" en reportes | Mes llega como string `"04"` | Cast explícito `(int) $mes` |

---

**Contacto soporte:** revisar `storage/logs/laravel.log` para trazas. Estado del sistema en el footer (indicador "ACTIVO").
