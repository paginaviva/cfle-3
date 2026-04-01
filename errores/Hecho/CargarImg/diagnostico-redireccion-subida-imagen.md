# Diagnóstico: Redirección Incorrecta tras Subir Imagen

**Fecha:** 2026-03-29  
**Problema:** Al subir imagen, la página redirige a pantalla de carga de PDF  
**Archivo de análisis:** `temp/diagnostico-redireccion-subida-imagen.md`

---

## Índice de Contenidos

1. [Objetivo del Diagnóstico](#1-objetivo-del-diagnóstico)
2. [Metodología de Análisis](#2-metodología-de-análisis)
3. [Flujo Actual del Código](#3-flujo-actual-del-código)
4. [Raíz del Problema](#4-raíz-del-problema)
5. [Evidencia del Código](#5-evidencia-del-código)
6. [Soluciones Propuestas](#6-soluciones-propuestas)
7. [Recomendación](#7-recomendación)

---

## 1. Objetivo del Diagnóstico

Identificar la causa raíz por la cual, después de hacer clic en "📷 Subir imagen" en la pantalla de resultados, la aplicación redirige a la pantalla de "Procesar Ficha Técnica" (carga de PDF) en lugar de permanecer en resultados y habilitar el botón "🌐 Alta producto en Web".

---

## 2. Metodología de Análisis

**Enfoque:** Ingeniería inversa del código fuente  
**Archivo analizado:** `src/php/carga_pdf.php`  
**Técnica:** Trazado del flujo de ejecución POST

---

## 3. Flujo Actual del Código

### Estructura de `carga_pdf.php`

```
┌─────────────────────────────────────────┐
│ 1. Inicio del script                    │
│    - Inicializar variables              │
│    - $imageUploaded = false             │
│    - Leer sesión para estado imagen     │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│ 2. LÓGICA DE SUBIDA DE IMAGEN           │
│    if (POST && isset($_FILES['image'])) │
│    - Procesa subida                     │
│    - Setea $imageUploaded = true        │
│    - Guarda en sesión                   │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│ 3. LÓGICA DE SUBIDA DE PDF              │
│    if (POST && isset($_FILES['pdf']))   │
│    - Procesa PDF                        │
│    - Setea $uploadedFile                │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│ 4. LÓGICA DE PROCESAMIENTO (OpenAI)     │
│    if (POST && isset($_POST['filepath'])│
│    - Llama a OpenAI                     │
│    - Setea $processingResult            │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│ 5. Renderizado de Vistas                │
│    if ($processingResult) → Vista resultados
│    elseif ($uploadedFile) → Vista confirmación
│    else → Vista carga PDF               │
└─────────────────────────────────────────┘
```

---

## 4. Raíz del Problema

### Problema Identificado

**Los bloques `if` son independientes y NO mutuamente excluyentes.**

Después de procesar la imagen (bloque 2), el código **continúa ejecutando** los bloques 3, 4 y 5.

### Escenario: Usuario sube imagen en pantalla de resultados

1. **Estado inicial:** Usuario viene de procesar PDF
   - `$_SESSION['current_pdf_basename']` = "FichaDemo"
   - `$_SESSION['current_image_path']` = null (aún no subida)

2. **Usuario selecciona imagen + click "Subir imagen"**
   - `$_POST` = datos del formulario de imagen
   - `$_FILES['image']` = archivo de imagen
   - `$_FILES['pdf']` = NO EXISTE
   - `$_POST['filepath']` = NO EXISTE

3. **Ejecución del código:**

```php
// BLOQUE 2: SUBIDA DE IMAGEN ✅ SE EJECUTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    // Sube imagen correctamente
    $imageUploaded = true;  // ✅ Se establece a true
    $_SESSION['current_image_path'] = $rutaDestino;
    // ...
}

// BLOQUE 3: SUBIDA DE PDF ❌ NO SE EJECUTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    // $_FILES['pdf'] NO existe → salta este bloque
}

// BLOQUE 4: PROCESAMIENTO OPENAI ❌ NO SE EJECUTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filepath']) && !isset($_FILES['pdf'])) {
    // $_POST['filepath'] NO existe → salta este bloque
}

// BLOQUE 5: RENDERIZADO ❌ MUESTRA PANTALLA EQUIVOCADA
if ($processingResult) {
    // ❌ $processingResult es NULL → no muestra resultados
} elseif ($uploadedFile) {
    // ❌ $uploadedFile es NULL → no muestra confirmación
} else {
    // ✅ MUESTRA PANTALLA DE CARGA DE PDF (INCORRECTO)
    // Esta es la pantalla que ve el usuario
}
```

### Variable Crítica: `$processingResult`

**Problema:** Después de subir imagen, `$processingResult` sigue siendo `null`.

```php
// Línea 13: Inicialización
$processingResult = null;

// Línea 33-70: Subida de imagen NO modifica $processingResult
if (isset($_FILES['image'])) {
    // ... sube imagen ...
    // ❌ NO se asigna $processingResult
}

// Línea 345: Renderizado
if ($processingResult) {  // ❌ FALSE → no muestra resultados
    // Vista de resultados
} else {
    // ✅ MUESTRA PANTALLA DE CARGA DE PDF
}
```

---

## 5. Evidencia del Código

### Líneas Clave

**Línea 13:** Inicialización de variables
```php
$uploadedFile = null;
$error = null;
$processingResult = null;  // ← PROBLEMA: Siempre null tras subir imagen
```

**Líneas 33-70:** Subida de imagen
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // ... sube imagen ...
        $imageUploaded = true;  // ← Se actualiza
        $_SESSION['current_image_path'] = $imagePath;  // ← Se actualiza
        
        // ❌ PERO: $processingResult NO se actualiza
    }
}
```

**Líneas 345-350:** Renderizado de vista
```php
<?php if ($processingResult): ?>
    <!-- Vista de resultados (NO SE MUESTRA) -->
<?php elseif ($uploadedFile): ?>
    <!-- Vista de confirmación (NO SE MUESTRA) -->
<?php else: ?>
    <!-- Vista de carga de PDF (SE MUESTRA - INCORRECTO) -->
<?php endif; ?>
```

### Flujo de Variables

| Variable | Inicial | Tras subir imagen | ¿Debería cambiar? |
|----------|---------|-------------------|-------------------|
| `$imageUploaded` | false | ✅ true | ✅ Sí |
| `$processingResult` | null | ❌ null | ❌ Debería mantener valor anterior |
| `$uploadedFile` | null | ❌ null | ✅ Correcto (no es upload de PDF) |

---

## 6. Soluciones Propuestas

### Opción A: Redireccionar tras subir imagen (RECOMENDADA)

**Implementación:**
```php
// Líneas 33-70: Después de subir imagen exitosamente
if (move_uploaded_file($nombreTmp, $rutaDestino)) {
    $imageUploaded = true;
    $imagePath = $rutaDestino;
    $imageName = $nombreImagen;
    $_SESSION['current_image_path'] = $imagePath;
    $_SESSION['current_image_name'] = $imageName;
    
    // ✅ REDIRECCIONAR para recargar página y mantener estado
    header('Location: carga_pdf.php');
    exit;
}
```

**Ventajas:**
- ✅ Patrón POST-Redirect-GET (evita reenvío de formulario)
- ✅ Mantiene estado desde sesión
- ✅ URL limpia (sin parámetros POST)
- ✅ Simple de implementar

**Desventajas:**
- ⚠️ Requiere recarga de página

---

### Opción B: Cargar estado desde sesión antes de renderizar

**Implementación:**
```php
// Línea 13: Después de inicializar variables
$processingResult = null;

// NUEVO: Cargar resultado desde sesión si existe
if (isset($_SESSION['last_processing_result'])) {
    $processingResult = $_SESSION['last_processing_result'];
}

// Líneas 56-60: Después de subir imagen
if (move_uploaded_file($nombreTmp, $rutaDestino)) {
    // ... guardar imagen ...
    
    // ✅ NO redirigir, continuar en misma página
    // El código de renderizado usará $processingResult de sesión
}
```

**Ventajas:**
- ✅ Sin recarga de página
- ✅ Mantiene todo el estado

**Desventajas:**
- ⚠️ Más complejo
- ⚠️ Requiere guardar resultado en sesión previamente

---

### Opción C: Usar estructura if-elseif-else

**Implementación:**
```php
// Reestructurar TODA la lógica:
if (isset($_FILES['image'])) {
    // Procesar imagen
    // ...
} elseif (isset($_FILES['pdf'])) {
    // Procesar PDF
    // ...
} elseif (isset($_POST['filepath'])) {
    // Procesar con OpenAI
    // ...
}
```

**Ventajas:**
- ✅ Solo un bloque se ejecuta
- ✅ Más claro

**Desventajas:**
- ⚠️ Cambio grande de estructura
- ⚠️ Riesgo de romper funcionalidad existente

---

## 7. Recomendación

### Solución a Implementar: **Opción A (Redireccionar)**

**Razones:**

1. ✅ **Más simple** - Solo añadir 2 líneas después de subir imagen
2. ✅ **Patrón establecido** - POST-Redirect-GET es buena práctica
3. ✅ **Menos riesgo** - No cambia estructura del código
4. ✅ **Funciona con sesión** - El estado ya se guarda en sesión

### Código a Añadir

**Ubicación:** `src/php/carga_pdf.php`, línea ~60

**Código actual:**
```php
if (move_uploaded_file($nombreTmp, $rutaDestino)) {
    $imageUploaded = true;
    $imagePath = $rutaDestino;
    $imageName = $nombreImagen;
    $_SESSION['current_image_path'] = $imagePath;
    $_SESSION['current_image_name'] = $imageName;
} else {
    $error = "Error al guardar la imagen.";
}
```

**Código modificado:**
```php
if (move_uploaded_file($nombreTmp, $rutaDestino)) {
    $imageUploaded = true;
    $imagePath = $rutaDestino;
    $imageName = $nombreImagen;
    $_SESSION['current_image_path'] = $imagePath;
    $_SESSION['current_image_name'] = $imageName;
    
    // ✅ AÑADIR: Redireccionar para mantener estado
    header('Location: carga_pdf.php');
    exit;
} else {
    $error = "Error al guardar la imagen.";
}
```

---

## 8. Resumen Ejecutivo

### Problema

Al subir imagen, página redirige a carga de PDF porque:
1. `$processingResult` sigue siendo `null` después de subir imagen
2. El código de renderizado muestra pantalla de carga de PDF cuando `$processingResult` es null
3. No hay redirección después de procesar imagen

### Solución

Añadir redirección (`header('Location: carga_pdf.php')`) después de subir imagen exitosamente para:
1. Recargar página
2. Leer estado desde sesión
3. Mostrar pantalla de resultados correctamente

### Impacto

- **Archivos a modificar:** 1 (`src/php/carga_pdf.php`)
- **Líneas a añadir:** 2
- **Riesgo:** Bajo
- **Tiempo estimado:** 5 minutos

---

**Diagnóstico completado:** 2026-03-29  
**Autor:** Agente de Desarrollo  
**Estado:** Pendiente de implementación
