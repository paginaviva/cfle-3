# Propuesta de Actualización de Inventario de Recursos - Sprint 2

**Fecha:** 2026-03-29  
**Sprint:** Sprint 2: Validaciones y UX  
**Documento generado:** `_doc-desarrollo/notas-proyecto/propuesta-inventario-sprint2.md`

---

## Índice de Contenidos

1. [Objetivo del Documento](#1-objetivo-del-documento)
2. [Criterio para Detectar Cambios Inventariables](#2-criterio-para-detectar-cambios-inventariables)
3. [Archivos y Recursos Modificados](#3-archivos-y-recursos-modificados)
4. [Motivo de Cada Actualización](#4-motivo-de-cada-actualización)
5. [Evidencia de Cambios Realizados](#5-evidencia-de-cambios-realizados)
6. [Pendientes o No Verificables](#6-pendientes-o-no-verificables)

---

## 1. Objetivo del Documento

Este documento lista todas las modificaciones, incorporaciones y actualizaciones que deberían realizarse en `.governance/inventario_recursos.md` como consecuencia del trabajo realizado en el **Sprint 2: Validaciones y UX**, específicamente la implementación del **modo DEMO para pruebas sin gastar tokens de OpenAI**.

---

## 2. Criterio para Detectar Cambios Inventariables

Se consideran cambios inventariables aquellos que afectan a:

| Categoría | Criterio de Inclusión |
|-----------|----------------------|
| **Archivos de configuración** | Nuevas constantes, variables de entorno, parámetros configurables |
| **Scripts de aplicación** | Modificaciones funcionales significativas, nuevas características |
| **Servicios externos** | Integraciones con APIs, servicios de terceros |
| **Flujos de trabajo** | Nuevos procesos, cambios en flujos existentes |
| **Recursos de infraestructura** | Cambios en despliegue, configuración de servidor |
| **Documentación técnica** | Nuevos documentos, actualizaciones significativas |

**Exclusiones:**
- Cambios cosméticos (formato, comentarios)
- Correcciones de bugs menores sin impacto funcional
- Refactorización interna sin cambios externos visibles

---

## 3. Archivos y Recursos Modificados

### 3.1. Archivos Existentes - Actualizaciones Requeridas

| Archivo | Tipo | Cambio Requerido | Prioridad |
|---------|------|------------------|-----------|
| `config/config.php` | Configuración | Añadir constante `OPENAI_ENABLED` al inventario de configuración | Alta |
| `src/php/carga_pdf.php` | Script de aplicación | Actualizar descripción del flujo de procesamiento | Media |

### 3.2. Nuevos Recursos a Inventariar

| Recurso | Tipo | Descripción | Ubicación |
|---------|------|-------------|-----------|
| **Modo DEMO OpenAI** | Característica funcional | Permite ejecutar la aplicación sin llamar a OpenAI, usando datos mock para ahorrar tokens | `config/config.php` + `src/php/carga_pdf.php` |
| **JSON Mock de Pruebas** | Datos de prueba | Estructura JSON simulada para modo demo | `src/php/carga_pdf.php` (inline) |
| **Banner de Modo DEMO** | Componente UI | Indicador visual amarillo que avisa al usuario que está en modo demo | `src/php/carga_pdf.php` |

---

## 4. Motivo de Cada Actualización

### 4.1. `config/config.php` - Constante `OPENAI_ENABLED`

**Motivo:** Nueva constante de configuración que controla el comportamiento de la aplicación.

**Descripción para inventario:**
```markdown
| OPENAI_ENABLED | boolean | true | Controla si la aplicación llama a OpenAI (true) o usa datos mock (false). Útil para pruebas sin gastar tokens. |
```

**Impacto:**
- Producción: `OPENAI_ENABLED = true` → Llamada real a OpenAI
- Desarrollo/Pruebas: `OPENAI_ENABLED = false` → Sin coste de tokens

---

### 4.2. `src/php/carga_pdf.php` - Flujo de Procesamiento Condicional

**Motivo:** El flujo de procesamiento ahora tiene dos modos de operación.

**Actualización requerida en inventario:**
- Añadir descripción del modo DEMO
- Documentar comportamiento condicional
- Registrar JSON mock como recurso de pruebas

---

### 4.3. Servicio Externo: OpenAI API

**Motivo:** Ahora el uso del servicio es opcional/configurable.

**Actualización requerida:**
- Añadir nota de que el servicio puede estar deshabilitado en modo demo
- Documentar que el modo demo no consume cuota de OpenAI

---

## 5. Evidencia de Cambios Realizados

### 5.1. Commits de Git

| Commit | Hash | Descripción |
|--------|------|-------------|
| Añadir modo DEMO | `ca831f1` | Implementación completa de OPENAI_ENABLED |

### 5.2. Cambios en Archivos

**`config/config.php`:**
```php
// Modo Demo/Pruebas
// true = Ejecutar OpenAI normalmente (producción)
// false = Saltar OpenAI, mostrar resultados mock (ahorro de tokens/dinero)
if (!defined('OPENAI_ENABLED')) define('OPENAI_ENABLED', true);
```

**`src/php/carga_pdf.php`:**
- Líneas ~147-160: Verificación de `OPENAI_ENABLED`
- Líneas ~160-295: Bloque `if ($openaiEnabled)` → Llamada a OpenAI
- Líneas ~298-322: Bloque `else` → JSON mock
- Líneas ~347-351: Banner UI de modo demo

### 5.3. Despliegue

- ✅ Archivos subidos a FTP (wa.cofemlevante.com)
- ✅ Push a GitHub completado
- ✅ Disponible en producción

---

## 6. Pendientes o No Verificables

### 6.1. Pendientes de Validación

| Elemento | Estado | Acción Requerida |
|----------|--------|------------------|
| Pruebas de modo DEMO | ⏳ Pendiente | Verificar que `OPENAI_ENABLED = false` funciona correctamente |
| Pruebas de modo producción | ⏳ Pendiente | Verificar que `OPENAI_ENABLED = true` no rompe funcionalidad existente |
| Documentación de usuario | ⏳ Pendiente | Actualizar manual de usuario con modo demo |

### 6.2. No Verificables desde Repositorio

| Elemento | Razón |
|----------|-------|
| Ahorro real de tokens | No hay métricas de uso de OpenAI en el repositorio |
| Casos de uso específicos | No hay documentación de cuándo usar modo demo vs producción |
| Política de despliegue | No está documentado si modo demo debe estar disponible en producción |

---

## 7. Resumen de Actualizaciones Propuestas para Inventario

### Para añadir en `.governance/inventario_recursos.md`:

#### Sección: Configuración
```markdown
## Configuración de la Aplicación

| Variable | Tipo | Valor por Defecto | Descripción |
|----------|------|-------------------|-------------|
| OPENAI_ENABLED | boolean | true | Habilita/deshabilita llamadas a OpenAI API. false = modo demo con datos mock. |
```

#### Sección: Características Funcionales
```markdown
## Características Funcionales

| Característica | Descripción | Archivos Relacionados |
|----------------|-------------|----------------------|
| Modo DEMO OpenAI | Permite probar la aplicación sin gastar tokens de OpenAI | config/config.php, src/php/carga_pdf.php |
```

#### Sección: Servicios Externos
```markdown
## Servicios Externos

| Servicio | Estado | Configuración | Notas |
|----------|--------|---------------|-------|
| OpenAI API | Opcional | OPENAI_ENABLED | Puede deshabilitarse para modo demo sin coste |
```

---

**Documento generado:** 2026-03-29  
**Autor:** Agente de Desarrollo  
**Estado:** Pendiente de revisión e incorporación al inventario principal
