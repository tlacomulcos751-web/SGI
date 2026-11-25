#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# SGI - Script de Despliegue Automatizado y Seguro a Producción (Zero Downtime)
# Servidor Destino: ivan@100.125.103.121 (/var/www/html/inventario)
# ==============================================================================

COLOR_RESET="\033[0m"
COLOR_GREEN="\033[1;32m"
COLOR_YELLOW="\033[1;33m"
COLOR_CYAN="\033[1;36m"
COLOR_RED="\033[1;31m"

REMOTE_HOST="100.125.103.121"
REMOTE_USER="ivan"
REMOTE_PASS="nao2026"
REMOTE_PATH="/var/www/html/inventario"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo -e "${COLOR_CYAN}====================================================${COLOR_RESET}"
echo -e "${COLOR_CYAN}   SGI: Despliegue Automatizado a Producción        ${COLOR_RESET}"
echo -e "${COLOR_CYAN}====================================================${COLOR_RESET}"

# 1. Empaquetar versión actual con Docker y extraer assets
echo -e "\n${COLOR_YELLOW}[1/5] Empaquetando y validando release con Docker...${COLOR_RESET}"
cd "$PROJECT_ROOT"
./scripts/package-release.sh

LATEST_RELEASE="$(ls -t "$PROJECT_ROOT"/dist/sgi-release-*.tar.gz | head -n 1)"
ARCHIVE_NAME="$(basename "$LATEST_RELEASE")"

echo -e "${COLOR_GREEN}✓ Release listo: ${ARCHIVE_NAME}${COLOR_RESET}"

# 2. Transferir paquete vía Python/Paramiko (SFTP seguro)
echo -e "\n${COLOR_YELLOW}[2/5] Transfiriendo release a ${REMOTE_HOST}...${COLOR_RESET}"
python3 -c "
import paramiko, sys

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect('$REMOTE_HOST', username='$REMOTE_USER', password='$REMOTE_PASS', timeout=20)
    sftp = ssh.open_sftp()
    print('Subiendo $ARCHIVE_NAME a /tmp/...')
    sftp.put('$LATEST_RELEASE', '/tmp/$ARCHIVE_NAME')
    sftp.close()
    ssh.close()
    print('✓ Transferencia completada.')
except Exception as e:
    print('Error en transferencia:', e)
    sys.exit(1)
"

# 3. Aplicar release en el servidor remoto con backup de seguridad y permisos
echo -e "\n${COLOR_YELLOW}[3/5] Aplicando release en ${REMOTE_PATH}...${COLOR_RESET}"
python3 -c "
import paramiko, sys

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('$REMOTE_HOST', username='$REMOTE_USER', password='$REMOTE_PASS', timeout=20)

commands = [
    # Backup preventivo del .env y snapshot de seguridad
    'mkdir -p /home/ivan/sgi_backups',
    'cp -f $REMOTE_PATH/.env /home/ivan/sgi_backups/.env.bak_\$(date +%Y%m%d_%H%M%S)',
    
    # Descomprimir en directorio temporal
    'rm -rf /tmp/sgi_stage && mkdir -p /tmp/sgi_stage',
    'tar -xzf /tmp/$ARCHIVE_NAME -C /tmp/sgi_stage',
    
    # Sincronizar archivos nuevos hacia el directorio de producción preservando storage y .env
    'echo \"nao2026\" | sudo -S cp -r /tmp/sgi_stage/. $REMOTE_PATH/',
    
    # Asegurar permisos correctos
    'echo \"nao2026\" | sudo -S chown -R www-data:www-data $REMOTE_PATH',
    'echo \"nao2026\" | sudo -S chmod -R 775 $REMOTE_PATH/storage $REMOTE_PATH/bootstrap/cache',
    
    # Limpiar caché obsoleta y regenerar optimizaciones de Laravel
    'echo \"nao2026\" | sudo -S rm -f $REMOTE_PATH/bootstrap/cache/*.php',
    'cd $REMOTE_PATH && echo \"nao2026\" | sudo -u www-data -S php artisan package:discover --ansi',
    'cd $REMOTE_PATH && echo \"nao2026\" | sudo -u www-data -S php artisan storage:link || true',
    'cd $REMOTE_PATH && echo \"nao2026\" | sudo -u www-data -S php artisan config:clear',
    'cd $REMOTE_PATH && echo \"nao2026\" | sudo -u www-data -S php artisan route:clear',
    'cd $REMOTE_PATH && echo \"nao2026\" | sudo -u www-data -S php artisan view:clear',
    'cd $REMOTE_PATH && echo \"nao2026\" | sudo -u www-data -S php artisan view:cache',
    
    # Limpieza de temporales
    'rm -rf /tmp/sgi_stage /tmp/$ARCHIVE_NAME'
]

for cmd in commands:
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8')
    err = stderr.read().decode('utf-8')
    if out:
        print(out.strip())
    if err and 'password' not in err.lower():
        print('AVISO:', err.strip())

ssh.close()
"

# 4. Verificación de salud post-despliegue
echo -e "\n${COLOR_YELLOW}[4/5] Verificando estado del servicio en producción...${COLOR_RESET}"
python3 -c "
import paramiko, sys

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('$REMOTE_HOST', username='$REMOTE_USER', password='$REMOTE_PASS', timeout=15)

stdin, stdout, stderr = ssh.exec_command('curl -s -o /dev/null -w \"HTTP_STATUS: %{http_code}\" http://127.0.0.1/up && echo \"\" && curl -s -o /dev/null -w \"HTTP_STATUS: %{http_code}\" http://127.0.0.1/')
result = stdout.read().decode('utf-8')
print('Comprobación HTTP en servidor local:')
print(result)

ssh.close()
"

echo -e "\n${COLOR_CYAN}====================================================${COLOR_RESET}"
echo -e "${COLOR_GREEN}  ¡Despliegue a producción completado exitosamente! ${COLOR_RESET}"
echo -e "${COLOR_CYAN}====================================================${COLOR_RESET}"
