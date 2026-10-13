@echo off
title Agente Huella - Sistema Biometrico Casino
cd /d "%~dp0"
echo Iniciando agente de captura de huella...
echo Deja esta ventana abierta mientras usas el sistema.
echo.
"C:\Users\Jhon Zamora\AppData\Local\Programs\Python\Python311-32\python.exe" agente_huella.py
pause
