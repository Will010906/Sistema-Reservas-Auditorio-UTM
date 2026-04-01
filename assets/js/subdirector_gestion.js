/**
 * GESTIÓN DE SUBDIRECCIÓN - SIRA UTM
 * Lógica de datos, animaciones y Filtro de Fechas Blindado.
 */
$(document).ready(function () {
    const sessionToken = localStorage.getItem('sira_session_token');
    if (!sessionToken) return;

    const payload = JSON.parse(atob(sessionToken.split('.')[1]));
    
    // Inyectar datos del perfil en el HTML
    $('#nombreSubdirector').text(payload.nombre);
    $('#areaCarrera').text(payload.area);
    $('#avatarLetra').text(payload.nombre.charAt(0).toUpperCase());

    // Inicializar DataTable (Prevenir error de re-inicialización)
    if ($.fn.DataTable.isDataTable('#tablaSubdirector')) {
        $('#tablaSubdirector').DataTable().destroy();
    }

    const table = $('#tablaSubdirector').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pageLength: 10,
        dom: '<"p-3"f>rtip',
        order: [[0, 'desc']]
    });

    /**
     * Función para animar los números de los KPIs
     */
    function animarNumero(id, valorFinal) {
        $({ countNum: $(id).text() }).animate({ countNum: valorFinal }, {
            duration: 1000,
            step: function() { $(id).text(Math.floor(this.countNum)); },
            complete: function() { $(id).text(this.countNum); }
        });
    }

    /**
     * Carga de datos filtrados desde el API
     */
    function cargarDatosDashboard() {
        fetch(`api/solicitudes/filtrar_solicitudes_carrera.php?area=${encodeURIComponent(payload.area)}`, {
            headers: { 'Authorization': `Bearer ${sessionToken}` }
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) return console.error("Error SQL:", data.error);

            animarNumero('#kpiTotal', data.length);
            animarNumero('#kpiPendientes', data.filter(s => s.estado === 'Pendiente').length);
            animarNumero('#kpiAceptadas', data.filter(s => s.estado === 'Aceptada').length);
            
            table.clear();
            data.forEach(row => {
                let badgeClass = 'bg-warning text-warning';
                if(row.estado === 'Aceptada') badgeClass = 'bg-success text-success';
                if(row.estado === 'Rechazada') badgeClass = 'bg-danger text-danger';
                
              table.row.add([
    `<span class="folio-sira">#${row.folio}</span>`,
    `<div class="small fw-bold text-dark">${row.nombre}</div>`,
    `<span class="small fw-bold text-dark">${row.titulo_event}</span>`,
    `<span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>${row.fecha_evento}</span>`,
    `<div class="text-center"><span class="badge-status bg-opacity-10 ${badgeClass}">${row.estado.toUpperCase()}</span></div>`,
    `<div class="text-center">
        <button class="btn btn-view-pro shadow-sm" onclick="verDetalle(${row.id_solicitud})" title="Ver Detalles">
            <i class="bi bi-eye-fill"></i>
        </button>
    </div>`
]);
            });
            table.draw();
        })
        .catch(err => console.error("Error al conectar con API:", err));
    }

    cargarDatosDashboard();

    // 3. FILTRO DE FECHAS DINÁMICO BLINDADO
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        let minStr = $('#min_fecha').val();
        let maxStr = $('#max_fecha').val();
        
        // Limpiamos el texto de la columna fecha (columna 3) quitando iconos HTML
        let fechaCelda = data[3].replace(/<[^>]*>/g, '').trim(); 
        
        if (!minStr && !maxStr) return true;

        // Convertir fecha de la tabla (dd/mm/yyyy) a objeto Date comparable
        let partes = fechaCelda.split('-'); // Cambiar a '-' si tu PHP manda YYYY-MM-DD
        if (fechaCelda.includes('/')) partes = fechaCelda.split('/');
        
        // Creamos la fecha a mediodía para evitar problemas de zona horaria
        let fechaFila = new Date(partes[0], partes[1] - 1, partes[2], 12, 0, 0);
        if (fechaCelda.includes('/')) {
            fechaFila = new Date(partes[2], partes[1] - 1, partes[0], 12, 0, 0);
        }

        // Convertir inputs a Date (vienen como YYYY-MM-DD)
        let dMin = minStr ? new Date(minStr + "T12:00:00") : null;
        let dMax = maxStr ? new Date(maxStr + "T12:00:00") : null;

        if (dMin && fechaFila < dMin) return false;
        if (dMax && fechaFila > dMax) return false;
        
        return true;
    });

    $('#min_fecha, #max_fecha').on('change', () => table.draw());

    // Reporte PDF
    $('#btnPDFCarrera').on('click', function() {
        const inicio = $('#min_fecha').val(); 
        const fin = $('#max_fecha').val();    
        if (!inicio || !fin) {
            Swal.fire({ icon: 'info', title: 'Rango incompleto', text: 'Selecciona fechas para el reporte.', confirmButtonColor: '#5B3D66' });
            return;
        }
        window.open(`api/reportes/generar_reporte_carrera.php?token=${sessionToken}&inicio=${inicio}&fin=${fin}`, '_blank');
    });
});

// Funciones Globales
function abrirNuevaReservacion() { $('#modalNuevaSolicitud').modal('show'); }
function limpiarFiltrosFecha() { $('#min_fecha, #max_fecha').val('').trigger('change'); }

function confirmarSalida() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Tu acceso de supervisión terminará.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#5B3D66',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => { 
        if (result.isConfirmed) { 
            localStorage.removeItem('sira_session_token');
            window.location.href = 'index.php?status=logout'; 
        } 
    });
}
/**
 * Abre el modal de detalle y llena la información mapeada
 */
function verDetalle(id) {
    console.log("Visualizando solicitud ID:", id);
    
    // 1. Instanciar y mostrar el modal (Usando el ID correcto: bsModalDetalle)
    const modalEl = document.getElementById('bsModalDetalle');
    if (!modalEl) return console.error("No se encontró el modal bsModalDetalle");
    
    const modalSira = new bootstrap.Modal(modalEl);
    modalSira.show();

    // 2. Estado de carga visual
    $('#detFolio').text('...');
    $('#detTituloEv').text('Cargando...');
    $('#detUsuarioNombre').text('...');

    // 3. Obtener datos del servidor
    const token = localStorage.getItem('sira_session_token');
    fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(res => {
        if (!res.ok) throw new Error("Error 403 o no encontrado");
        return res.json();
    })
    .then(data => {
        // --- MAPEO EXACTO A LOS IDs DE TU MODAL ---
        $('#detFolio').text(data.folio);
        $('#detTituloEv').text(data.titulo_event);
        $('#detUsuarioNombre').text(data.nombre_usuario);
        $('#detCarrera').text(data.carrera);
        $('#detMatricula').text(data.matricula);
        $('#detCorreo').text(data.correo);
        $('#detAuditorio').text(data.nombre_espacio);
        $('#detAsistentes').text(data.num_asistentes + ' asistentes aprox.');
        $('#detFechaEvento').text(data.fecha_evento_limpia);
        $('#detHorario').text(`${data.hora_inicio.substring(0,5)} a ${data.hora_fin.substring(0,5)}`);
        $('#detDescription').text(data.descripcion || 'Sin descripción adicional.');

        // WhatsApp Dinámico
        if(data.telefono) {
            $('#btnWhatsApp').attr('href', `https://wa.me/52${data.telefono}`).show();
        } else {
            $('#btnWhatsApp').hide();
        }

        // Equipamiento (Chips)
        let htmlEquip = '';
        if(data.equipamiento_fijo) {
            data.equipamiento_fijo.split(',').forEach(item => {
                htmlEquip += `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:0.6rem;">${item.trim()}</span>`;
            });
        }
        $('#detEquipamiento').html(htmlEquip || '<small class="text-muted">Ninguno</small>');

        // Ocultar sección de administrador (ya que el subdirector solo visualiza)
        $('#seccionBitacoraAdmin').hide();
    })
    .catch(err => {
        console.error("Fallo al cargar:", err);
        modalSira.hide();
        Swal.fire('Acceso Restringido', 'No tienes permiso para ver esta solicitud.', 'error');
    });
}