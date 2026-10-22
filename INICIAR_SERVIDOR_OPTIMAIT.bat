@echo off
title Servidor OptimaIT (IP: 10.4.4.40)
color 0B
echo ========================================================
echo         INICIANDO SERVIDOR LOCAL DE OPTIMAIT
echo ========================================================
echo  IP Servidor Relay/ID: 10.4.4.40
echo  Puertos usados: TCP 21115-21119, UDP 21116
echo ========================================================
echo.

cd /d "%~dp0server\x86_64"

:: Cerrar instancias previas si las hubiera
taskkill /f /im hbbs.exe >nul 2>&1
taskkill /f /im hbbr.exe >nul 2>&1

echo [1/2] Iniciando hbbr (Servidor de Relay)...
start "OptimaIT Relay Server (hbbr)" /b hbbr.exe -k _

echo [2/2] Iniciando hbbs (Servidor ID / Rendezvous)...
start "OptimaIT ID Server (hbbs)" /b hbbs.exe -r 10.4.4.40 -k _

echo.
echo ========================================================
echo  SERVIDOR OPTIMAIT ACTIVO Y ESCUCHANDO EN 10.4.4.40
echo ========================================================
echo.
echo Los clientes pueden descargarse desde tu red local en:
echo    http://10.4.4.40/NovaDesk/
echo.
echo Clave Publica (Key):
type id_ed25519.pub
echo.
echo ========================================================
echo Presiona cualquier tecla para detener el servidor...
pause >nul

taskkill /f /im hbbs.exe >nul 2>&1
taskkill /f /im hbbr.exe >nul 2>&1
echo Servidor detenido.
pause
