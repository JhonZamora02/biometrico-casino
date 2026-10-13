"""
instalar_agente.py
Registra el agente para inicio automático con Windows.
Ejecutar UNA VEZ:  py -3.11-32 instalar_agente.py
"""
import os, sys, winreg

PYTHON_EXE = sys.executable
AGENTE     = os.path.join(os.path.dirname(os.path.abspath(__file__)), "agente_huella.py")
CMD        = f'"{PYTHON_EXE}" "{AGENTE}"'
KEY_NAME   = "AgenteHuellaCasino"

try:
    key = winreg.OpenKey(
        winreg.HKEY_CURRENT_USER,
        r"SOFTWARE\Microsoft\Windows\CurrentVersion\Run",
        0, winreg.KEY_SET_VALUE
    )
    winreg.SetValueEx(key, KEY_NAME, 0, winreg.REG_SZ, CMD)
    winreg.CloseKey(key)
    print(f"[OK] Agente registrado para inicio automático")
    print(f"     Comando: {CMD}")
    print()
    print("[..] Iniciando agente ahora...")
    import subprocess
    subprocess.Popen([PYTHON_EXE, AGENTE], creationflags=subprocess.CREATE_NEW_CONSOLE)
    print("[OK] Agente iniciado. Busca la ventana 'Agente Huella'")
    print()
    print("Desde ahora el agente se inicia automáticamente con Windows.")
except Exception as e:
    print(f"[!!] Error: {e}")
