<section class="about_section layout_padding">
  <div class="container">
    <div class="row justify-content-center">

      <div class="col-md-5 d-flex align-items-center justify-content-center">
        <div class="img-box">
          <img src="images/imagen-index.jpg" alt="Casino" style="border-radius:12px; max-width:100%;">
        </div>
      </div>

      <div class="col-md-6">
        <div class="detail-box">
          <h2 class="custom_heading">
            Registro de <span>Asistencia</span>
          </h2>

          <!-- Reloj en tiempo real -->
          <p class="text-muted mb-3">
            📅 <?php echo date("d/m/Y"); ?> &nbsp;
            🕐 <span id="horaReloj" style="font-weight:600;"></span>
          </p>

          <p>Si vas a asistir al almuerzo de hoy, ingresa tu cédula:</p>

          <div id="toast-container"></div>

          <form id="frmAsistencia">
            <div class="form-group">
              <input type="text" id="cedula" name="cedula"
                     class="form-control" placeholder="Número de cédula" required
                     style="font-size:1.1rem; padding:10px;">
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">
              ✅ Registrar Asistencia
            </button>
          </form>

          <p class="text-muted mt-3" style="font-size:12px;">
            // Recuerde que la plataforma se cierra a las 10:00 AM //
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
/* ── Reloj en tiempo real ───────────────────────────── */
function actualizarHora() {
    const ahora    = new Date();
    let h = ahora.getHours(), m = ahora.getMinutes(), s = ahora.getSeconds();
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    m = m < 10 ? '0'+m : m;
    s = s < 10 ? '0'+s : s;
    document.getElementById('horaReloj').textContent = `${h}:${m}:${s} ${ampm}`;
}
actualizarHora();
setInterval(actualizarHora, 1000);

/* ── Toast de notificación ─────────────────────────── */
function showToast(mensaje, tipo) {
    const toast = document.createElement('div');
    toast.className = 'alert alert-' + (tipo === 'success' ? 'success' : 'danger');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;min-width:320px;z-index:9999;'
                        + 'box-shadow:0 4px 12px rgba(0,0,0,.2);border-radius:10px;font-weight:600;';
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .6s'; }, 4000);
    setTimeout(() => toast.remove(), 4700);
}

/* ── Submit AJAX ───────────────────────────────────── */
document.getElementById('frmAsistencia').addEventListener('submit', function(e) {
    e.preventDefault();
    const cedula = document.getElementById('cedula').value.trim();
    if (!cedula) return;

    $.ajax({
        url  : 'ajax.php?module=Asistencia&controller=Asistencia&function=registrar',
        type : 'POST',
        data : { cedula: cedula },
        dataType: 'json',
        success: function(rs) {
            showToast(rs.mensaje, rs.tipo);
            document.getElementById('cedula').value = '';
        },
        error: function() {
            showToast('❌ Error de conexión. Intente de nuevo.', 'error');
        }
    });
});
</script>
