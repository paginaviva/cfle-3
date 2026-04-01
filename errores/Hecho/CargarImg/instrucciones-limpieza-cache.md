# Instrucciones para Usuario - Limpieza de Caché

**Fecha:** 2026-03-29  
**Problema:** Botón aparece disabled aunque el código está correcto

---

## El Código Está Correcto

Verificación realizada:
- ✅ Servidor FTP tiene el código SIN `disabled`
- ✅ Línea 393: `<button ... cursor: pointer;>` (correcto)

---

## Posible Causa: Caché del Navegador

El navegador puede estar mostrando una versión **CACHADA** de la página.

---

## Solución: Forzar Recarga

### Opción 1: Hard Reload (Recomendado)

**Windows/Linux:**
```
Ctrl + Shift + R
```
o
```
Ctrl + F5
```

**Mac:**
```
Cmd + Shift + R
```

### Opción 2: Limpiar Caché Manualmente

**Chrome/Edge:**
1. `F12` (abrir DevTools)
2. Click derecho en botón de recargar
3. Seleccionar "Vaciar caché y recargar forzosamente"

**Firefox:**
1. `Ctrl + Shift + Supr`
2. Seleccionar "Caché"
3. Click en "Limpiar ahora"

### Opción 3: Navegación Incógnito

1. Abrir ventana de incógnito (`Ctrl + Shift + N`)
2. Ir a: https://wa.cofemlevante.com/src/php/carga_pdf.php
3. Login y probar

---

## Verificación

Después de limpiar caché, deberías ver:

**ANTES (incorrecto):**
```
Botón gris, cursor: not-allowed, disabled
```

**DESPUÉS (correcto):**
```
Botón verde (#10b981), cursor: pointer, clickable
```

---

## Si el Problema Persiste

1. **Inspeccionar elemento** (click derecho → Inspeccionar)
2. Buscar el botón "Alta producto en Web"
3. Verificar en el HTML si tiene atributo `disabled`
4. Tomar captura y enviar

---

**Estado:** Código en servidor ✅ CORRECTO  
**Posible problema:** Caché del navegador ⚠️
