#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# SGI - Script de Empaquetado y Preparación de Release para Producción
# ==============================================================================

COLOR_RESET="\033[0m"
COLOR_GREEN="\033[1;32m"
COLOR_YELLOW="\033[1;33m"
COLOR_CYAN="\033[1;36m"
COLOR_RED="\033[1;31m"

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo -e "${COLOR_CYAN}====================================================${COLOR_RESET}"
echo -e "${COLOR_CYAN}  SGI: Empaquetando sistema con Docker para Producción ${COLOR_RESET}"
echo -e "${COLOR_CYAN}====================================================${COLOR_RESET}"

# 1. Comprobar que Docker esté disponible
if ! command -v docker >/dev/null 2>&1; then
    echo -e "${COLOR_RED}Error: Docker no está instalado o no se encuentra en el PATH.${COLOR_RESET}"
    exit 1
fi

IMAGE_TAG="sgi-app:production"
BUILD_DATE="$(date +'%Y%m%d_%H%M%S')"
TAR_NAME="sgi-release-${BUILD_DATE}.tar.gz"

# 2. Compilar imagen de Docker multi-stage
echo -e "\n${COLOR_YELLOW}[1/4] Compilando imagen de Docker multi-stage (${IMAGE_TAG})...${COLOR_RESET}"
docker build -t "$IMAGE_TAG" -t "sgi-app:latest" -f Dockerfile .

echo -e "${COLOR_GREEN}✓ Imagen de Docker compilada exitosamente.${COLOR_RESET}"

# 3. Extraer assets compilados (public/build) para sincronización directa o fallback
echo -e "\n${COLOR_YELLOW}[2/4] Extrayendo assets compilados desde el contenedor...${COLOR_RESET}"
EXTRACT_CONTAINER="sgi_extract_${BUILD_DATE}"
docker create --name "$EXTRACT_CONTAINER" "$IMAGE_TAG"

mkdir -p "$PROJECT_ROOT/public/build"
docker cp "$EXTRACT_CONTAINER:/var/www/html/public/build/." "$PROJECT_ROOT/public/build/"
docker rm "$EXTRACT_CONTAINER" >/dev/null

echo -e "${COLOR_GREEN}✓ Assets de Vite (public/build) extraídos y sincronizados localmente.${COLOR_RESET}"

# 4. Crear tarball de release empaquetado para despliegue directo seguro
echo -e "\n${COLOR_YELLOW}[3/4] Generando archivo de release empaquetado (${TAR_NAME})...${COLOR_RESET}"
mkdir -p "$PROJECT_ROOT/dist"

# Excluir archivos innecesarios del tarball
tar -czf "$PROJECT_ROOT/dist/${TAR_NAME}" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.phpunit.result.cache' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='*.sql' \
    --exclude='scratch' \
    --exclude='.env' \
    -C "$PROJECT_ROOT" \
    app \
    bootstrap \
    config \
    database \
    public \
    resources \
    routes \
    word \
    artisan \
    composer.json \
    composer.lock \
    package.json \
    package-lock.json \
    docker \
    Dockerfile \
    docker-compose.yml \
    docker-compose.prod.yml

echo -e "${COLOR_GREEN}✓ Paquete creado en dist/${TAR_NAME} ($(du -h "$PROJECT_ROOT/dist/${TAR_NAME}" | cut -f1))${COLOR_RESET}"

# 5. Resumen
echo -e "\n${COLOR_CYAN}====================================================${COLOR_RESET}"
echo -e "${COLOR_GREEN}  ¡Empaquetado completado con éxito!${COLOR_RESET}"
echo -e "  - Imagen Docker: ${IMAGE_TAG}"
echo -e "  - Paquete Standalone: dist/${TAR_NAME}"
echo -e "  - Assets Vite: public/build (listo para producción)"
echo -e "${COLOR_CYAN}====================================================${COLOR_RESET}"
