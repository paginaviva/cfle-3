#!/usr/bin/env python3
"""
Script de despliegue FTP para EnDES
Sube todos los archivos del proyecto al servidor FTP
"""

import os
import sys
from ftplib import FTP, error_perm

# Configuración FTP
FTP_HOST = os.getenv('FTP_HOST', 'ftp.bee-viva.es')
FTP_PORT = int(os.getenv('FTP_PORT', '21'))
FTP_USER = os.getenv('FTP_USER', 'ftp123b@wa.cofemlevante.com')
FTP_PASS = os.getenv('FTP_PASS', 'humhRNfA1iqwrMU2')

# Archivos y directorios a excluir del despliegue
EXCLUDE = {
    '.git',
    '.qwen',
    '__pycache__',
    '.pytest_cache',
    '.mypy_cache',
    '.coverage',
    '.DS_Store',
    'Thumbs.db',
    '*.pyc',
    '.env'  # Solo .env se excluye, .env.example se incluye
}

def should_exclude(path, name):
    """Verifica si un archivo/directorio debe excluirse"""
    # Excluir directorios
    if os.path.isdir(path):
        if name in EXCLUDE or name.startswith('.'):
            return True
    
    # Excluir archivos por patrón
    for pattern in EXCLUDE:
        if pattern.startswith('*') and name.endswith(pattern[1:]):
            return True
        if name == pattern:
            return True
    
    return False

def upload_directory(ftp, local_dir, remote_dir):
    """Sube un directorio recursivamente"""
    if not os.path.isdir(local_dir):
        return
    
    try:
        ftp.cwd(remote_dir)
    except error_perm:
        # El directorio remoto no existe, crearlo
        print(f"  Creando directorio: {remote_dir}")
        ftp.mkd(remote_dir)
        ftp.cwd(remote_dir)
    
    for item in os.listdir(local_dir):
        local_path = os.path.join(local_dir, item)
        
        if should_exclude(local_path, item):
            print(f"  Excluyendo: {item}")
            continue
        
        remote_path = f"{remote_dir}/{item}" if remote_dir != '/' else f"/{item}"
        
        if os.path.isdir(local_path):
            print(f"  Directorio: {item}")
            upload_directory(ftp, local_path, remote_path)
            ftp.cwd('..')
        else:
            print(f"  Subiendo: {item}")
            try:
                with open(local_path, 'rb') as f:
                    ftp.storbinary(f'STOR {item}', f)
            except Exception as e:
                print(f"    Error subiendo {item}: {e}")

def main():
    # Directorio del proyecto (padre del script)
    project_dir = os.path.dirname(os.path.abspath(__file__))
    
    print(f"=== Despliegue FTP para EnDES ===")
    print(f"Servidor: {FTP_HOST}:{FTP_PORT}")
    print(f"Usuario: {FTP_USER}")
    print(f"Directorio local: {project_dir}")
    print()
    
    # Conectar al servidor FTP
    print("Conectando al servidor FTP...")
    try:
        ftp = FTP()
        ftp.connect(FTP_HOST, FTP_PORT, timeout=60)
        
        # Intentar login normal primero
        try:
            ftp.login(FTP_USER, FTP_PASS)
            print("✓ Conectado exitosamente (FTP)")
        except error_perm as e:
            if '530' in str(e):
                print("FTP falló, intentando FTPS (explícito)...")
                from ftplib import FTP_TLS
                ftps = FTP_TLS()
                ftps.connect(FTP_HOST, FTP_PORT, timeout=60)
                ftps.auth()  # TLS explícito
                ftps.login(FTP_USER, FTP_PASS)
                ftps.prot_p()  # Protección de datos
                print("✓ Conectado exitosamente (FTPS)")
                ftp = ftps
            else:
                raise
        
        ftp.set_pasv(True)  # Modo pasivo para evitar problemas de firewall
        print()
        
        # Navegar al directorio raíz del FTP
        print("Navegando al directorio raíz...")
        ftp.cwd('/')
        print("✓ Directorio raíz: /")
        print()
        
        # Subir archivos
        print("Subiendo archivos del proyecto...")
        upload_directory(ftp, project_dir, '/')
        
        print()
        print("=== Despliegue completado ===")
        print(f"URL de acceso: https://wa.levantecofem.es/")
        
        ftp.quit()
        
    except Exception as e:
        print(f"ERROR: {e}")
        sys.exit(1)

if __name__ == '__main__':
    main()
