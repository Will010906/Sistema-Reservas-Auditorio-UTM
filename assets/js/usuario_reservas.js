/**
 * GESTIÓN DE MIS RESERVACIONES (USUARIO) - SIRA UTM
 */

$(document).ready(function () {
    // 1. Inicialización de DataTable
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

    // 2. Filtros y Cards
    $('.check-filtro').on('change', function () {
        let valores = [];
        $('.check-filtro:checked').each(function () { valores.push($(this).val()); });
        table.column(4).search(valores.length > 0 ? valores.join('|') : '', true, false).draw();
    });

    $('.card-user.bg-pend').on('click', () => { $('#f_pen').click(); });
    $('.card-user.bg-acep').on('click', () => { $('#f_apr').click(); });
    $('.card-user.bg-rech').on('click', () => { $('#f_rec').click(); });
});

// 3. Ver Detalle con Diseño Premium
window.verDetalleUsuario = function(id) {
    fetch(`modules/get_detalle.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.error) return Swal.fire('Error', data.error, 'error');

            let htmlContent = `
            <div class="text-start px-3 py-1">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-light p-3 rounded-4 me-3 border shadow-sm">
                        <i class="bi bi-shield-lock-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Título del Evento</label>
                        <h4 class="fw-800 mb-0" style="color: #2D1B33;">${data.titulo_event}</h4>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 border-0 rounded-4 shadow-sm bg-light">
                            <label class="text-muted fw-bold text-uppercase mb-1 d-block small">Auditorio</label>
                            <span class="small fw-700 color-purple"><i class="bi bi-geo-alt me-1 text-danger"></i> ${data.nombre_espacio}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border-0 rounded-4 shadow-sm bg-light">
                            <label class="text-muted fw-bold text-uppercase mb-1 d-block small">Fecha</label>
                            <span class="small fw-700 color-purple"><i class="bi bi-calendar3 me-1 text-warning"></i> ${data.fecha_evento_limpia}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-4 border-start border-5 border-primary shadow-sm bg-white">
                    <label class="text-primary fw-800 text-uppercase mb-2 d-block small">Respuesta de Administración</label>
                    <p class="mb-0 text-dark italic small">
                        <i class="bi bi-chat-left-quote-fill opacity-25 me-2"></i>
                        "${data.observaciones_admin || 'Tu solicitud está siendo procesada.'}"
                    </p>
                </div>
            </div>`;

            Swal.fire({
                html: htmlContent,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#5B3D66',
                customClass: { popup: 'rounded-5 border-0', confirmButton: 'rounded-pill px-4' }
            });
        });
};

window.cancelarMiSolicitud = function(id) {
    Swal.fire({
        title: '¿Cancelar reservación?',
        text: "Esta acción eliminará tu folio permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EE5253',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`modules/eliminar_solicitud.php?id=${id}`)
                .then(res => res.json())
                .then(data => { 
                    if(data.success) {
                        Swal.fire('Eliminado', 'Tu solicitud ha sido borrada.', 'success')
                        .then(() => location.reload()); 
                    }
                });
        }
    });
};

// 4. Función Editar (Modificar)
window.editarMiSolicitud = function(id) {
    // Feedback visual de carga
    Swal.fire({ title: 'Cargando datos...', didOpen: () => { Swal.showLoading() }, allowOutsideClick: false });

    fetch(`modules/get_detalle.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if(data.error) return Swal.fire('Error', data.error, 'error');

            // --- A. LLENADO DE FORMULARIO ---
            $('input[name="titulo"]').val(data.titulo_event);
            $('textarea[name="descripcion"]').val(data.descripcion_event || data.descripcion);
            $('input[name="otros_servicios"]').val(data.otros_servicios || '');
            
            // Campos ocultos de logística
            $('#input_id_auditorio').val(data.id_auditorio);
            $('#input_fecha_evento').val(data.fecha_evento);
            $('#input_hora_inicio').val(data.hora_inicio);
            $('#input_hora_fin').val(data.hora_fin);

            // --- B. PRECARGA VISUAL ---
            $('#display_nombre_final').text(data.nombre_espacio);
            const imgPreview = document.getElementById("img_final_preview");
            if (imgPreview) {
                imgPreview.src = `assets/img/auditorios/${data.id_auditorio}.jpg`;
                imgPreview.onerror = function() { this.src = 'assets/img/placeholder.jpg'; };
            }

            // --- C. CONFIGURACIÓN DE MODO EDICIÓN ---
            $('#modalNuevaSolicitud .modal-title').text('Modificar Reservación: ' + data.folio);
            $('#modalNuevaSolicitud form').attr('action', 'modules/guardar_edicion_usuario.php');
            
            // Inyectamos el ID de control
            if(!$('#id_editando').length) {
                $('#modalNuevaSolicitud form').append(`<input type="hidden" name="id_editando" id="id_editando" value="${id}">`);
            } else {
                $('#id_editando').val(id);
            }

            // --- D. LIMPIEZA DE INTERFAZ (OCULTAR BOTONES)
            $('.btn-link[onclick*="irAlCalendario"]').hide(); // Enlace arriba
            $('#paso_formulario .btn-link:contains("Anterior")').hide(); // Botón abajo

            // --- E. MOSTRAR PASO FINAL ---
            $('#paso_catalogo, #paso_calendario').hide();
            $('#paso_formulario').show();

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaSolicitud')).show();
        });
};

// RESET AL CERRAR EL MODAL
$('#modalNuevaSolicitud').on('hidden.bs.modal', function () {
    $('#id_editando').remove();
    $('#modalNuevaSolicitud form').attr('action', 'modules/procesar_solicitud.php');
    $('#modalNuevaSolicitud form')[0].reset();
    
    // Restaurar visibilidad de navegación para nuevas solicitudes
    $('#paso_catalogo').show();
    $('#paso_formulario, #paso_calendario').hide();
    $('a.btn-link, button.btn-link').show(); 
    $('#modalNuevaSolicitud .modal-title').text('Nueva Reservación');
});

