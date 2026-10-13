<section class="about_section layout_padding">
  <div class="container-fluid">
    <div class="row mb-3">
      <div class="col-12">
        <h2 class="custom_heading">
          Gestión de <span>Empleados</span>
          <button class="btn btn-primary ml-3"
                  data-toggle="modal" data-target="#modalEmpleado"
                  onclick="limpiarModal()">
            ➕ Registrar empleado
          </button>
        </h2>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <table class="table table-bordered table-hover" id="tbEmpleados">
          <thead class="thead-dark">
            <tr>
              <th>Cédula</th><th>Nombre</th><th>Apellido</th>
              <th>Correo</th><th>Área</th><th>Huella</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════
     MODAL PRINCIPAL: Registrar / Modificar Empleado
     ══════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEmpleado" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius:14px;overflow:hidden;">

      <div class="modal-header" style="background:linear-gradient(90deg,#0f3460,#16213e);">
        <h5 class="modal-title text-white" id="modalTitulo">Registrar Empleado</h5>
        <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body p-0">
        <div class="row no-gutters">

          <!-- Panel izquierdo: Huella -->
          <div class="col-md-4 p-4 text-center d-flex flex-column align-items-center"
               style="background:#f0f4f8; border-right:1px solid #dee2e6;">

            <p class="font-weight-bold mb-2" style="color:#0f3460;font-size:.9rem;">
              🔐 Huella dactilar
            </p>

            <!-- Preview: imagen capturada o placeholder -->
            <div id="huellaBox"
                 style="width:150px;height:150px;border-radius:12px;
                        border:2px dashed #adb5bd;background:#fff;
                        display:flex;align-items:center;justify-content:center;
                        overflow:hidden;margin-bottom:12px;position:relative;">
              <img id="huellaPreviewImg" src="images/huella.jpg"
                   style="width:90px;opacity:.3;transition:all .4s;"
                   alt="Huella">
              <div id="huellaPreviewTxt"
                   style="position:absolute;bottom:6px;width:100%;
                          text-align:center;font-size:.7rem;color:#999;">
                Sin capturar
              </div>
            </div>

            <!-- Spinner de espera -->
            <div id="huellaSpinner" style="display:none; margin-bottom:10px;">
              <div class="spinner-border text-primary" role="status"
                   style="width:2rem;height:2rem;"></div>
              <p class="text-primary mt-1 mb-0" style="font-size:.82rem;">
                Coloca el dedo en el lector...
              </p>
            </div>

            <!-- Mensaje de estado -->
            <div id="huellaEstado" style="font-size:.82rem;min-height:20px;
                                          margin-bottom:10px;"></div>

            <!-- Botones -->
            <button type="button" id="btnCapturar"
                    class="btn btn-primary btn-sm w-100 mb-2"
                    onclick="capturarHuella()">
              👆 Capturar huella
            </button>
            <button type="button" id="btnLimpiarH"
                    class="btn btn-outline-secondary btn-sm w-100"
                    onclick="limpiarHuella()" style="display:none;">
              🗑️ Quitar huella
            </button>

            <p class="text-muted mt-3" style="font-size:.72rem;line-height:1.4;">
              El lector U.are.U debe estar conectado.<br>
              Puedes registrar el empleado sin huella y agregarla después.
            </p>

            <!-- Imagen capturada en base64 (se envía al servidor) -->
            <input type="hidden" id="huellaB64" value="">
          </div>

          <!-- Panel derecho: Datos -->
          <div class="col-md-8 p-4">
            <form id="frmEmpleado">
              <input type="hidden" id="accionEmpleado" name="accionEmpleado" value="add">
              <div class="form-group">
                <label class="font-weight-bold">Cédula <span class="text-danger">*</span></label>
                <input type="text" id="cedula" name="cedula" class="form-control"
                       placeholder="Número de cédula" required>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label class="font-weight-bold">Nombre</label>
                  <input type="text" id="nombre" name="nombre" class="form-control"
                         placeholder="Nombre">
                </div>
                <div class="form-group col-md-6">
                  <label class="font-weight-bold">Apellido</label>
                  <input type="text" id="apellido" name="apellido" class="form-control"
                         placeholder="Apellido">
                </div>
              </div>
              <div class="form-group">
                <label class="font-weight-bold">Correo</label>
                <input type="email" id="correo" name="correo" class="form-control"
                       placeholder="correo@ejemplo.com">
              </div>
              <div class="form-group">
                <label class="font-weight-bold">Área</label>
                <input type="text" id="area" name="area" class="form-control"
                       placeholder="Área o departamento">
              </div>
              <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-secondary mr-2"
                        data-dismiss="modal">Cancelar</button>
                <button type="submit" id="btnGuardar" class="btn btn-success px-4">
                  💾 Guardar
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════
     MODAL SECUNDARIO: Actualizar huella existente
     ══════════════════════════════════════════════════ -->
<div class="modal fade" id="modalHuella" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="border-radius:14px;overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(90deg,#155724,#28a745);">
        <h5 class="modal-title text-white">Actualizar Huella</h5>
        <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body text-center p-4">

        <p class="font-weight-bold mb-3" id="m2Nombre"></p>

        <div style="width:150px;height:150px;border-radius:12px;border:2px dashed #adb5bd;
                    background:#fff;display:flex;align-items:center;justify-content:center;
                    overflow:hidden;margin:0 auto 12px;position:relative;">
          <img id="m2PreviewImg" src="images/huella.jpg"
               style="width:90px;opacity:.3;transition:all .4s;" alt="Huella">
          <div id="m2PreviewTxt"
               style="position:absolute;bottom:6px;width:100%;
                      text-align:center;font-size:.7rem;color:#999;">
            Sin capturar
          </div>
        </div>

        <div id="m2Spinner" style="display:none;margin-bottom:10px;">
          <div class="spinner-border text-success" role="status"
               style="width:2rem;height:2rem;"></div>
          <p class="text-success mt-1 mb-0" style="font-size:.82rem;">
            Coloca el dedo en el lector...
          </p>
        </div>

        <div id="m2Estado" style="font-size:.82rem;min-height:20px;margin-bottom:10px;"></div>

        <button type="button" id="m2BtnCapturar"
                class="btn btn-success btn-sm w-100 mb-2"
                onclick="capturarHuella2()">
          👆 Capturar huella
        </button>

        <input type="hidden" id="m2Cedula" value="">
        <input type="hidden" id="m2B64"    value="">

        <button type="button" id="m2BtnGuardar"
                class="btn btn-primary w-100 mt-2" disabled
                onclick="guardarHuella2()">
          💾 Guardar huella
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════
     JAVASCRIPT
     ══════════════════════════════════════════════════ -->
<script>
/* URLs de los endpoints de huella */
var URL_CAPTURAR = 'huella/capturar_huella.php';
var URL_GUARDAR  = 'huella/guardar_huella_img.php';

/* ── DataTable ─────────────────────────────────── */
var tbEmpleados;
document.addEventListener('DOMContentLoaded', cargarTabla);

function cargarTabla() {
    if (tbEmpleados) tbEmpleados.destroy();
    tbEmpleados = $('#tbEmpleados').DataTable({
        destroy   : true,
        responsive: true,
        pageLength: 10,
        language  : { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        ajax      : { url: 'ajax.php?module=Empleado&controller=Empleado&function=data',
                      type: 'POST', dataSrc: 'data' },
        columns: [
            { data: 'cedula'   },
            { data: 'nombre'   },
            { data: 'apellido' },
            { data: 'correo'   },
            { data: 'area'     },
            { data: 'huella_badge', orderable: false },
            { data: null, orderable: false, render: function(d) {
                var bh = d.tiene_huella
                    ? `<button class="btn btn-sm btn-success mr-1"
                               onclick="abrirModalHuella('${d.cedula}','${d.nombre} ${d.apellido}')">
                           👆 Actualizar
                       </button>`
                    : `<button class="btn btn-sm btn-outline-success mr-1"
                               onclick="abrirModalHuella('${d.cedula}','${d.nombre} ${d.apellido}')">
                           ➕ Huella
                       </button>`;
                return bh
                    + `<button class="btn btn-sm btn-warning mr-1"
                               onclick="editarEmpleado('${d.cedula}','${d.nombre}','${d.apellido}','${d.correo}','${d.area}')">✏️</button>`
                    + `<button class="btn btn-sm btn-danger"
                               onclick="confirmarEliminar('${d.cedula}','${d.nombre}')">🗑️</button>`;
            }}
        ]
    });
}

/* ── Captura — modal principal ─────────────────── */
var capturando = false;

function capturarHuella() {
    if (capturando) return;
    capturando = true;

    document.getElementById('btnCapturar').disabled = true;
    document.getElementById('huellaSpinner').style.display  = '';
    document.getElementById('huellaEstado').innerHTML = '';

    $.ajax({
        url     : URL_CAPTURAR,
        type    : 'GET',
        dataType: 'json',
        timeout : 25000,   // 25 s (el Python espera hasta 12 s al lector)
        success : function(rs) {
            if (rs.ok) {
                document.getElementById('huellaB64').value = rs.imagen_b64;
                mostrarPreview('huellaPreviewImg','huellaPreviewTxt','huellaEstado',
                               rs.imagen_b64, rs.formato || 'jpg');
                document.getElementById('btnLimpiarH').style.display = '';
            } else {
                setEstado('huellaEstado',
                    '<span class="text-danger">❌ ' + escHtml(rs.error) + '</span>');
            }
        },
        error: function(xhr, status) {
            var msg = status === 'timeout'
                ? 'Tiempo agotado. Coloca el dedo y vuelve a intentar.'
                : 'No se pudo contactar al script Python. Verifica que PHP pueda ejecutar procesos.';
            setEstado('huellaEstado',
                '<span class="text-danger">❌ ' + msg + '</span>');
        },
        complete: function() {
            capturando = false;
            document.getElementById('btnCapturar').disabled  = false;
            document.getElementById('huellaSpinner').style.display = 'none';
        }
    });
}

function limpiarHuella() {
    document.getElementById('huellaB64').value = '';
    resetPreview('huellaPreviewImg','huellaPreviewTxt','huellaEstado');
    document.getElementById('btnLimpiarH').style.display = 'none';
}

/* ── Captura — modal secundario ────────────────── */
var capturando2 = false;

function capturarHuella2() {
    if (capturando2) return;
    capturando2 = true;

    document.getElementById('m2BtnCapturar').disabled = true;
    document.getElementById('m2Spinner').style.display = '';
    document.getElementById('m2Estado').innerHTML = '';

    $.ajax({
        url     : URL_CAPTURAR,
        type    : 'GET',
        dataType: 'json',
        timeout : 25000,
        success : function(rs) {
            if (rs.ok) {
                document.getElementById('m2B64').value = rs.imagen_b64;
                mostrarPreview('m2PreviewImg','m2PreviewTxt','m2Estado',
                               rs.imagen_b64, rs.formato || 'jpg');
                document.getElementById('m2BtnGuardar').disabled = false;
            } else {
                setEstado('m2Estado',
                    '<span class="text-danger">❌ ' + escHtml(rs.error) + '</span>');
            }
        },
        error: function(xhr, status) {
            setEstado('m2Estado',
                '<span class="text-danger">❌ ' +
                (status === 'timeout' ? 'Tiempo agotado.' : 'Error de conexión.') +
                '</span>');
        },
        complete: function() {
            capturando2 = false;
            document.getElementById('m2BtnCapturar').disabled = false;
            document.getElementById('m2Spinner').style.display = 'none';
        }
    });
}

function guardarHuella2() {
    var cedula = document.getElementById('m2Cedula').value;
    var b64    = document.getElementById('m2B64').value;
    if (!cedula || !b64) return;

    document.getElementById('m2BtnGuardar').disabled = true;
    document.getElementById('m2BtnGuardar').textContent = '⏳ Guardando...';

    $.post(URL_GUARDAR, { cedula: cedula, imagen_b64: b64 }, function(rs) {
        if (rs.ok) {
            $('#modalHuella').modal('hide');
            cargarTabla();
            Swal.fire({ icon:'success', title:'✅ Huella guardada',
                        text:'Imagen guardada como: ' + rs.ruta,
                        timer:3000, showConfirmButton:false });
        } else {
            Swal.fire('Error', rs.error || 'No se pudo guardar.', 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'Fallo de conexión.', 'error');
    }).always(function() {
        document.getElementById('m2BtnGuardar').disabled = false;
        document.getElementById('m2BtnGuardar').textContent = '💾 Guardar huella';
    });
}

/* ── Submit formulario empleado (add / update) ─ */
document.getElementById('frmEmpleado').addEventListener('submit', function(e) {
    e.preventDefault();
    var accion    = document.getElementById('accionEmpleado').value;
    var cedulaVal = document.getElementById('cedula').value.trim();
    var b64       = document.getElementById('huellaB64').value;

    function guardarEmpleado(rutaHuella) {
        var fd = new FormData(document.getElementById('frmEmpleado'));
        if (rutaHuella) fd.set('huella', rutaHuella);
        $.ajax({
            url: 'ajax.php?module=Empleado&controller=Empleado&function=' + accion,
            type: 'POST', data: fd, processData: false, contentType: false,
            success: function() {
                $('#modalEmpleado').modal('hide');
                cargarTabla();
                Swal.fire({ icon:'success',
                    title: accion === 'add' ? '✅ Empleado registrado' : '✅ Empleado actualizado',
                    timer:2500, showConfirmButton:false });
            },
            error: function() { Swal.fire('Error','No se pudo completar la operación.','error'); }
        });
    }

    // Si hay imagen capturada, guardarla primero
    if (b64 && cedulaVal) {
        $.post(URL_GUARDAR, { cedula: cedulaVal, imagen_b64: b64 }, function(rs) {
            guardarEmpleado(rs.ok ? rs.ruta : '');
        }, 'json').fail(function() { guardarEmpleado(''); });
    } else {
        guardarEmpleado('');
    }
});

/* ── Helpers modales ───────────────────────────── */
function limpiarModal() {
    document.getElementById('modalTitulo').textContent = 'Registrar Empleado';
    document.getElementById('frmEmpleado').reset();
    document.getElementById('accionEmpleado').value = 'add';
    document.getElementById('cedula').removeAttribute('readonly');
    document.getElementById('btnGuardar').textContent = '💾 Guardar';
    limpiarHuella();
}

function editarEmpleado(cedula, nombre, apellido, correo, area) {
    document.getElementById('modalTitulo').textContent = 'Modificar Empleado';
    document.getElementById('accionEmpleado').value = 'update';
    document.getElementById('cedula').value   = cedula;
    document.getElementById('cedula').setAttribute('readonly', true);
    document.getElementById('nombre').value   = nombre;
    document.getElementById('apellido').value = apellido;
    document.getElementById('correo').value   = correo;
    document.getElementById('area').value     = area;
    document.getElementById('btnGuardar').textContent = '✏️ Actualizar';
    limpiarHuella();
    $('#modalEmpleado').modal('show');
}

function abrirModalHuella(cedula, nombre) {
    document.getElementById('m2Cedula').value = cedula;
    document.getElementById('m2B64').value    = '';
    document.getElementById('m2Nombre').textContent = '👤 ' + nombre;
    document.getElementById('m2Estado').innerHTML = '';
    document.getElementById('m2BtnGuardar').disabled = true;
    document.getElementById('m2BtnGuardar').textContent = '💾 Guardar huella';
    resetPreview('m2PreviewImg','m2PreviewTxt','m2Estado');
    $('#modalHuella').modal('show');
}

function confirmarEliminar(cedula, nombre) {
    Swal.fire({
        title:'¿Eliminar empleado?',
        text:`Esto eliminará a "${nombre}" y todos sus registros de asistencia.`,
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#dc3545', cancelButtonColor:'#6c757d',
        confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
    }).then(function(r) {
        if (r.isConfirmed)
            $.post('ajax.php?module=Empleado&controller=Empleado&function=delete',
                   { cedula: cedula }, function() { cargarTabla(); });
    });
}

/* ── Utilidades imagen ─────────────────────────── */
function mostrarPreview(imgId, txtId, estadoId, b64, fmt) {
    var img = document.getElementById(imgId);
    img.src = 'data:image/' + (fmt || 'jpeg') + ';base64,' + b64;
    img.style.width   = '100%';
    img.style.opacity = '1';
    document.getElementById(txtId).textContent = '';
    setEstado(estadoId,
        '<span class="text-success font-weight-bold">✅ Huella capturada</span>');
}

function resetPreview(imgId, txtId, estadoId) {
    var img = document.getElementById(imgId);
    img.src = 'images/huella.jpg';
    img.style.width   = '90px';
    img.style.opacity = '.3';
    document.getElementById(txtId).textContent = 'Sin capturar';
    setEstado(estadoId, '');
}

function setEstado(id, html) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = html;
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}
</script>
