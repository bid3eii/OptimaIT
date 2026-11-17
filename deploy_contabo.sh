#!/bin/bash
set -e

echo "============================================="
echo "   INSTALADOR AUTOMATICO OPTIMAIT (CONTABO)  "
echo "============================================="

# 1. Actualizar e instalar Docker si no esta presente
if ! command -v docker &> /dev/null; then
    echo "[1/4] Instalando Docker..."
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
fi

# 2. Configurar llaves criptograficas de OptimaIT
echo "[2/4] Configurando llaves de cifrado OptimaIT..."
mkdir -p /opt/rustdesk-data
echo "eYxYdWFvipZDixUcvCzfOuzM9X36GVz+reuXTcpUFJjAHzuXMM1FUPZq0DvdbjVtU/RWZi7OXSiJZTZCK0XSFQ==" > /opt/rustdesk-data/id_ed25519
echo "wB87lzDNRVD2atA73W41bVP0VmYuzl0oiWU2QitF0hU=" > /opt/rustdesk-data/id_ed25519.pub
chmod 600 /opt/rustdesk-data/id_ed25519
chmod 644 /opt/rustdesk-data/id_ed25519.pub

# 3. Configurar firewall ufw si existe
echo "[3/4] Abriendo puertos en firewall..."
if command -v ufw &> /dev/null; then
    ufw allow 22/tcp || true
    ufw allow 80/tcp || true
    ufw allow 443/tcp || true
    ufw allow 21115:21119/tcp || true
    ufw allow 21116/udp || true
fi

# 4. Desplegar servicios
echo "[4/4] Levantando contenedores de OptimaIT..."
if docker compose version &> /dev/null; then
    docker compose down || true
    docker compose up -d --build
else
    docker-compose down || true
    docker-compose up -d --build
fi

echo "============================================="
echo " ¡DESPLIEGUE EXITOSO EN CONTABO!             "
echo " Panel Web:  http://155.133.197.176/login.php"
echo " Servidor:   155.133.197.176                 "
echo "============================================="
