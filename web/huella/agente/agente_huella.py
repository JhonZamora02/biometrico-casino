"""
agente_huella.py - Agente local TCP para captura de huella
Escucha en 127.0.0.1:7842
Ejecutar con: py -3.11-32 agente_huella.py
"""
import sys, os, json, base64, io, time, threading, socket, struct

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

HOST     = "127.0.0.1"
PORT     = 7842
TIMEOUT  = 20

# Parámetros confirmados experimentalmente
IMG_OFFSET = 4600
IMG_ANCHO  = 300
IMG_ALTO   = 400
IMG_SIZE   = IMG_ANCHO * IMG_ALTO  # 120000

print(f"[Agente Huella] Iniciando en {HOST}:{PORT} ...")

def manejar_cliente(conn, addr):
    try:
        data = conn.recv(64).decode().strip()
        if data == "CAPTURAR":
            print("[Agente] Solicitud de captura recibida")
            resultado = capturar()
            respuesta = json.dumps(resultado, ensure_ascii=False) + "\n"
            # Enviar en chunks para imágenes grandes
            encoded = respuesta.encode()
            total = len(encoded)
            sent  = 0
            while sent < total:
                n = conn.send(encoded[sent:sent+4096])
                if n == 0: break
                sent += n
        elif data == "PING":
            conn.sendall(b'{"ok":true,"msg":"pong"}\n')
        else:
            conn.sendall(b'{"ok":false,"error":"Comando desconocido"}\n')
    except Exception as e:
        try: conn.sendall((json.dumps({"ok":False,"error":str(e)})+"\n").encode())
        except: pass
    finally:
        conn.close()

def iniciar_servidor():
    srv = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    srv.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    srv.bind((HOST, PORT))
    srv.listen(5)
    print(f"[Agente Huella] Escuchando en {HOST}:{PORT}")
    print(f"[Agente Huella] Listo para capturar huellas desde el sistema web")
    while True:
        try:
            conn, addr = srv.accept()
            t = threading.Thread(target=manejar_cliente, args=(conn, addr), daemon=True)
            t.start()
        except Exception as e:
            print(f"[Agente] Error servidor: {e}")

def capturar():
    try:
        import win32com.client, pythoncom
    except ImportError:
        return {"ok":False,"error":"pywin32 no instalado."}

    pythoncom.CoInitialize()
    serial = None
    try:
        readers = win32com.client.Dispatch("DPFPDevX.DPFPReadersCollection")
        if readers.Count == 0:
            return {"ok":False,"error":"No se detectó ningún lector U.are.U conectado."}
        for idx in [0,1]:
            try: serial = readers.Reader(idx).SerialNumber; break
            except: continue
    except Exception as e:
        return {"ok":False,"error":f"Error enumerando lectores: {e}"}
    finally:
        pythoncom.CoUninitialize()

    raw_ref=[None]; evento=threading.Event(); err_ref=[None]

    class CE:
        def OnComplete(self,*a):
            raw_ref[0]=a[1] if len(a)>=2 else (a[0] if a else None)
            evento.set()
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
            while not evento.is_set() and time.time()-t0<TIMEOUT:
                pythoncom.PumpWaitingMessages(); time.sleep(0.05)
            try: cap.StopCapture()
            except: pass
        except Exception as e:
            err_ref[0]=str(e); evento.set()
        finally:
            pythoncom.CoUninitialize()

    t=threading.Thread(target=hilo, daemon=True); t.start(); t.join(timeout=TIMEOUT+3)

    if err_ref[0]: return {"ok":False,"error":err_ref[0]}
    if not raw_ref[0]: return {"ok":False,"error":f"Tiempo agotado ({TIMEOUT}s). Coloca el dedo y vuelve a intentar."}

    pythoncom.CoInitialize()
    try:
        sample = win32com.client.Dispatch(raw_ref[0])
        blob   = bytes(sample.Serialize())
    except Exception as e:
        return {"ok":False,"error":f"Error serializando: {e}"}
    finally:
        pythoncom.CoUninitialize()

    if len(blob) < IMG_OFFSET + IMG_SIZE:
        return {"ok":False,"error":f"Datos insuficientes ({len(blob)} bytes)."}

    # Extraer píxeles con el offset confirmado
    pixels = blob[IMG_OFFSET : IMG_OFFSET + IMG_SIZE]

    # Verificar calidad: contar píxeles en rango válido (no ruido puro)
    validos = sum(1 for b in pixels[:2000] if 30 < b < 230)
    print(f"[Agente] Calidad: {validos}/2000 píxeles válidos")

    if validos < 800:
        # Buscar offset correcto escaneando el blob
        mejor_off, mejor_val = IMG_OFFSET, validos
        for off in range(4000, min(6000, len(blob)-IMG_SIZE), 50):
            px = blob[off:off+2000]
            v  = sum(1 for b in px if 30 < b < 230)
            if v > mejor_val:
                mejor_val = v; mejor_off = off
        if mejor_val > validos:
            print(f"[Agente] Ajustando offset: {IMG_OFFSET} → {mejor_off} (calidad {mejor_val}/2000)")
            pixels = blob[mejor_off : mejor_off + IMG_SIZE]

    b64 = pixeles_a_jpeg_b64(pixels, IMG_ANCHO, IMG_ALTO)
    print(f"[Agente] Captura exitosa ({len(pixels)} bytes → JPEG b64={len(b64)} chars)")
    return {"ok":True,"imagen_b64":b64,"formato":"jpg","ancho":IMG_ANCHO,"alto":IMG_ALTO}

def pixeles_a_jpeg_b64(pixels, ancho, alto):
    esp = ancho * alto
    pixels = (pixels + bytes(esp))[:esp]
    if PILLOW_OK:
        try:
            img = Image.frombytes("L", (ancho, alto), pixels)
            img = ImageEnhance.Contrast(img).enhance(1.5)
            img = ImageEnhance.Sharpness(img).enhance(1.3)
            img = img.filter(ImageFilter.UnsharpMask(radius=1, percent=120))
            buf = io.BytesIO()
            img.save(buf, format="JPEG", quality=92)
            return base64.b64encode(buf.getvalue()).decode()
        except: pass
    import zlib
    def chunk(t,d):
        c=t+d; return struct.pack(">I",len(d))+c+struct.pack(">I",zlib.crc32(c)&0xFFFFFFFF)
    filas = b"".join(b"\x00"+pixels[y*ancho:(y+1)*ancho] for y in range(alto))
    png = (b"\x89PNG\r\n\x1a\n"
           +chunk(b"IHDR",struct.pack(">IIBBBBB",ancho,alto,8,0,0,0,0))
           +chunk(b"IDAT",zlib.compress(filas,6))+chunk(b"IEND",b""))
    return base64.b64encode(png).decode()

if __name__ == "__main__":
    iniciar_servidor()
