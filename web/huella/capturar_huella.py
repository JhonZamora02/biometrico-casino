"""
capturar_huella.py  FINAL
═══════════════════════════════════════════════════════════════════
Captura huella del U.are.U 4500 con SDK One Touch v1.x via COM.

Parámetros confirmados experimentalmente:
  - Serialize() devuelve 275124 bytes
  - Header propietario DigitalPersona: 4600 bytes
  - Píxeles raw escala de grises: offset 4600, tamaño 300x400

Ejecutar con:  py -3.11-32 capturar_huella.py
Dependencias:  py -3.11-32 -m pip install pywin32 Pillow
═══════════════════════════════════════════════════════════════════
"""
import sys, os, json, base64, io, time, threading

SDK_DIR = r"C:\Program Files (x86)\DigitalPersona\Bin"
if os.path.isdir(SDK_DIR):
    os.environ["PATH"] = SDK_DIR + os.pathsep + os.environ.get("PATH","")
    try: os.add_dll_directory(SDK_DIR)
    except: pass

try:
    from PIL import Image, ImageEnhance, ImageFilter
    PILLOW_OK = True
except ImportError:
    PILLOW_OK = False

def salida(d): print(json.dumps(d, ensure_ascii=False)); sys.exit(0)
def error(m):  salida({"ok": False, "error": m})

# Parámetros confirmados
IMG_OFFSET = 4600
IMG_ANCHO  = 300
IMG_ALTO   = 400
IMG_SIZE   = IMG_ANCHO * IMG_ALTO  # 120000
TIMEOUT    = 12


def main():
    try:
        import win32com.client, pythoncom
    except ImportError:
        error("pywin32 no instalado. Ejecuta: py -3.11-32 -m pip install pywin32 Pillow")

    # Obtener serial del primer lector
    pythoncom.CoInitialize()
    serial = None
    try:
        readers = win32com.client.Dispatch("DPFPDevX.DPFPReadersCollection")
        if readers.Count == 0:
            error("No se detectó ningún lector U.are.U conectado.")
        for idx in [0, 1]:
            try: serial = readers.Reader(idx).SerialNumber; break
            except: continue
    except Exception as e:
        error(f"Error enumerando lectores: {e}")
    finally:
        pythoncom.CoUninitialize()

    raw_ref = [None]; evento = threading.Event(); err_ref = [None]

    class CaptureEvents:
        def OnComplete(self, *args):
            raw_ref[0] = args[1] if len(args) >= 2 else (args[0] if args else None)
            evento.set()
        def OnSampleQuality(self, *args): pass
        def OnReaderConnect(self, *args):  pass
        def OnReaderDisconnect(self, *args): pass

    def hilo():
        pythoncom.CoInitialize()
        try:
            cap = win32com.client.DispatchWithEvents("DPFPDevX.DPFPCapture", CaptureEvents)
            if serial:
                try: cap.ReaderSerialNumber = serial
                except: pass
            try: cap.Priority = 2
            except: pass
            cap.StartCapture()
            t0 = time.time()
            while not evento.is_set() and time.time() - t0 < TIMEOUT:
                pythoncom.PumpWaitingMessages()
                time.sleep(0.05)
            try: cap.StopCapture()
            except: pass
        except Exception as e:
            err_ref[0] = str(e); evento.set()
        finally:
            pythoncom.CoUninitialize()

    t = threading.Thread(target=hilo, daemon=True)
    t.start(); t.join(timeout=TIMEOUT + 3)

    if err_ref[0]: error(err_ref[0])
    if raw_ref[0] is None:
        error(f"Tiempo agotado ({TIMEOUT}s). Coloca el dedo en el lector y vuelve a intentar.")

    # Extraer datos del sample
    pythoncom.CoInitialize()
    try:
        sample = win32com.client.Dispatch(raw_ref[0])
        blob   = bytes(sample.Serialize())
    except Exception as e:
        error(f"Error serializando muestra: {e}")
    finally:
        pythoncom.CoUninitialize()

    # Extraer píxeles desde el offset confirmado
    if len(blob) < IMG_OFFSET + IMG_SIZE:
        error(f"Datos insuficientes: {len(blob)} bytes (se esperaban >{IMG_OFFSET+IMG_SIZE})")

    pixels = blob[IMG_OFFSET: IMG_OFFSET + IMG_SIZE]
    imagen_b64 = pixeles_a_jpeg_b64(pixels, IMG_ANCHO, IMG_ALTO)

    salida({
        "ok"        : True,
        "imagen_b64": imagen_b64,
        "formato"   : "jpg" if PILLOW_OK else "png",
        "ancho"     : IMG_ANCHO,
        "alto"      : IMG_ALTO,
    })


def pixeles_a_jpeg_b64(pixels, ancho, alto):
    if PILLOW_OK:
        try:
            img = Image.frombytes("L", (ancho, alto), pixels)
            img = ImageEnhance.Contrast(img).enhance(1.5)
            img = ImageEnhance.Sharpness(img).enhance(1.3)
            img = img.filter(ImageFilter.UnsharpMask(radius=1, percent=120))
            buf = io.BytesIO()
            img.save(buf, format="JPEG", quality=92)
            return __import__('base64').b64encode(buf.getvalue()).decode()
        except: pass
    import zlib, struct, base64
    def chunk(t, d):
        c = t+d; return struct.pack(">I",len(d))+c+struct.pack(">I",zlib.crc32(c)&0xFFFFFFFF)
    filas = b"".join(b"\x00"+pixels[y*ancho:(y+1)*ancho] for y in range(alto))
    png = (b"\x89PNG\r\n\x1a\n"
           +chunk(b"IHDR",struct.pack(">IIBBBBB",ancho,alto,8,0,0,0,0))
           +chunk(b"IDAT",zlib.compress(filas,6))
           +chunk(b"IEND",b""))
    return base64.b64encode(png).decode()


if __name__ == "__main__":
    main()
