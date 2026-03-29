# Guía de Despliegue en Railway - EnDES

Esta guía te llevará paso a paso desde el repositorio hasta tener la aplicación funcionando en Railway.

---

## 📋 Prerrequisitos

- Cuenta de GitHub
- Cuenta de Railway (https://railway.app)
- API Key de OpenAI (ya configurada en `.env` para local, configurar en Railway como variable de entorno)

---

## 🚀 Pasos de Despliegue

### Paso 1: Preparar el Repositorio Local

```bash
# 1. Clona o actualiza tu repositorio
cd /path/to/cfle-3

# 2. Verifica los archivos de configuración creados
ls -la .env.example .gitignore railway.json nixpacks.toml

# 3. (Opcional) Genera un nuevo hash para tu contraseña de admin
php generate-password.php
# Copia el hash generado
```

### Paso 2: Verificar Archivo .env (Solo Local)

El archivo `.env` ya contiene la API Key configurada. **No commitear este archivo**.

```bash
# Verificar que .env existe y está excluido del versionado
cat .env
git check-ignore .env  # Debe mostrar ".env" si está ignorado
```

### Paso 3: Hacer Commit y Push a GitHub

```bash
# Añade los archivos (excluyendo los ignorados por .gitignore)
git add .

# Haz commit
git commit -m "Preparar despliegue en Railway"

# Push a GitHub
git push origin main
# o tu rama principal
```

### Paso 4: Conectar a Railway

1. **Inicia sesión en Railway**: https://railway.app
2. **Crea un nuevo proyecto**: Click en "New Project"
3. **Selecciona "Deploy from GitHub repo"**
4. **Elige tu repositorio** `cfle-3`
5. **Railway detectará automáticamente PHP** y aplicará la configuración de `nixpacks.toml`

### Paso 5: Configurar Variable de Entorno en Railway

La API Key está en `.env` localmente, pero en Railway debe configurarse como variable de entorno:

1. Click en tu proyecto → **Variables**
2. Añade la variable:
   - **Name**: `OPENAI_API_KEY`
   - **Value**: `sk-proj-...` (tu API Key)
3. Click en **Add**

### Paso 6: (Opcional) Configurar Volumen Persistente

Si deseas persistencia para los PDFs subidos:

1. En Railway Dashboard → **New** → **Volume**
2. Configura:
   - **Mount Path**: `/app/docs`
   - **Size**: 1GB (o más según necesidad)
3. Repite para `/app/logs` si deseas persistencia de logs

### Paso 7: Desplegar

1. Railway iniciará el build automáticamente
2. Monitorea los logs en **Deployments** → **View Logs**
3. Cuando el estado sea **Running**, la aplicación está activa

### Paso 8: Verificar el Despliegue

1. Click en **Generate Domain** (o usa el dominio proporcionado)
2. Navega a `https://tu-app.railway.app`
3. Deberías ver la página de login

### Paso 9: Pruebas Funcionales

1. **Login**: Usa el usuario `admin` y la contraseña que hashasheaste
2. **Subir PDF**: Selecciona un PDF de ficha técnica
3. **Procesar**: Ejecuta el análisis con OpenAI
4. **Descargar**: Verifica que puedes descargar el resultado JSON

---

## 🔧 Troubleshooting

### Error: "Error al subir archivo"

**Causa**: Permisos de escritura o límite de tamaño.

**Solución**:
1. Verifica que el volumen `/app/docs` esté montado (si usas volumen)
2. Railway tiene límite de 500MB por defecto para uploads
3. Revisa los logs para el error específico

### Error: Timeout en procesamiento

**Causa**: El PDF es grande o OpenAI tarda más del timeout.

**Solución**:
1. Aumenta el timeout en `nixpacks.toml`
2. Usa PDFs más pequeños
3. Verifica que tu cuenta de OpenAI tenga créditos disponibles

### La aplicación no arranca

**Causa**: Problema de configuración de PHP o puertos.

**Solución**:
1. Revisa los logs de Railway → Deployments → View Logs
2. Verifica que `nixpacks.toml` esté en la raíz del repositorio
3. Asegúrate de que el puerto usa la variable `$PORT` de Railway

---

## 📊 Monitoreo y Logs

### Ver Logs en Tiempo Real

```bash
# Desde Railway CLI (si está instalado)
railway logs
```

### Acceder a Logs de Aplicación

Los logs de procesamiento se guardan en:
- `/app/logs/process.log` (si usas volumen persistente)
- O en el directorio del PDF subido: `/app/docs/NOMBRE_PDF/process.log`

---

## 🔐 Seguridad Post-Despliegue

### Checklist de Seguridad

- [x] ✅ API Key en archivo `.env` (excluido de git)
- [ ] Contraseña de admin cambiada del valor por defecto (`admin123`)
- [x] ✅ `.env` añadido a `.gitignore`
- [ ] Variable `OPENAI_API_KEY` configurada en Railway
- [ ] HTTPS habilitado (Railway lo proporciona automáticamente)

### Mejoras Recomendadas

1. **Rate Limiting**: Implementar límite de peticiones por usuario
2. **Validación de PDFs**: Verificar tipo MIME real, no solo extensión
3. **Limpieza de archivos**: Script para eliminar PDFs antiguos periódicamente
4. **Backup de configs**: Mantener backup seguro de `config/config.php`

---

## 💰 Costos Estimados en Railway

| Recurso | Uso Estimado | Costo Mensual |
|---------|--------------|---------------|
| **Compute** | 512MB RAM, 0.25 vCPU | $5-10 |
| **Disk (opcional)** | 1GB | $1-2 |
| **Dominio railway.app** | Incluido | $0 |
| **Total estimado** | | **$6-12/mes** |

**Nota**: Railway ofrece $5 de crédito mensual gratuito para nuevos usuarios.

---

## 📞 Soporte

Si encuentras problemas:

1. Revisa los logs de Railway
2. Verifica la documentación en `README.md` y `OPENAI_INTEGRATION.md`
3. Consulta el análisis completo en `temp/railway-deployment-analysis.md`

---

**Última actualización**: 2026-03-29  
**Versión de la guía**: 1.0
