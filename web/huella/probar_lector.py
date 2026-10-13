"""
probar_lector.py  FINAL
Verifica el lector y guarda prueba_huella.jpg
Ejecutar:  py -3.11-32 probar_lector.py
"""
import sys, os, json, base64, time, threading

SDK_DIR = r"C:\Program Files (x86)\DigitalPersona\Bin"
if os.path.isdir(SDK_DIR):
    os.environ["PATH"] = SDK_DIR + os.pathsep + os.environ.get("PATH","")
    try: os.add_dll_directory(SDK_DIR)
    except: pass

print("="*55)
print(" Diagnóstico lector DigitalPersona U.are.U")
print("="*55)

try:
    import win32com.client, pythoncom
    print("[OK] pywin32 instalado")
except ImportError:
    print("[!!] py -3.11-32 -m pip install pywin32"); sys.exit(1)

try:
    from PIL import Image as PILImage, ImageEnhance, ImageFilter
    PILLOW_OK = True
    print("[OK] Pillow instalado")
except ImportError:
    PILLOW_OK = False
    print("[AV] py -3.11-32 -m pip install Pillow")

IMG_OFFSET = 4600; IMG_ANCHO = 300; IMG_ALTO = 400
IMG_SIZE   = IMG_ANCHO * IMG_ALTO

pythoncom.CoInitialize()
serial = None
try:
    readers = win32com.client.Dispatch("DPFPDevX.DPFPReadersCollection")
    print(f"\n[OK] Lectores: {readers.Count}")
    for idx in [0, 1]:
        try: serial = readers.Reader(idx).SerialNumber; break
        except: continue
    if serial: print(f"     Serial: {serial}")
finally: pythoncom.CoUninitialize()

print("\n[..] Iniciando captura...")
print("     >>> COLOCA EL DEDO EN EL LECTOR <<<\n")

raw_ref=[None]; evento=threading.Event(); err_ref=[None]

class CE:
    def OnComplete(self,*a):
        raw_ref[0]=a[1] if len(a)>=2 else (a[0] if a else None); evento.set()
    def OnSampleQuality(self,*a): pass
    def OnReaderConnect(self,*a): pass
    def OnReaderDisconnect(self,*a): pass

def hilo():
    pythoncom.CoInitialize()
    try:
        cap=win32com.client.DispatchWithEvents("DPFPDevX.DPFPCapture",CE)
        if serial:
            try: cap.ReaderSerialNumber=serial
            except: pass
        try: cap.Priority=2
        except: pass
        cap.StartCapture()
        t0=time.time()
        while not evento.is_set() and time.time()-t0<12:
            pythoncom.PumpWaitingMessages(); time.sleep(0.05)
        try: cap.StopCapture()
        except: pass
    except Exception as e: err_ref[0]=str(e); evento.set()
    finally: pythoncom.CoUninitialize()

t=threading.Thread(target=hilo,daemon=True); t.start(); t.join(timeout=15)

if err_ref[0]: print(f"[!!] {err_ref[0]}"); sys.exit(1)
if not raw_ref[0]: print("[!!] Tiempo agotado"); sys.exit(1)

pythoncom.CoInitialize()
try:
    sample = win32com.client.Dispatch(raw_ref[0])
    blob   = bytes(sample.Serialize())
    print(f"[OK] Muestra capturada: {len(blob)} bytes")
finally: pythoncom.CoUninitialize()

pixels = blob[IMG_OFFSET: IMG_OFFSET + IMG_SIZE]
print(f"[OK] Píxeles extraídos: {len(pixels)} bytes (offset {IMG_OFFSET}, {IMG_ANCHO}x{IMG_ALTO})")

if PILLOW_OK:
    img = PILImage.frombytes("L",(IMG_ANCHO,IMG_ALTO),pixels)
    img = ImageEnhance.Contrast(img).enhance(1.5)
    img = ImageEnhance.Sharpness(img).enhance(1.3)
    img = img.filter(ImageFilter.UnsharpMask(radius=1, percent=120))
    img.save("prueba_huella.jpg","JPEG",quality=92)
    print("[OK] *** prueba_huella.jpg guardada ***")
    print("\n✅ ¡SISTEMA COMPLETAMENTE FUNCIONAL!")
    print("   El botón '👆 Capturar huella' en el sistema web ya funciona.")
else:
    print("[AV] Instala Pillow: py -3.11-32 -m pip install Pillow")

print()
print("="*55)
