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

## 4. Probar contra SUNAT (lo real)

SUNAT **no tiene ambiente de pruebas** para Recibos por Honorarios. Se prueba directo con tu **Clave SOL** real. Por eso lo importante es validar internamente antes.

**Pasos:**

1. Desde el lote en envíoRH, click en **Generar archivo SUNAT** y descarga el archivo.
2. Entra a https://www.sunat.gob.pe/ con tu Clave SOL.
3. Menú **Planilla Electrónica → PLAME → Presentación**.
4. Elige el período (mes/año).
5. En la sección **4ta Categoría (recibos por honorarios)** busca la opción **Importar desde archivo**.
6. Sube el archivo descargado de envíoRH.
7. SUNAT te mostrará los recibos importados. Revisa que todo cuadre.
8. Si todo bien → **Presentar declaración** → SUNAT te da una constancia con número de orden.

**Antes de presentar revisa:**

- Todos los RUC/DNI tengan 11 u 8 dígitos.
- Los montos coincidan con los recibos reales.
- La retención del 8% solo aparezca cuando un emisor ya pasó los S/ 1,500 acumulados en el mes.

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
6. Genera archivo SUNAT y súbelo a tu Clave SOL real

---

**Guía técnica complementaria:** ver [GUIA_PRUEBAS.md](GUIA_PRUEBAS.md) para el detalle de validaciones, checklist de producción y formato PLAME.
