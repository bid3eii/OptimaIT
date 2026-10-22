@echo off
title Habilitar Puertos Firewall para OptimaIT
echo Habilitando puertos en el Firewall de Windows para OptimaIT (10.4.4.40)...
echo.
netsh advfirewall firewall add rule name="OptimaIT Server TCP" dir=in action=allow protocol=TCP localport=21115-21119
netsh advfirewall firewall add rule name="OptimaIT Server UDP" dir=in action=allow protocol=UDP localport=21116
echo.
echo Listo! Reglas de Firewall agregadas correctamente.
pause
