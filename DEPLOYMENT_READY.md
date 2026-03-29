# 🚀 EnDES - Listo para Despliegue en Railway

## ✅ Configuración Completada

Este documento resume el estado actual del proyecto para despliegue en Railway.

---

## 📁 Archivos de Configuración Creados

| Archivo | Descripción |
|---------|-------------|
| `config/config.php` | ✅ API Key de OpenAI configurada |
| `.gitignore` | ✅ Archivos sensibles excluidos de git |
| `.env.example` | ✅ Plantilla de variables de entorno |
| `railway.json` | ✅ Configuración de Railway |
| `nixpacks.toml` | ✅ Configuración de build PHP |
| `generate-password.php` | ✅ Utilidad para generar hashes |
| `DEPLOYMENT_RAILWAY.md` | ✅ Guía completa de despliegue |

---

## 🔑 Credenciales de Acceso

### Usuario por Defecto

| Campo | Valor |
|-------|-------|
| **Usuario** | `admin` |
| **Contraseña** | `admin123` |
| **Hash** | `$2y$10$fJfIuvIY35xcuvbCgaCMWe1mZg./gDeTRqCNiU8QFTdt/1Cd5v2sW` |

### API Key de OpenAI

✅ Configurada en archivo `.env` (no commitear a git)

---

## ⚠️ Importante: Archivo .env

El archivo `.env` contiene la API Key de OpenAI y **NO debe subirse a GitHub**.

- ✅ `.env` está excluido en `.gitignore`
- ✅ En Railway, configurar como variable de entorno `OPENAI_API_KEY`

---

## 📋 Pasos Pendientes para Despliegue

### 1. Verificar Archivos Sensibles Excluidos

```bash
# Verificar que .env está excluido
git check-ignore .env  # Debe mostrar ".env"

# Verificar estado de git
git status
```

### 2. Commit y Push a GitHub (excluyendo .env)

```bash
# Añadir todos los archivos (git ignorará .env automáticamente)
git add .

# Verificar qué se va a commitear
git status

# Commit
git commit -m "Preparar despliegue en Railway"

# Push a GitHub
git push origin main
```

### 3. Conectar a Railway

1. Ir a https://railway.app
2. Click "New Project"
3. "Deploy from GitHub repo"
4. Seleccionar repositorio `cfle-3`

### 4. Configurar Variable de Entorno en Railway

La API Key está en `.env` localmente, pero en Railway debe configurarse:

1. En Railway Dashboard → Variables
2. Añadir variable:
   - **Name**: `OPENAI_API_KEY`
   - **Value**: `<tu-api-key-de-openai>` (copiar desde el archivo `.env` local)
3. Click "Add"

### 5. Verificar Despliegue

- Railway detectará PHP automáticamente
- El build se ejecutará con `nixpacks.toml`
- La aplicación estará disponible en el dominio generado

### 6. Probar Funcionalidad

1. Navegar a `https://tu-app.railway.app`
2. Login: `admin` / `admin123`
3. Subir un PDF de prueba
4. Ejecutar análisis
5. Verificar resultados

---

## 📊 Configuración Técnica

### PHP Version
- PHP 8.2 (configurado en `nixpacks.toml`)

### Extensiones Requeridas
- ✅ cURL
- ✅ session
- ✅ json
- ✅ mbstring

### Puertos
- Puerto dinámico (variable `$PORT` de Railway)

### Healthcheck
- Path: `/src/php/carga_pdf.php`
- Timeout: 30 segundos

---

## 🗂️ Estructura del Proyecto

```
cfle-3/
├── config/
│   ├── config.php           ✅ API Key configurada
│   └── prompts.php          ✅ Prompts de extracción
├── src/
│   ├── OpenAIClient.php     ✅ Cliente OpenAI
│   ├── Service/
│   │   └── AuthService.php  ✅ Autenticación
│   └── php/
│       ├── carga_pdf.php    ✅ Script principal
│       ├── login.php        ✅ Login
│       ├── logout.php       ✅ Logout
│       ├── layout_*.php     ✅ Layouts
│       └── visualizador_cfle.php ✅ Visualizador
├── assets/
│   ├── app.js               ✅ JavaScript
│   └── style.css            ✅ Estilos
├── .gitignore               ✅ Configurado
├── .env.example             ✅ Plantilla
├── railway.json             ✅ Configuración Railway
├── nixpacks.toml            ✅ Configuración build
├── generate-password.php    ✅ Utilidad
├── DEPLOYMENT_RAILWAY.md    ✅ Guía despliegue
└── index.php                ✅ Entry point
```

---

## ⚠️ Consideraciones de Seguridad

1. **API Key en .env**: La API Key está en el archivo `.env`
   - ✅ `.env` está excluido de git por `.gitignore`
   - ✅ No commitear `.env` bajo ninguna circunstancia
   - ⚠️ En Railway: configurar como variable de entorno

2. **Contraseña por defecto**: `admin123`
   - ⚠️ Recomendado: Cambiar después del primer despliegue
   - Usar: `php generate-password.php nueva-password`

---

## 💰 Costos Estimados en Railway

| Recurso | Costo Mensual |
|---------|---------------|
| Compute (512MB, 0.25 vCPU) | $5-10 |
| Disk (opcional, 1GB) | $1-2 |
| **Total estimado** | **$6-12/mes** |

---

## 📞 Próximos Pasos

1. ✅ Configuración completada
2. ⏳ Hacer push a GitHub
3. ⏳ Conectar a Railway
4. ⏳ Verificar funcionamiento
5. ⏳ (Opcional) Configurar volumen persistente

---

**Estado**: ✅ LISTO PARA DESPLIEGUE  
**Fecha**: 2026-03-29  
**Versión**: 1.0
