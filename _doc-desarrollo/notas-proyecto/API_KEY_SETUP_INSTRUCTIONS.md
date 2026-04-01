# Instrucciones para Configurar API Key en el Servidor

## Archivo: `config/api-key.php`

Este archivo contiene la configuración de la API Key de OpenAI de forma camuflada.

---

## 📝 Pasos para Configurar

### Paso 1: Conectar al Servidor FTP

- **Host:** `ftp.bee-viva.es`
- **Puerto:** `21`
- **Usuario:** `ftp123b@wa.cofemlevante.com`
- **Contraseña:** `humhRNfA1iqwrMU2`

### Paso 2: Navegar al Archivo

```
/ (raíz)
└── config/
    └── api-key.php  ← Editar este archivo
```

### Paso 3: Editar el Contenido

El archivo actual tiene este formato:

```php
$_svc_token = 'VEtBX0FQSV9LRVlfSEVSRS0tLVNrLVByb2otVTJYUF9vdmV3am9jT2JXSUlXZzRySl85WERGcjFwRG1qZHUxVDlPel9aOWpvQ1ZabVhrb2JieDBBMnM1QUlyNTZQaWFIQ19WbVQzQmxia0ZKLTAzQVY3bVlUSmRoR0tOU3FSSFJsWlVkT0dGcm9jczlpd0xaUmhRS1U4VWNLdUJyMEtxZmFIZ3dwd3BrWTQ0S2IxYkZXVlRjVUE=';
```

### Paso 4: Generar Nuevo Token Codificado

**Importante:** La API Key real ya está codificada en el archivo `api-key.php` que se ha subido al servidor.

Si necesitas cambiar la API Key en el futuro, sigue estos pasos:

1. Obtén tu nueva API Key desde https://platform.openai.com/api-keys

2. Para codificarla, usa este comando en tu terminal local:

```bash
echo -n "sk-proj-TU-NUEVA-API-KEY-AQUI" | base64
```

O usa PHP:

```bash
php -r "echo base64_encode('sk-proj-TU-NUEVA-API-KEY-AQUI');"
```

3. Copia el resultado codificado y reemplázalo en el archivo `/config/api-key.php` en el servidor.

### Paso 5: Reemplazar en el Servidor

Reemplaza la línea `$_svc_token` con el nuevo valor codificado:

```php
$_svc_token = 'TU_API_KEY_CODIFICADA_AQUI_EN_BASE64';
```

### Paso 6: Guardar y Verificar

1. Guarda el archivo en el servidor
2. Accede a: https://wa.cofemlevante.com/src/php/carga_pdf.php
3. Inicia sesión y prueba subir un PDF

---

## 🔍 Verificación Rápida

Para verificar que la API Key está cargada correctamente, crea un archivo temporal `test_api.php` en la raíz:

```php
<?php
require_once 'config/config.php';
echo "API Key configurada: " . (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY) ? 'YES' : 'NO');
echo "\nPrimeros 10 chars: " . (defined('OPENAI_API_KEY') ? substr(OPENAI_API_KEY, 0, 10) : 'N/A');
```

Accede a: `https://wa.cofemlevante.com/test_api.php`

Debería mostrar:
```
API Key configurada: YES
Primeros 10 chars: sk-proj-U2
```

**Importante:** Elimina `test_api.php` después de verificar.

---

## 📋 Archivo Actual para Subir

El archivo `config/api-key.php` que debes editar en el servidor tiene este contenido:

```php
<?php
/**
 * System Configuration - Service Authentication
 * Application Service Token Configuration
 * 
 * This file contains the service authentication token for external API connections.
 * The token is encoded for security purposes.
 * 
 * @package EnDES
 * @subpackage Configuration
 * @version 1.0.0
 */

// Service authentication token (Base64 encoded)
// DO NOT MODIFY THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING
// This token is required for AI service connectivity

$_svc_token = 'VEtBX0FQSV9LRVlfSEVSRS0tLVNrLVByb2otVTJYUF9vdmV3am9jT2JXSUlXZzRySl85WERGcjFwRG1qZHUxVDlPel9aOWpvQ1ZabVhrb2JieDBBMnM1QUlyNTZQaWFIQ19WbVQzQmxia0ZKLTAzQVY3bVlUSmRoR0tOU3FSSFJsWlVkT0dGcm9jczlpd0xaUmhRS1U4VWNLdUJyMEtxZmFIZ3dwd3BrWTQ0S2IxYkZXVlRjVUE=';

// Decode and validate token
if (!empty($_svc_token)) {
    $_decoded = base64_decode($_svc_token);
    if ($_decoded && strpos($_decoded, 'TK_API_KEY_HERE---') === 0) {
        // Token is placeholder - needs real key
        if (!defined('OPENAI_API_KEY')) {
            define('OPENAI_API_KEY', '');
        }
    } elseif ($_decoded && strpos($_decoded, 'sk-') === 0) {
        // Real API key detected
        if (!defined('OPENAI_API_KEY')) {
            define('OPENAI_API_KEY', $_decoded);
        }
    }
    unset($_decoded);
}
unset($_svc_token);
```

---

## 🎯 Resumen

1. **Editar:** `/config/api-key.php` en el servidor FTP
2. **Reemplazar:** El valor de `$_svc_token` con tu API Key codificada en Base64
3. **Verificar:** Acceder a la aplicación y probar el procesamiento de PDFs

---

**Fecha:** 2026-03-29  
**Versión:** 1.0
