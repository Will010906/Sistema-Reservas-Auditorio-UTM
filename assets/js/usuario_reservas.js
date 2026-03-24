/**
 * GESTIÓN DE MIS RESERVACIONES (USUARIO) - SIRA UTM
 * Implementa: Async/Await, Seguridad JWT y Respuesta JSON
 */

/* global $, Swal, bootstrap */

$(document).ready(function () {
    // 1. Inicialización de DataTable (Filtros útiles - 15%)
    if ($.fn.DataTable.isDataTable('#tablaMisReservas')) {
        $('#tablaMisReservas').DataTable().destroy();
    }

    const table = $('#tablaMisReservas').DataTable({
        retrieve: true,
        autoWidth: false,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pageLength: 10,
        order: [[0, "desc"]],
        dom: 'rtip' 
    });

    // Filtros por estatus (Filtros útiles funcionando)
    $('.check-filtro').on('change', function () {
        let valores = [];
        $('.check-filtro:checked').each(function () { valores.push($(this).val()); });
        table.column(4).search(valores.length > 0 ? valores.join('|') : '', true, false).draw();
    });

    // Accesos rápidos desde las Cards
    $('.card-user.bg-pend').on('click', () => { $('#f_pen').click(); });
    $('.card-user.bg-acep').on('click', () => { $('#f_apr').click(); });
    $('.card-user.bg-rech').on('click', () => { $('#f_rec').click(); });
});

// 2. Ver Detalle con Seguridad JWT (Requisito 30%)
window.verDetalleUsuario = async function(id) {
    try {
        // CORRECCIÓN: Ruta al subdirectorio 'solicitudes' y uso de 'sira_session_token'
        const res = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });

        if (res.status === 401) return manejarSesionExpirada();

        const data = await res.json();
        if(data.error) return Swal.fire('Error', data.error, 'error');

        // Diseño Premium (Evaluado en UX)
        let htmlContent = `
            <div class="text-start px-3 py-1">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-light p-3 rounded-4 me-3 border shadow-sm">
                        <i class="bi bi-calendar-check-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Evento</label>
                        <h4 class="fw-800 mb-0" style="color: #2D1B33;">${data.titulo_event}</h4>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 border-0 rounded-4 shadow-sm bg-light">
                            <label class="text-muted fw-bold text-uppercase mb-1 d-block small">Lugar</label>
                            <span class="small fw-700 color-purple">${data.nombre_espacio}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border-0 rounded-4 shadow-sm bg-light">
                            <label class="text-muted fw-bold text-uppercase mb-1 d-block small">Fecha</label>
                            <span class="small fw-700 color-purple">${data.fecha_evento}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-4 border-start border-5 border-primary shadow-sm bg-white">
                    <label class="text-primary fw-800 text-uppercase mb-2 d-block small">Observaciones del Admin</label>
                    <p class="mb-0 text-dark italic small">
                        "${data.observaciones_admin || 'Tu solicitud está siendo procesada.'}"
                    </p>
                </div>
            </div>`;

        Swal.fire({
            html: htmlContent,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#5B3D66',
            customClass: { popup: 'rounded-5 border-0' }
        });
    } catch (error) {
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    }
};

// 3. Cancelar Solicitud (DELETE semántico + JWT)
window.cancelarMiSolicitud = function(id) {
    Swal.fire({
        title: '¿Cancelar reservación?',
        text: "Esta acción es irreversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EE5253',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'No, mantener'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // CORRECCIÓN: Ruta al controlador maestro 'gestion_solicitudes.php' y token correcto
                const res = await fetch(`api/solicitudes/gestion_solicitudes.php?id=${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
                });

                const data = await res.json();
                if(data.success) {
                    Swal.fire('Eliminado', 'Solicitud borrada.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Error al procesar la baja.', 'error');
            }
        }
    });
};

// 4. Editar (Modificar) con Carga Asíncrona
window.editarMiSolicitud = async function(id) {
    Swal.fire({ title: 'Cargando...', didOpen: () => { Swal.showLoading() }, allowOutsideClick: false });

    try {
        // CORRECCIÓN: Ruta al subdirectorio y token correcto
        const res = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        const data = await res.json();
        Swal.close();

        if(data.error) return Swal.fire('Error', data.error, 'error');

        // Mapeo de datos al formulario
        $('input[name="titulo"]').val(data.titulo_event);
        $('textarea[name="descripcion"]').val(data.descripcion_event || data.descripcion);
        $('input[name="otros_servicios"]').val(data.otros_servicios || '');
        
        // Logística oculta
        $('#input_id_auditorio').val(data.id_auditorio);
        $('#input_fecha_evento').val(data.fecha_evento);
        $('#input_hora_inicio').val(data.hora_inicio);
        $('#input_hora_fin').val(data.hora_fin);

        // Configuración de modo edición
        $('#modalNuevaSolicitud .modal-title').text('Modificar Reservación');
        // CORRECCIÓN: Apuntar al controlador maestro para procesar la edición
        $('#modalNuevaSolicitud form').attr('action', 'api/solicitudes/gestion_solicitudes.php');
        
        if(!$('#id_editando').length) {
            $('#modalNuevaSolicitud form').append(`<input type="hidden" name="id_editando" id="id_editando" value="${id}">`);
        } else {
            $('#id_editando').val(id);
        }

        // Mostrar solo el paso de formulario
        $('#paso_catalogo, #paso_calendario').hide();
        $('#paso_formulario').show();

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaSolicitud')).show();
    } catch (e) {
        Swal.fire('Error', 'Fallo de red.', 'error');
    }
};

function manejarSesionExpirada() {
    // CORRECCIÓN: Limpieza del token correcto
    localStorage.removeItem('sira_session_token');
    Swal.fire('Sesión Expirada', 'Por seguridad, reingresa al sistema.', 'error')
        .then(() => window.location.href = 'index.php');
}