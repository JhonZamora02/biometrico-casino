<?php date_default_timezone_set("America/Bogota"); ?>

<section class="layout_padding">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 text-center">

        <h2 class="custom_heading mb-2">
          Verificador de <span>Asistencia</span>
        </h2>
        <p class="text-muted mb-1" id="fechaHora" style="font-size:.95rem; font-weight:600;"></p>

        <!-- Panel de espera -->
        <div id="panelEspera">
          <p class="mt-3 mb-2">Coloca tu dedo en el huellero para verificar tu reserva de almuerzo.</p>
          <div id="huellaAnim"
               style="width:140px;height:140px;margin:20px auto;
                      background:url('images/huella.jpg') no-repeat center;
                      background-size:contain;
                      animation:pulse 1.2s infinite ease-in-out; opacity:.7;">
          </div>
          <button id="btnVerificar" class="btn btn-primary btn-lg px-5 mt-2"
                  onclick="iniciarVerificacion()">
            👆 Verificar huella
          </button>
          <p class="text-muted mt-3" style="font-size:.8rem;">
            Solo empleados con reserva registrada para hoy pueden verificar.
          </p>
        </div>

        <!-- Spinner -->
        <div id="panelCargando" style="display:none; margin-top:30px;">
          <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
          <p class="mt-3 text-primary font-weight-bold">Capturando huella...<br>
            <small class="text-muted">Coloca el dedo firmemente en el lector</small>
          </p>
        </div>

        <!-- Error -->
        <div id="panelError" style="display:none; margin-top:20px;">
          <div class="alert" id="alertError" role="alert" style="border-radius:14px; font-size:1rem;"></div>
          <button class="btn btn-outline-primary mt-2" onclick="reiniciar()">🔄 Intentar de nuevo</button>
        </div>

        <!-- Éxito -->
        <div id="panelExito" style="display:none; margin-top:20px;">
          <div class="alert alert-success" style="border-radius:14px; font-size:1.1rem;">
            <strong>✅ Huella verificada</strong><br>
            <span id="exitoNombre"></span>
          </div>
          <p class="text-muted">Redirigiendo...</p>
        </div>

      </div>
    </div>
  </div>
</section>

<style>
@keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }
</style>

<script>
function actualizarHora() {
    const f = new Date();
    document.getElementById('fechaHora').textContent =
        'Fecha: ' + f.toLocaleDateString('es-ES',{day:'2-digit',month:'2-digit',year:'numeric'}) +
        '  |  Hora: ' + f.toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true});
}
actualizarHora(); setInterval(actualizarHora, 1000);

function iniciarVerificacion() {
    document.getElementById('panelEspera').style.display   = 'none';
    document.getElementById('panelCargando').style.display = '';
    document.getElementById('panelError').style.display    = 'none';
    document.getElementById('panelExito').style.display    = 'none';

    $.ajax({
        url     : 'ajax.php?module=Verificado&controller=Verificado&function=verificarHuella',
        type    : 'POST',
        dataType: 'text',      // Texto plano para parsear manualmente
        timeout : 40000,
        success : function(rawText) {
            document.getElementById('panelCargando').style.display = 'none';

            // Buscar el JSON dentro del texto (puede haber warnings de PHP antes)
            var rs = null;
            var lineas = rawText.split('\n');
            for (var i = lineas.length - 1; i >= 0; i--) {
                var l = lineas[i].trim();
                if (l.charAt(0) === '{') {
                    try { rs = JSON.parse(l); break; } catch(e) {}
                }
            }

            if (!rs) {
                mostrarError({tipo:'conexion', error:'Respuesta inválida del servidor: ' + rawText.substring(0,200)});
                return;
            }

            if (rs.ok) {
                document.getElementById('exitoNombre').textContent =
                    rs.nombre + ' ' + rs.apellido + ' — reserva a las ' + rs.hora_reserva;
                document.getElementById('panelExito').style.display = '';
                sessionStorage.setItem('verificado', JSON.stringify(rs));
                setTimeout(function() {
                    window.location.href = 'index.php?module=Verificado&controller=Verificado&function=confirmacion';
                }, 1800);
            } else {
                mostrarError(rs);
            }
        },
        error: function(xhr, status, err) {
            document.getElementById('panelCargando').style.display = 'none';
            var msg = status === 'timeout'
                ? 'Tiempo agotado. Coloca el dedo al presionar el botón y espera.'
                : 'Error de red: ' + status + ' ' + err;
            // Intentar parsear la respuesta aunque haya error HTTP
            if (xhr.responseText) {
                var lineas = xhr.responseText.split('\n');
                for (var i = lineas.length-1; i>=0; i--) {
                    var l = lineas[i].trim();
                    if (l.charAt(0) === '{') {
                        try {
                            var rs = JSON.parse(l);
                            mostrarError(rs); return;
                        } catch(e) {}
                    }
                }
                msg += ' | Respuesta: ' + xhr.responseText.substring(0,200);
            }
            mostrarError({tipo:'conexion', error: msg});
        }
    });
}

function mostrarError(rs) {
    var iconos  = {domingo:'🚫', fuera_horario:'🕙', no_reconocido:'❌', sin_reserva:'📋', conexion:'⚠️'};
    var colores = {domingo:'alert-warning', fuera_horario:'alert-warning', no_reconocido:'alert-danger', sin_reserva:'alert-info', conexion:'alert-secondary'};
    var tipo  = rs.tipo || 'conexion';
    var el    = document.getElementById('alertError');
    el.className = 'alert ' + (colores[tipo] || 'alert-danger');
    el.innerHTML = '<strong>' + (iconos[tipo]||'❌') + ' ' + (rs.error||'Error desconocido') + '</strong>';
    document.getElementById('panelError').style.display = '';
}

function reiniciar() {
    document.getElementById('panelError').style.display  = 'none';
    document.getElementById('panelEspera').style.display = '';
}
</script>
