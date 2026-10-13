<section class="about_section layout_padding">
  <div class="container-fluid">

    <div class="row mb-3">
      <div class="col-12">
        <h2 class="custom_heading">
          Registros de <span>Asistencia</span>
        </h2>
      </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-10 offset-md-1">
        <div class="card shadow-sm">
          <div class="card-body py-3">
            <div class="form-row align-items-end">
              <div class="form-group col-md-4 mb-0">
                <label class="font-weight-bold">📅 Filtrar por fecha</label>
                <input type="date" id="filtroFecha" class="form-control">
              </div>
              <div class="form-group col-md-4 mb-0">
                <label class="font-weight-bold">🏢 Filtrar por área</label>
                <input type="text" id="filtroArea" class="form-control"
                       placeholder="Ej: sistemas, cocina...">
              </div>
              <div class="form-group col-md-2 mb-0">
                <button id="btnFiltrar" class="btn btn-primary w-100">
                  🔍 Filtrar
                </button>
              </div>
              <div class="form-group col-md-2 mb-0">
                <button id="btnLimpiar" class="btn btn-secondary w-100">
                  🔄 Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="row">
      <div class="col-md-10 offset-md-1">
        <table class="table table-bordered table-hover" id="tbRegistros">
          <thead class="thead-dark">
            <tr>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Cédula</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th>Área</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <!-- Botón imprimir -->
    <div class="row mt-2">
      <div class="col-md-10 offset-md-1 text-right">
        <button onclick="window.print()" class="btn btn-outline-secondary">
          🖨️ Generar informe
        </button>
      </div>
    </div>

  </div>
</section>

<script>
var tbRegistros;

function cargarTabla(fecha, area) {
    if (tbRegistros) {
        tbRegistros.destroy();
    }
    tbRegistros = $('#tbRegistros').DataTable({
        destroy      : true,
        responsive   : true,
        searching    : false,
        lengthChange : false,
        pageLength   : 15,
        language     : { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        dom: 'Bfrtip',
        buttons: [{
            extend   : 'excelHtml5',
            text     : '📥 Exportar Excel',
            className: 'btn btn-success btn-sm',
            filename : 'Registros_Asistencia'
        }],
        ajax: {
            url    : 'ajax.php?module=Registros&controller=Registros&function=data',
            type   : 'POST',
            data   : { fecha: fecha || '', area: area || '' },
            dataSrc: 'data'
        },
        columns: [
            { data: 'fecha'    },
            { data: 'hora'     },
            { data: 'cedula'   },
            { data: 'nombre'   },
            { data: 'apellido' },
            { data: 'area'     }
        ]
    });
}

document.addEventListener('DOMContentLoaded', function () {
    cargarTabla('', '');
});

document.getElementById('btnFiltrar').addEventListener('click', function () {
    const fecha = document.getElementById('filtroFecha').value;
    const area  = document.getElementById('filtroArea').value;
    cargarTabla(fecha, area);
});

document.getElementById('btnLimpiar').addEventListener('click', function () {
    document.getElementById('filtroFecha').value = '';
    document.getElementById('filtroArea').value  = '';
    cargarTabla('', '');
});
</script>
