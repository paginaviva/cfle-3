# Análisis Completo del Proyecto EnDES

**Fecha de análisis:** 2026-04-01  
**Directorio del proyecto:** `/workspaces/cfle-3`  
**Tipo de aplicación:** Sistema web de extracción de datos de PDFs con IA

---

## Índice de Contenidos

1. [Visión General](#1-visión-general)
2. [Estructura del Proyecto](#2-estructura-del-proyecto)
3. [Arquitectura Técnica](#3-arquitectura-técnica)
4. [Configuración y Despliegue](#4-configuración-y-despliegue)
5. [Flujo de Procesamiento](#5-flujo-de-procesamiento)
6. [Integración con OpenAI](#6-integración-con-openai)
7. [Sistema de Autenticación](#7-sistema-de-autenticación)
8. [Interfaz de Usuario](#8-interfaz-de-usuario)
9. [Documentación Disponible](#9-documentación-disponible)
10. [Estado del Proyecto](#10-estado-del-proyecto)
11. [Recomendaciones](#11-recomendaciones)

---

## 1. Visión General

**EnDES** es un sistema web desarrollado en PHP para extraer datos estructurados de fichas técnicas en formato PDF utilizando la API de OpenAI (modelo GPT-5.1 y Responses API).

### Propósito Principal
- Subir fichas técnicas en formato PDF
- Procesar el contenido con Inteligencia Artificial
- Extraer datos de productos y tablas técnicas
- Visualizar resultados en formato JSON y tablas HTML
- Descargar resultados estructurados

### URL de Producción
- **Acceso:** https://wa.cofemlevante.com/
- **Login:** https://wa.cofemlevante.com/src/php/login.php

---

## 2. Estructura del Proyecto

```
cfle-3/
├── index.php                          # Punto de entrada (redirige a login/carga_pdf)
├── .env.example                       # Plantilla de variables de entorno
├── .gitignore                         # Archivos excluidos de git
├── deploy_ftp.py                      # Script de despliegue FTP
├── generate-password.php              # Utilidad para generar hashes bcrypt
├── test_openai_key.php                # Script para verificar API Key
├── nixpacks.toml                      # Configuración de build para Railway
├── railway.json                       # Configuración de despliegue Railway
│
├── assets/
│   ├── app.js                         # JavaScript principal (mínimo)
│   └── style.css                      # Estilos CSS globales
│
├── config/
│   ├── config.php                     # Configuración principal (rutas, usuarios, OpenAI)
│   ├── prompts.php                    # Definición de prompts y parámetros para IA
│   └── api-key.php                    # API Key de OpenAI codificada en Base64
│
├── src/
│   ├── OpenAIClient.php               # Cliente HTTP para OpenAI Responses API
│   ├── Service/
│   │   └── AuthService.php            # Servicio de autenticación de usuarios
│   └── php/
│       ├── carga_pdf.php              # Interfaz principal de carga y procesamiento
│       ├── login.php                  # Sistema de autenticación
│       ├── logout.php                 # Cierre de sesión
│       ├── layout_header.php          # Header HTML común
│       ├── layout_footer.php          # Footer HTML común
│       ├── visualizador_cfle.php      # Visualizador de tablas JSON
│       ├── subir_imagen.php           # Página para subir imagen asociada al PDF
│       └── form_f2_imagen.php         # Formulario F2 de subida de imagen
│
├── includes/
│   └── header.php                     # Header adicional (legacy)
│
├── _doc-desarrollo/
│   └── notas-proyecto/                # Documentación interna del desarrollo
│       ├── API_KEY_SETUP_INSTRUCTIONS.md
│       ├── DEPLOYMENT_FTP_COMPLETED.md
│       ├── DEPLOYMENT_RAILWAY.md
│       ├── DEPLOYMENT_READY.md
│       ├── OPENAI_INTEGRATION.md
│       ├── issue2.md
│       └── propuesta-inventario-sprint2.md
│
├── docs/                              # Directorio de PDFs subidos (creado automáticamente)
└── logs/                              # Logs de procesamiento (creado automáticamente)
```

---

## 3. Arquitectura Técnica

### Tecnologías Utilizadas

| Capa | Tecnología | Versión |
|------|------------|---------|
| **Backend** | PHP | 8.2+ |
| **Frontend** | HTML5, CSS3, JavaScript | - |
| **IA** | OpenAI Responses API | GPT-5.1 |
| **Visualización JSON** | JSON Editor | 9.10.0 |
| **Despliegue** | Railway / FTP | - |

### Extensiones PHP Requeridas
- cURL
- session
- json
- mbstring

### Constantes Principales

```php
// Rutas
BASE_PATH          // Directorio raíz del proyecto
DOCS_PATH          // Directorio de documentos subidos
LOGS_PATH          // Directorio de logs

// OpenAI
OPENAI_API_KEY     // Clave de API de OpenAI
OPENAI_MODEL_ID    // Modelo por defecto: 'gpt-5.1'
OPENAI_RESPONSES_URL  // https://api.openai.com/v1/responses
OPENAI_FILES_URL      // https://api.openai.com/v1/files
OPENAI_ENABLED     // true = producción, false = modo demo

// Aplicación
APP_TITLE          // 'Gestor de Fichas Tecnicas'
APP_VERSION        // '1.0.0'
```

---

## 4. Configuración y Despliegue

### Métodos de Despliegue Disponibles

#### 4.1. Despliegue FTP
- **Script:** `deploy_ftp.py`
- **Servidor:** `ftp.bee-viva.es:21`
- **Usuario:** `ftp123b@wa.cofemlevante.com`
- **Estado:** ✅ Completado

#### 4.2. Despliegue Railway
- **Archivos de configuración:**
  - `railway.json` - Configuración del deploy
  - `nixpacks.toml` - Configuración del build PHP
- **Variable de entorno requerida:** `OPENAI_API_KEY`
- **Healthcheck:** `/src/php/carga_pdf.php`
- **Estado:** ✅ Listo para desplegar

### Configuración de API Key

La API Key de OpenAI está configurada de forma segura en `config/api-key.php`:
- Token codificado en Base64
- Se decodifica automáticamente al cargar
- También soporta `.env` y variables de entorno

### Usuarios del Sistema

```php
$users_config = [
    'admin' => '$2y$10$fJfIuvIY35xcuvbCgaCMWe1mZg./gDeTRqCNiU8QFTdt/1Cd5v2sW', // admin123
];
```

**Utilidad para cambiar contraseña:**
```bash
php generate-password.php nueva-contraseña
```

---

## 5. Flujo de Procesamiento

### Paso a Paso del Procesamiento de PDF

```
1. Usuario inicia sesión
   ↓
2. Usuario sube PDF (carga_pdf.php)
   ↓
3. PDF se guarda en docs/NOMBRE_CARPETA/
   ↓
4. Usuario confirma parámetros (número de tablas, modelo IA)
   ↓
5. Sistema sube PDF a OpenAI Files API (/v1/files)
   ↓
6. Sistema construye payload para Responses API:
   - model: gpt-5.1
   - instructions: prompt de extracción (prompts.php)
   - input: [input_text, input_file con file_id]
   ↓
7. OpenAI procesa el PDF y devuelve JSON
   ↓
8. Sistema extrae, valida y normaliza JSON
   ↓
9. Se guarda resultado en .result.json
   ↓
10. Usuario visualiza/descarga resultados
   ↓
11. Usuario puede subir imagen asociada (subir_imagen.php)
```

### Modo DEMO

Existe un modo DEMO configurado mediante `OPENAI_ENABLED`:
- `true` (producción): Llama a OpenAI normalmente
- `false` (demo): Usa datos mock, no gasta tokens

---

## 6. Integración con OpenAI

### Endpoints Utilizados

| Endpoint | Propósito | Método |
|----------|-----------|--------|
| `/v1/files` | Subir PDF para procesamiento | POST |
| `/v1/responses` | Procesar con IA | POST |

### Modelo de IA
- **Modelo principal:** `gpt-5.1`
- **Lista blanca:** Solo `gpt-5.1` permitido
- **Forzado:** Si se envía otro modelo, se fuerza a `gpt-5.1`

### Estructura del Payload

```json
{
  "model": "gpt-5.1",
  "instructions": "<prompt completo de extracción>",
  "input": [
    {
      "role": "user",
      "content": [
        {
          "type": "input_text",
          "text": "Analiza el archivo PDF adjunto..."
        },
        {
          "type": "input_file",
          "file_id": "file-XXXXXXXXXXXX"
        }
      ]
    }
  ],
  "stream": false
}
```

### Formato de Salida Esperado

```json
{
  "Matriz": [
    {
      "nombre_del_producto": "Producto X",
      "codigo_referencia": "REF-001",
      "clasificacion_sistema": "SISTEMA ALGORÍTMICO",
      "categoria_producto": "Central algorítmica direccionable",
      "descripcion_del_producto": "...",
      "idiomas_detectados": "es",
      "lista_caracteristicas": "- Característica 1<br>- Característica 2"
    },
    {
      "titulo_tabla": "Especificaciones Técnicas",
      "html_tabla": "<table>...</table>"
    }
  ]
}
```

### Gestión de Errores

| Tipo de Error | Manejo |
|---------------|--------|
| Error de red | Excepción con mensaje de transporte |
| Error HTTP (4xx, 5xx) | Excepción con código y mensaje de OpenAI |
| JSON inválido | Excepción con json_last_error_msg() |
| Matriz vacía | Log registrado, se muestra al usuario |

---

## 7. Sistema de Autenticación

### AuthService (`src/Service/AuthService.php`)

Clase responsable de:
- Login con verificación bcrypt
- Gestión de sesiones PHP
- Protección de rutas

### Métodos Principales

```php
login($username, $password)      // Verifica credenciales
logout()                         // Destruye sesión
isAuthenticated()                // Verifica si hay sesión activa
getCurrentUser()                 // Obtiene usuario actual
requireLogin()                   // Redirige a login si no autenticado
```

### Flujo de Autenticación

```
1. Usuario accede a ruta protegida
   ↓
2. requireLogin() verifica sesión
   ↓
3. Si no hay sesión → redirige a login.php
   ↓
4. Usuario envía credenciales
   ↓
5. password_verify() valida hash bcrypt
   ↓
6. Si válido → $_SESSION['logged_in'] = true
   ↓
7. Redirige a carga_pdf.php
```

---

## 8. Interfaz de Usuario

### Páginas Principales

#### 8.1. Login (`src/php/login.php`)
- Formulario simple: usuario y contraseña
- Diseño centrado con CSS
- Mensajes de error en rojo

#### 8.2. Carga de PDF (`src/php/carga_pdf.php`)
- Selector de prompt (actualmente solo "Extracción de datos Cofem")
- Input file para PDF
- Parámetros dinámicos (número de tablas, modelo IA)
- Vista previa de resultados JSON
- Botones de descarga y visualización

#### 8.3. Subida de Imagen (`src/php/subir_imagen.php`)
- Formulario para subir imagen asociada al PDF
- La imagen se guarda con el mismo nombre que el PDF
- Botón "Alta producto en Web" (con modal de aviso)

#### 8.4. Visualizador (`src/php/visualizador_cfle.php`)
- JSON Editor interactivo (vista de árbol)
- Vista de tablas HTML renderizadas
- Soporte para múltiples productos y tablas

### Componentes UI

| Componente | Ubicación | Propósito |
|------------|-----------|-----------|
| layout_header.php | Header común | Logo, título, usuario, botones |
| layout_footer.php | Footer común | Cierre HTML, scripts |
| style.css | assets/ | Estilos globales |
| app.js | assets/ | JavaScript (mínimo) |

---

## 9. Documentación Disponible

### Documentación Técnica

| Archivo | Contenido |
|---------|-----------|
| `README.md` | Descripción general, estructura, configuración, uso |
| `OPENAI_INTEGRATION.md` | Detalle técnico de integración con OpenAI API |
| `DEPLOYMENT_RAILWAY.md` | Guía completa de despliegue en Railway |
| `DEPLOYMENT_READY.md` | Estado de preparación para despliegue |
| `DEPLOYMENT_FTP_COMPLETED.md` | Confirmación de despliegue FTP |
| `API_KEY_SETUP_INSTRUCTIONS.md` | Instrucciones para configurar API Key |

### Documentación de Desarrollo

| Archivo | Contenido |
|---------|-----------|
| `issue2.md` | Descripción de issue de subida de imagen |
| `propuesta-inventario-sprint2.md` | Propuesta de actualización de inventario |

### Scripts de Utilidad

| Script | Propósito |
|--------|-----------|
| `generate-password.php` | Generar hashes bcrypt para contraseñas |
| `test_openai_key.php` | Verificar si API Key es válida |
| `deploy_ftp.py` | Desplegar archivos vía FTP |

---

## 10. Estado del Proyecto

### Funcionalidades Implementadas

| Funcionalidad | Estado | Notas |
|---------------|--------|-------|
| Autenticación de usuarios | ✅ Completado | Bcrypt + sesiones |
| Subida de PDFs | ✅ Completado | Carpeta por PDF |
| Procesamiento con OpenAI | ✅ Completado | Responses API + GPT-5.1 |
| Extracción de productos | ✅ Completado | JSON estructurado |
| Extracción de tablas | ✅ Completado | HTML embebido |
| Visualizador JSON | ✅ Completado | JSON Editor + tablas |
| Descarga de resultados | ✅ Completado | JSON descargable |
| Subida de imagen asociada | ✅ Completado | Mismo nombre que PDF |
| Modo DEMO | ✅ Completado | Sin gasto de tokens |
| Despliegue FTP | ✅ Completado | wa.cofemlevante.com |
| Configuración Railway | ✅ Completado | Listo para deploy |

### Issues Conocidos

| Issue | Descripción | Estado |
|-------|-------------|--------|
| Issue #2 | Subida de imagen con redirección incorrecta | Documentado |

### URLs de Producción

| Página | URL |
|--------|-----|
| Login | https://wa.cofemlevante.com/src/php/login.php |
| Carga PDF | https://wa.cofemlevante.com/src/php/carga_pdf.php |
| Visualizador | https://wa.cofemlevante.com/src/php/visualizador_cfle.php |
| Logout | https://wa.cofemlevante.com/src/php/logout.php |

---

## 11. Recomendaciones

### Seguridad

1. **Cambiar contraseña por defecto**
   - Actual: `admin123`
   - Acción: `php generate-password.php nueva-password`

2. **Mantener `.env` excluido**
   - Verificar `.gitignore`
   - No commitear credenciales

3. **Rotar API Key periódicamente**
   - Actualizar `config/api-key.php` en servidor

4. **Validar tipo MIME de PDFs**
   - No confiar solo en extensión

### Mejoras Potenciales

1. **Rate Limiting**
   - Limitar peticiones por usuario/hora

2. **Limpieza de archivos**
   - Script para eliminar PDFs antiguos

3. **Backup de configuración**
   - Mantener backup seguro de configs

4. **Logs centralizados**
   - Sistema de logs más robusto

5. **Métricas de uso**
   - Tracking de tokens consumidos

6. **Soporte multi-usuario**
   - Roles y permisos

### Próximos Pasos Sugeridos

1. [ ] Verificar modo DEMO funciona correctamente
2. [ ] Probar flujo completo en producción
3. [ ] Documentar manual de usuario
4. [ ] Implementar mejoras de seguridad
5. [ ] Considerar migración a framework PHP

---

## Apéndice A: Prompt de Extracción

El prompt completo está definido en `config/prompts.php` e incluye:

### Proceso 1: Extracción de Productos
- nombre_del_producto
- codigo_referencia
- clasificacion_sistema
- categoria_producto
- descripcion_del_producto
- lista_caracteristicas
- idiomas_detectados

### Proceso 2: Extracción de Tablas
- titulo_tabla
- html_tabla (tabla HTML completa)

### Reglas Especiales
- Múltiples productos → múltiples objetos JSON
- Tablas con título en primera fila (colspan)
- JSON único con clave raíz "Matriz"

---

## Apéndice B: Comandos Útiles

```bash
# Generar hash de contraseña
php generate-password.php mi-nueva-password

# Verificar API Key
php test_openai_key.php

# Desplegar vía FTP
python3 deploy_ftp.py

# Verificar archivos excluidos
git check-ignore .env

# Ver estado de git
git status
```

---

**Documento generado:** 2026-04-01  
**Análisis completado por:** Agente de Desarrollo  
**Versión del análisis:** 1.0
