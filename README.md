# EnDES - Sistema de Extracción de Datos de PDFs

Sistema web para extraer datos estructurados de fichas técnicas en PDF utilizando OpenAI GPT-5.1 y la Responses API.

## 📋 Descripción

EnDES es una aplicación PHP que permite:
- Subir fichas técnicas en formato PDF
- Procesar el contenido con IA (GPT-5.1)
- Extraer datos de productos y tablas técnicas
- Visualizar resultados en formato JSON y tablas HTML
- Descargar resultados estructurados

---

## 🗂️ Estructura de Archivos

```
EnDES/
├── index.php                      # Punto de entrada (redirige a login)
├── assets/
│   ├── app.js                     # JavaScript principal
│   └── styles.css                 # Estilos globales
├── config/
│   ├── config.php                 # Configuración principal (API keys, usuarios)
│   └── prompts.php                # Definición de prompts y parámetros
├── docs/                          # Directorio de PDFs subidos (creado automáticamente)
├── logs/                          # Logs de procesamiento (creado automáticamente)
└── src/
    ├── OpenAIClient.php           # Cliente para OpenAI Responses API
    └── php/
        ├── carga_pdf.php          # Interfaz principal de carga y procesamiento
        ├── layout_footer.php      # Footer común
        ├── layout_header.php      # Header común
        ├── login.php              # Sistema de autenticación
        ├── logout.php             # Cierre de sesión
        └── visualizador_cfle.php  # Visualizador de tablas JSON
```

---

## 📄 Descripción de Archivos

| Archivo | Propósito | Dependencias |
|---------|-----------|--------------|
| **`index.php`** | Punto de entrada, redirige a login o carga_pdf | `config/config.php` |
| **`config/config.php`** | Configuración: API keys, rutas, usuarios | Ninguna |
| **`config/prompts.php`** | Definición de prompts IA y parámetros | Ninguna |
| **`src/OpenAIClient.php`** | Cliente HTTP para OpenAI Responses API | `config/config.php` |
| **`src/php/login.php`** | Autenticación de usuarios | `config/config.php`, `layout_header.php`, `layout_footer.php` |
| **`src/php/logout.php`** | Cierre de sesión | Ninguna |
| **`src/php/carga_pdf.php`** | Interfaz principal: upload, procesamiento, resultados | `config/config.php`, `config/prompts.php`, `OpenAIClient.php`, `layout_header.php`, `layout_footer.php` |
| **`src/php/visualizador_cfle.php`** | Visualiza JSON como tablas HTML | Ninguna (standalone) |
| **`src/php/layout_header.php`** | Header HTML común | `assets/styles.css` |
| **`src/php/layout_footer.php`** | Footer HTML común | `assets/app.js` |
| **`assets/app.js`** | JavaScript para interactividad | Ninguna |
| **`assets/styles.css`** | Estilos CSS globales | Ninguna |

---

## ⚙️ Configuración

### 1. Requisitos Previos

- PHP 7.4 o superior
- Extensión cURL habilitada
- Cuenta de OpenAI con acceso a GPT-5.1
- Servidor web (Apache/Nginx)

### 2. Configurar API de OpenAI

Editar `config/config.php`:

```php
// Línea 14: Reemplazar con tu API key real
if (!defined('OPENAI_API_KEY')) define('OPENAI_API_KEY', 'sk-tu-api-key-aqui');

// Línea 17: Modelo por defecto
if (!defined('OPENAI_MODEL_ID')) define('OPENAI_MODEL_ID', 'gpt-5.1');
```

### 3. Configurar Usuarios

Editar `config/config.php` (líneas 25-28):

```php
$users_config = [
    'admin' => '$2y$10$tu-hash-bcrypt-aqui',
    // Añadir más usuarios si es necesario
];
```

**Generar hash de contraseña**:
```php
echo password_hash('tu-contraseña', PASSWORD_BCRYPT);
```

### 4. Permisos de Directorios

Asegurar que el servidor web pueda escribir en:
```bash
chmod 755 docs/
chmod 755 logs/
```

---

## 🎨 Personalización Visual

### Favicon

**Ubicación**: `assets/favicon.ico` (crear si no existe)

**Referencia en**: `src/php/layout_header.php`
```html
<link rel="icon" type="image/x-icon" href="../../assets/favicon.ico">
```

### Logo de Login

**Ubicación**: `assets/logo.png` (crear si no existe)

**Referencia en**: `src/php/login.php`
```html
<img src="../../assets/logo.png" alt="Logo" style="max-width: 200px;">
```

### Estilos Globales

**Archivo**: `assets/styles.css`

Personalizar colores, fuentes, y diseño general.

---

## 🚀 Uso

### 1. Acceso al Sistema

1. Navegar a `https://tu-dominio.com/ed_cfle2_a/`
2. Iniciar sesión con usuario y contraseña
3. Serás redirigido a `src/php/carga_pdf.php`

### 2. Procesar un PDF

**Paso 1: Subir archivo**
- Seleccionar prompt: "Extracción de datos Cofem"
- Elegir archivo PDF
- Clic en "Subir Archivo"

**Paso 2: Configurar parámetros**
- Número de tablas (detectadas en el PDF)
- Modelo IA (gpt-5.1)
- Clic en "Ejecutar Análisis IA"

**Paso 3: Ver resultados**
- Vista previa del JSON
- Botón "📥 Descargar JSON Completo"
- Botón "👁️ Visualizar JSON" (abre visualizador)
- Botón "🔄 Procesar Otro Archivo"

### 3. Visualizador de Tablas

El visualizador (`visualizador_cfle.php`) convierte automáticamente:
- **Objetos de producto** → Tablas HTML con campos como filas
- **Tablas HTML** → Renderizado directo del HTML

**Formato JSON soportado**:
```json
{
  "Matriz": [
    {
      "nombre_del_producto": "Producto X",
      "codigo_referencia": "REF-001",
      "descripcion_del_producto": "..."
    },
    {
      "titulo_tabla": "Especificaciones",
      "html_tabla": "<table>...</table>"
    }
  ]
}
```

---

## 🔧 Arquitectura Técnica

### Flujo de Procesamiento

```
1. Usuario sube PDF
   ↓
2. PDF se guarda en docs/YYYYMMDD_HHMMSS/
   ↓
3. PDF se sube a OpenAI Files API (purpose: user_data)
   ↓
4. Se construye payload para Responses API:
   - model: gpt-5.1
   - instructions: prompt de extracción
   - input: [input_text, input_file]
   ↓
5. OpenAI procesa el PDF y devuelve JSON
   ↓
6. Sistema extrae, valida y normaliza JSON
   ↓
7. Se guarda resultado en .result.json
   ↓
8. Usuario visualiza/descarga resultados
```

### Métodos de OpenAI API Utilizados

**Activos**:
- `uploadFile()` - Sube PDF a `/v1/files`
- `callResponses()` - Procesa con `/v1/responses`

**Deprecados** (no usar):
- `createThread()` - Assistants API (obsoleto)
- `createRun()` - Assistants API (obsoleto)
- `getRun()` - Assistants API (obsoleto)
- `listMessages()` - Assistants API (obsoleto)
- `createAssistant()` - Assistants API (obsoleto)

---

## 📊 Formato de Salida

### Estructura JSON

```json
{
  "Matriz": [
    {
      "nombre_del_producto": "string",
      "codigo_referencia": "string",
      "clasificacion_sistema": "string",
      "categoria_producto": "string",
      "descripcion_del_producto": "string",
      "idiomas_detectados": "string",
      "lista_caracteristicas": "string"
    },
    {
      "titulo_tabla": "string",
      "html_tabla": "<table>...</table>"
    }
  ]
}
```

---

## 🔐 Seguridad

- ✅ Autenticación con contraseñas hasheadas (bcrypt)
- ✅ Sesiones PHP para control de acceso
- ✅ Validación de tipos de archivo (solo PDF)
- ✅ Escape de HTML en visualizador
- ⚠️ **Importante**: Mantener `config/config.php` fuera del control de versiones

---

## 📝 Logs

Los logs se guardan en `logs/process.log` con formato:
```
[YYYY-MM-DD HH:MM:SS] Mensaje de log
```

Incluyen:
- Inicio de proceso
- Subida de archivo
- Llamadas a OpenAI
- Extracción de JSON
- Errores y excepciones

---

## 🐛 Solución de Problemas

### Error: "La clave API de OpenAI no está configurada"
**Solución**: Editar `config/config.php` y configurar `OPENAI_API_KEY`

### Error 404 en visualizador
**Solución**: Verificar que `visualizador_cfle.php` esté en `src/php/`

### PDF no se procesa
**Solución**: 
1. Verificar permisos de `docs/`
2. Revisar `logs/process.log`
3. Verificar API key de OpenAI

### JSON vacío `{"Matriz": []}`
**Solución**: El PDF no tiene contenido extraíble o el prompt necesita ajustes

---

## 📞 Soporte

Para modificar prompts o parámetros, editar `config/prompts.php`

Para cambiar modelos disponibles, editar línea 17 de `config/prompts.php`:
```php
"opciones" => ["gpt-5.1", "gpt-5.1-mini"],
```

---

## 📜 Licencia

Proyecto interno - Todos los derechos reservados
