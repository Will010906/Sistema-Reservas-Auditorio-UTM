/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: GESTIÓN Y SUPERVISIÓN ACADÉMICA (SUBDIRECCIÓN)
 * * @package     Frontend_Logic
 * @subpackage  Subdirection_Management
 * @author      Wilmer (Estudiante de Tecnologías de la Información, UTM)
 * @version     2.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Orquestador de la consola de subdirección. Implementa un tablero de control 
 * (Dashboard) con filtrado estricto por carrera y un motor de búsqueda cronológica 
 * blindado. Gestiona la visualización de expedientes mediante consumo de API REST.
 * * CAPACIDADES:
 * 1. KPIs Animados: Visualización dinámica de métricas institucionales.
 * 2. DataTables Blindado: Motor de búsqueda con sanitización de HTML en columnas de fecha.
 * 3. Seguridad JWT: Persistencia de identidad y área académica mediante tokens Bearer.
 * 4. Comunicación Directa: Integración con protocolos de mensajería instantánea.
 */

/* global Swal, bootstrap, $ */

$(document).ready(function () {
    /**
     * 1. INICIALIZACIÓN DE IDENTIDAD ACADÉMICA
     * Extrae el perfil y el área de responsabilidad directamente del JWT.
     */
    const sessionToken = localStorage.getItem('sira_session_token');
    if (!sessionToken) return;

    const payload = JSON.parse(atob(sessionToken.split('.')[1]));
    
    // Inyección de metadatos en la interfaz institucional
    $('#nombreSubdirector').text(payload.nombre);
    $('#areaCarrera').text(payload.area);
    $('#avatarLetra').text(payload.nombre.charAt(0).toUpperCase());

    /**
     * 2. SUBSISTEMA DE VISUALIZACIÓN DE DATOS (DATATABLE)
     */
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
     * MOTOR DE ANIMACIÓN DE MÉTRICAS (KPIs)
     * Utiliza interpolación de números para una experiencia de usuario enriquecida.
     * @param {string} id - Selector del elemento objetivo.
     * @param {number} valorFinal - Cifra meta a alcanzar.
     */
    function animarNumero(id, valorFinal) {
        $({ countNum: $(id).text() }).animate({ countNum: valorFinal }, {
            duration: 1000,
            step: function() { $(id).text(Math.floor(this.countNum)); },
            complete: function() { $(id).text(this.countNum); }
        });
    }

    /**
     * 3. CARGA DE DATOS FILTRADOS (ACADEMIC SCOPE)
     * Consume el servicio de filtrado por carrera para asegurar la privacidad entre áreas.
     */
    function cargarDatosDashboard() {
        fetch(`api/solicitudes/filtrar_solicitudes_carrera.php?area=${encodeURIComponent(payload.area)}`, {
            headers: { 'Authorization': `Bearer ${sessionToken}` }
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) return console.error("Fallo SQL institucional:", data.error);

            // Actualización dinámica de KPIs
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
        .catch(err => console.error("Error de conexión con el núcleo SIRA:", err));
    }

    cargarDatosDashboard();

    /**
     * 4. FILTRO DE FECHAS DINÁMICO BLINDADO
     * Implementa lógica de comparación de fechas mediante sanitización de DOM.
     */
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        let minStr = $('#min_fecha').val();
        let maxStr = $('#max_fecha').val();
        
        // Saneamiento de la columna 3: Elimina etiquetas HTML para extraer la fecha pura
        let fechaCelda = data[3].replace(/<[^>]*>/g, '').trim(); 
        
        if (!minStr && !maxStr) return true;

        // Normalización de formatos (YYYY-MM-DD o DD/MM/YYYY)
        let partes = fechaCelda.split('-'); 
        if (fechaCelda.includes('/')) partes = fechaCelda.split('/');
        
        // Creación de objeto Date a mediodía para neutralizar desfases de zona horaria
        let fechaFila = new Date(partes[0], partes[1] - 1, partes[2], 12, 0, 0);
        if (fechaCelda.includes('/')) {
            fechaFila = new Date(partes[2], partes[1] - 1, partes[0], 12, 0, 0);
        }

        let dMin = minStr ? new Date(minStr + "T12:00:00") : null;
        let dMax = maxStr ? new Date(maxStr + "T12:00:00") : null;

        if (dMin && fechaFila < dMin) return false;
        if (dMax && fechaFila > dMax) return false;
        
        return true;
    });

    $('#min_fecha, #max_fecha').on('change', () => table.draw());

    /**
     * 5. SUBSISTEMA DE REPORTES (EXPORTACIÓN ACADÉMICA)
     */
    $('#btnPDFCarrera').on('click', function() {
        const inicio = $('#min_fecha').val(); 
        const fin = $('#max_fecha').val();    
        if (!inicio || !fin) {
            Swal.fire({ icon: 'info', title: 'Rango incompleto', text: 'Seleccione fechas para delimitar el reporte institucional.', confirmButtonColor: '#5B3D66' });
            return;
        }
        window.open(`api/reportes/generar_reporte_carrera.php?token=${sessionToken}&inicio=${inicio}&fin=${fin}`, '_blank');
    });
});

/**
 * 6. MOTOR DE DETALLE DE EXPEDIENTE
 * Recupera y mapea la información atómica de una reservación.
 * @param {number} id - Identificador de la solicitud.
 */
function verDetalle(id) {
    const modalEl = document.getElementById('bsModalDetalle');
    if (!modalEl) return;
    
    const modalSira = new bootstrap.Modal(modalEl);
    modalSira.show();

    // Reset de placeholders visuales
    $('#detFolio, #detTituloEv, #detUsuarioNombre').text('...');

    const token = localStorage.getItem('sira_session_token');
    fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(res => {
        if (!res.ok) throw new Error("Acceso denegado o recurso inexistente");
        return res.json();
    })
    .then(data => {
        // Mapeo integral de datos al Modal de Auditoría
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

        // Vinculación dinámica a WhatsApp Business
        if(data.telefono) {
            $('#btnWhatsApp').attr('href', `https://wa.me/52${data.telefono}`).show();
        } else {
            $('#btnWhatsApp').hide();
        }

        // Renderizado de recursos solicitados (Chips)
        let htmlEquip = '';
        if(data.equipamiento_fijo) {
            data.equipamiento_fijo.split(',').forEach(item => {
                htmlEquip += `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size:0.6rem;">${item.trim()}</span>`;
            });
        }
        $('#detEquipamiento').html(htmlEquip || '<small class="text-muted">Sin equipamiento extra</small>');

        // Restricción de interfaz: El subdirector solo mantiene rol de visualización
        $('#seccionBitacoraAdmin').hide();
    })
    .catch(err => {
        modalSira.hide();
        Swal.fire('Restricción de Seguridad', 'No tiene los privilegios necesarios para auditar esta solicitud.', 'error');
    });
}

function confirmarSalida() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Su sesión de supervisión académica finalizará.",
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