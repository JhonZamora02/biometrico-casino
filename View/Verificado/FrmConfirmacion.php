<?php date_default_timezone_set("America/Bogota"); ?>

<section class="layout_padding">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 text-center">

        <div id="fechaHora" class="text-muted mb-3" style="font-size:.95rem; font-weight:600;"></div>

        <!-- Panel de éxito (llenado por JS con datos de sessionStorage) -->
        <div id="panelExito" style="display:none;">
          <h2 class="custom_heading mb-4">
            Confirmación de <span>Almuerzo</span>
          </h2>
          <div class="card shadow-sm p-4 mb-4" style="border-radius:16px;">
            <p style="font-size:1.15rem; margin-bottom:8px;">
              El empleado con CC <strong id="confCedula"></strong>
            </p>
            <p style="font-size:1.05rem; color:#555; margin-bottom:4px;">
              <strong id="confNombre"></strong>
            </p>
            <p style="font-size:1rem; color:#777; margin-bottom:20px;">
              Reservó su asistencia al casino a las <strong id="confHora"></strong>
            </p>
            <p style="font-size:2.2rem; font-weight:bold; color:#27ae60;">
              🍽️ ¡DISFRUTE DE SU ALMUERZO!
            </p>
          </div>
          <a href="<?php echo getUrl('Asistencia','Asistencia','read'); ?>"
             class="btn btn-primary px-5">
            ← Volver al inicio
          </a>
        </div>

        <!-- Panel de acceso denegado -->
        <div id="panelError" style="display:none;">
          <h2 class="custom_heading mb-4">
            Acceso <span>Denegado</span>
          </h2>
          <div class="card shadow-sm p-4 mb-4" style="border-radius:16px; border-left:5px solid #dc3545;">
            <p style="font-size:1.3rem;" id="errorMsg"></p>
          </div>
          <a href="<?php echo getUrl('Verificado','Verificado','read'); ?>"
             class="btn btn-outline-primary px-5">
            ← Volver a verificar
          </a>
        </div>

        <!-- Sin datos: acceso directo sin pasar por verificación -->
        <div id="panelDirecto" style="display:none;">
          <p class="text-muted">Accede primero al verificador de huella.</p>
          <a href="<?php echo getUrl('Verificado','Verificado','read'); ?>"
             class="btn btn-primary px-5">
            Ir al verificador
          </a>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
function actualizarHora() {
    const f = new Date();
    document.getElementById('fechaHora').textContent =
        'Fecha: ' + f.toLocaleDateString('es-ES',{day:'2-digit',month:'2-digit',year:'numeric'}) +
        '  |  Hora: ' + f.toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true});
}
actualizarHora(); setInterval(actualizarHora, 1000);

// Leer datos de sessionStorage (guardados en FrmVerificado)
var datos = null;
try { datos = JSON.parse(sessionStorage.getItem('verificado')); } catch(e) {}
sessionStorage.removeItem('verificado');

if (datos && datos.ok) {
    document.getElementById('confCedula').textContent = datos.cedula;
    document.getElementById('confNombre').textContent = datos.nombre + ' ' + datos.apellido;
    document.getElementById('confHora').textContent   = datos.hora_reserva;
    document.getElementById('panelExito').style.display = '';
} else if (datos && !datos.ok) {
    document.getElementById('errorMsg').textContent = datos.error || 'Acceso no permitido.';
    document.getElementById('panelError').style.display = '';
} else {
    document.getElementById('panelDirecto').style.display = '';
}
</script>
