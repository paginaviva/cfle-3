# 🚀 EnDES - Despliegue Completado en FTP

## ✅ Estado del Despliegue

**Fecha:** 2026-03-29  
**Método:** FTP  
**URL de Acceso:** https://wa.cofemlevante.com/

---

## 📁 Archivos Desplegados

El siguiente contenido fue subido al servidor FTP:

```
/ (raíz del subdominio wa.cofemlevante.com)
├── index.php
├── assets/
│   ├── app.js
│   └── style.css
├── config/
│   ├── config.php
│   └── prompts.php
├── src/
│   ├── OpenAIClient.php
│   ├── Service/
│   │   └── AuthService.php
│   └── php/
│       ├── carga_pdf.php
│       ├── login.php
│       ├── logout.php
│       ├── layout_header.php
│       ├── layout_footer.php
│       └── visualizador_cfle.php
├── includes/
│   └── header.php
├── .gitignore
├── .env.example
├── docs/ (creado para uploads)
└── logs/ (creado para logs)
```

---

## 🔐 Credenciales de Acceso

### Usuario por Defecto

| Campo | Valor |
|-------|-------|
| **Usuario** | `admin` |
| **Contraseña** | `admin123` |

⚠️ **IMPORTANTE:** Cambiar la contraseña después del primer acceso.

---

## 📋 Configuración FTP

| Parámetro | Valor |
|-----------|-------|
| **Host** | `ftp.bee-viva.es` |
| **Puerto** | `21` |
| **Usuario** | `ftp123b@wa.cofemlevante.com` |
| **Contraseña** | `humhRNfA1iqwrMU2` |
| **Directorio Destino** | `/` (raíz del subdominio) |

---

## 🔧 Configuración de la Aplicación

### OpenAI API Key

La API Key está configurada en el archivo `.env` local (NO subido al servidor).

**Para producción**, debes:
1. Crear un archivo `.env` en el servidor con la API Key, O
2. Modificar `config/config.php` para incluir la API Key directamente

### Estructura del archivo `.env` en producción:

```
OPENAI_API_KEY=sk-proj-tu-api-key-aqui
FTP_HOST=ftp.bee-viva.es
FTP_PORT=21
FTP_USER=ftp123b@wa.cofemlevante.com
FTP_PASS=humhRNfA1iqwrMU2
```

---

## 🧪 Pruebas de Funcionamiento

### 1. Acceso al Login
- URL: https://wa.cofemlevante.com/src/php/login.php
- Usuario: `admin`
- Contraseña: `admin123`

### 2. Subida de PDF
1. Iniciar sesión
2. Seleccionar prompt "Extracción de datos Cofem"
3. Elegir archivo PDF
4. Click en "Subir Archivo"

### 3. Procesamiento
1. Confirmar parámetros (número de tablas, modelo IA)
2. Click en "Ejecutar Análisis IA"
3. Esperar procesamiento

### 4. Resultados
- Vista previa del JSON
- Botón "📥 Descargar JSON Completo"
- Botón "👁️ Visualizar JSON"

---

## 📝 Scripts de Despliegue

### deploy_ftp.py

Script Python para desplegar automáticamente:

```bash
python3 deploy_ftp.py
```

**Archivos excluidos del despliegue:**
- `.env` (contiene credenciales sensibles)
- `.git/` (control de versiones)
- `temp/` (archivos temporales)
- `__pycache__/` (caché de Python)

---

## ⚠️ Consideraciones de Seguridad

1. **Archivo .env NO está en el servidor**
   - Las credenciales de OpenAI deben configurarse manualmente
   - Editar `config/config.php` o crear `.env` en el servidor

2. **Contraseña por defecto**
   - `admin123` es la contraseña actual
   - Cambiar inmediatamente después del primer acceso

3. **Permisos de directorio**
   - `docs/` debe tener permisos de escritura (755 o 775)
   - `logs/` debe tener permisos de escritura (755 o 775)

---

## 🔍 URLs de Acceso

| Página | URL |
|--------|-----|
| **Login** | https://wa.cofemlevante.com/src/php/login.php |
| **Carga de PDF** | https://wa.cofemlevante.com/src/php/carga_pdf.php |
| **Visualizador** | https://wa.cofemlevante.com/src/php/visualizador_cfle.php |
| **Logout** | https://wa.cofemlevante.com/src/php/logout.php |

---

## 🛠️ Próximos Pasos

1. [ ] **Configurar API Key en el servidor**
   - Crear archivo `.env` en la raíz del subdominio
   - O modificar `config/config.php` con la API Key

2. [ ] **Cambiar contraseña de admin**
   - Generar nuevo hash: `php generate-password.php nueva-password`
   - Actualizar en `config/config.php`

3. [ ] **Verificar permisos**
   - Asegurar que `docs/` y `logs/` tengan permisos de escritura

4. [ ] **Probar flujo completo**
   - Login → Subida de PDF → Procesamiento → Descarga

---

## 📞 Soporte

Para problemas de despliegue:
1. Verificar logs en `logs/process.log`
2. Comprobar permisos de archivos
3. Verificar configuración de OpenAI en `config/config.php`

---

**Despliegue realizado:** 2026-03-29  
**Versión:** 1.0  
**Estado:** ✅ Completado
