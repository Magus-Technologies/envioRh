# Cómo probar envíoRH (paso a paso, sin tecnicismos)

Guía simple para probar el sistema como si nunca hubieras programado.

---

## 1. Abrir el sistema

Abre tu navegador y entra a: **http://enviorh.test/**

Te aparecerá una pantalla de login. Entra con:

- **Correo:** `admin@example.com`
- **Contraseña:** `password`

---

## 2. Probar el auto-completado de RUC/DNI (lo nuevo)

Esto es lo que acabamos de instalar. Sirve para que escribas un RUC o DNI y el sistema llene solo los datos.

1. En el menú de arriba haz click en **Clientes**.
2. Click en el botón **+ Nuevo cliente** (arriba a la derecha).
3. En **Tipo de documento** elige **RUC**.
4. En **Número de documento** escribe un RUC real, por ejemplo: `20131312955` (es el de SUNAT).
5. Haz click fuera del campo (o presiona Tab).

**Qué debería pasar:**

- Arriba aparece un mensajito "Consultando…" con un spinner girando.
- En 1-2 segundos se llena automáticamente el campo **Razón social** y **Dirección**.
- El mensajito cambia a "✓ Datos obtenidos" en verde.

**Si pones un DNI** (8 dígitos como `12345678`) hace lo mismo pero llena **Nombres completos**.

Si ves el mensaje en rojo quiere decir que el RUC/DNI no existe o la API está caída.

6. Completa el correo y teléfono si quieres.
7. Click en **Guardar cliente**.
8. Debería aparecer arriba el mensaje verde "Cliente creado exitosamente".

---

## 3. Probar el flujo completo de un mes

Esto simula el trabajo real de un mes de recibos por honorarios.

### 3.1 — Crear el lote del mes

1. En el menú, click en **Lotes**.
2. Click en **+ Nuevo lote** (arriba a la derecha).
3. Elige **Mes** (por ej. Abril) y **Año** (2026).
4. Descripción opcional: "Honorarios abril".
5. Click en **Crear lote**.

Te llevará a la vista del lote recién creado (todo en 0).

### 3.2 — Importar recibos desde Excel

1. Dentro del lote, click en el botón **Importar** (arriba a la derecha).
2. En la pantalla de importación verás a la izquierda un botón **Descargar plantilla Excel**. Haz click ahí. Ahora tiene un loader para que no hagas varios clicks.
3. Abre el Excel que se descargó, llénalo con 2 o 3 recibos de prueba y guárdalo.
4. Vuelve al sistema y sube el archivo en la zona punteada.
5. Click en **Importar archivo**.

**Qué debería pasar:** el sistema te lleva al historial mostrando cuántos recibos fueron exitosos y cuántos fallaron.

### 3.3 — Ver reportes

Menú **Reportes** → tres opciones:

- **PLAME mensual** → agrupa todos los recibos por emisor, listo para SUNAT.
- **Retenciones** → te dice qué emisores superaron los S/ 1,500 y ya deben retener.
- **Resumen de lotes** → vista general del año.

Cada uno tiene botón **Exportar Excel** arriba a la derecha.

---

## 4. Emitir los recibos en SUNAT SOL

**Importante:** SUNAT no tiene carga masiva pública para emitir recibos por honorarios. Cada recibo se emite uno por uno en SOL. envíoRH **te genera un Excel de apoyo** para que no tengas que recordar nada.

### 4.1 — Exportar el Excel masivo del lote

1. Dentro del lote, click en **Exportar Excel masivo** (botón ámbar, arriba).
2. Se descarga un archivo `recibos_emision_LOTE-XXX_....xlsx`.
3. El Excel trae columnas en este orden listo para copiar/pegar a SOL:

   | Columna | Uso |
   |---|---|
   | Fecha emisión | fecha del recibo |
   | Tipo doc cliente | DNI o RUC |
   | N° documento cliente | ID del cliente |
   | Nombre / Razón social | nombre completo |
   | Descripción del servicio | concepto |
   | Moneda | PEN o USD |
   | Monto bruto | antes de retención |
   | ¿Retención 8%? | SÍ / NO |
   | Monto retención | calculado |
   | Monto neto | lo que cobra |
   | N° recibo SUNAT | lo llenas después |

4. Fila final con totales. Encabezado congelado.

### 4.2 — Emitir cada recibo en SOL

1. Entra a https://www.sunat.gob.pe/ con tu Clave SOL (RUC `10461249847`, usuario `ALONSOCD`).
2. Menú **Empresas → Comprobantes de Pago → Recibos por Honorarios → Emitir RHE**.
3. Por cada fila del Excel:
   - Copiar cliente (tipo doc, número, nombre).
   - Copiar descripción y monto.
   - Emitir. SOL te devuelve un número (ej. `E001-123`).
   - **Copia ese número y pégalo en la columna "N° recibo SUNAT" del Excel**, o vuelve al sistema y presiona **EMITIDO** en la fila del recibo e ingresa el número.

### 4.3 — Registrar en el sistema que el recibo ya fue emitido

Dos opciones:

- **Una por una desde el sistema:** en el lote, al costado de cada recibo hay un botón **EMITIDO**. Clic → escribes `E001-123` → guardar. El recibo cambia a estado "emitido".
- **Por lote (futuro):** si el ingeniero aprueba la Fase 2 con certificado digital, el sistema emite automáticamente los 200 contra SUNAT en un click.

---

## 5. Si algo sale mal

| Lo que ves | Qué significa | Qué hacer |
|---|---|---|
| Pantalla blanca | Error del servidor | Avísame, reviso el log |
| "No encontrado" en RUC | El RUC no existe o la API está caída | Intenta otro RUC real |
| No se guarda el cliente | Falta algún dato obligatorio | Revisa que llenaste número y nombre |
| El Excel no se descarga | Laragon apagado | Abre Laragon y dale "Iniciar todo" |

---

## Resumen corto

1. Entra con `admin@example.com` / `password`
2. Registra clientes (con auto-completado RUC/DNI)
3. Crea un lote del mes
4. Importa recibos por Excel
5. Mira los reportes
6. **Exporta Excel masivo** del lote → lo usas como checklist para emitir uno por uno en SOL
7. Por cada recibo emitido, marca **EMITIDO** en el sistema e ingresa el N° que asignó SUNAT

---

**Guía técnica complementaria:** ver [GUIA_PRUEBAS.md](GUIA_PRUEBAS.md) para el detalle de validaciones, checklist de producción y formato PLAME.
