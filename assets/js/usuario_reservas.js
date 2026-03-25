/**
 * GESTIÓN DE RESERVACIONES SIRA UTM - VERSIÓN INTEGRAL
 * Incluye: Creación, Edición, Cancelación y Visualización.
 */

/* global $, Swal, bootstrap, FullCalendar */
let auditorioSeleccionado = null;
let horaInicioSeleccionada = null;

$(document).ready(function () {
    // --- GESTIÓN DE IDENTIDAD SEGURA ---
    const token = localStorage.getItem('sira_session_token');
    
    if (token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const payload = JSON.parse(window.atob(base64));
            
            console.log("Datos del Token:", payload); // Revisa esto en F12 si sigue sin salir

            // Usamos nombres de propiedades comunes, cámbialos si en tu PHP el token es distinto
            const nombre = payload.nombre || payload.name || "Usuario";
            const rol = payload.perfil || payload.role || "Docente";

            // "Pintamos" los datos en el HTML
            $('#nombreUsuarioHeader').text(nombre);
            $('#rolUsuarioHeader').text(rol.toUpperCase());
            $('#inicialAvatarUsuario').text(nombre.charAt(0).toUpperCase());

        } catch (e) {
            console.error("Error al decodificar token:", e);
        }
    }

    cargarMisReservaciones();

    // --- 3. FILTRADO POR CHECKBOXES (DATATABLES) ---
    $(document).on('change', '.check-filtro', function () {
        const table = $('#tablaMisReservas').DataTable();
        let seleccionados = [];
        
        $('.check-filtro:checked').each(function () { 
            seleccionados.push($(this).val()); 
        });
        
        // Creamos una expresión regular para filtrar múltiples estatus
        const regex = seleccionados.length > 0 ? '^(' + seleccionados.join('|') + ')$' : '';
        table.column(4).search(regex, true, false).draw();
    });
});

/**
 * 1. CARGA DE TABLA
 */
async function cargarMisReservaciones() {
    const contenedor = document.getElementById("contenedorMisReservas");
    if (!contenedor) return;

    try {
        const response = await fetch('api/solicitudes/get_mis_reservas.php', {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        if (response.status === 401) return window.manejarSesionExpirada();
        const data = await response.json();
        
        if ($.fn.DataTable.isDataTable('#tablaMisReservas')) $('#tablaMisReservas').DataTable().destroy();
        contenedor.innerHTML = ""; 

        if (data.success && data.solicitudes.length > 0) {
            data.solicitudes.forEach(sol => {
                const esEditable = sol.estado.toUpperCase() === 'PENDIENTE';
                contenedor.innerHTML += `
                    <tr>
                        <td class="ps-4 fw-bold" style="color: #5B3D66;">#${sol.folio}</td>
                        <td>
                            <div class="fw-bold">${sol.titulo_event}</div>
                            <div class="text-muted x-small">${sol.nombre_espacio}</div>
                        </td>
                        <td><span class="badge rounded-pill bg-light text-dark border px-3">${sol.nombre_espacio}</span></td>
                        <td class="fw-bold text-muted">${sol.fecha_evento}</td>
                        <td class="text-center">
                            <span class="badge-status st-${sol.estado.toLowerCase()} shadow-sm">${sol.estado}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light border" onclick="window.verDetalleUsuario(${sol.id_solicitud})" title="Ver Detalle">
                                    <i class="bi bi-eye-fill text-primary"></i>
                                </button>
                                ${esEditable ? `
                                    <button class="btn btn-sm btn-light border" onclick="window.editarMiSolicitud(${sol.id_solicitud})" title="Editar">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border" onclick="window.cancelarMiSolicitud(${sol.id_solicitud})" title="Cancelar">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>` : ''}
                            </div>
                        </td>
                    </tr>`;
            });
            
            $('#tablaMisReservas').DataTable({
                retrieve: true, language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                dom: 'rtip', pageLength: 10, order: [[0, "desc"]],
                columnDefs: [{ targets: [5], orderable: false }]
            });

            if (data.stats) {
                document.getElementById("countPendientes").innerText = data.stats.pendientes || 0;
                document.getElementById("countAprobadas").innerText = data.stats.aprobadas || 0;
                document.getElementById("countRechazadas").innerText = data.stats.rechazadas || 0;
            }
        }
    } catch (error) { console.error("Error al cargar reservaciones", error); }
}

/**
 * 2. LÓGICA DE HORARIOS Y GRID
 */
window.obtenerDisponibilidadHoraria = async function(id, fecha) {
    const txt = document.getElementById('fecha_seleccionada_txt');
    horaInicioSeleccionada = null; 
    document.getElementById('btnConfirmarHorario').disabled = true;

    try {
        txt.innerText = "Consultando...";
        const response = await fetch(`api/solicitudes/get_disponibilidad.php?id=${id}&fecha=${fecha}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        const data = await response.json();
        txt.innerText = `Horarios para el ${fecha}:`;
        generarGridHorarios(data); 
    } catch (e) {
        txt.innerText = "Error de conexión.";
    }
};

function generarGridHorarios(ocupados) {
    const grid = document.getElementById('grid_horarios');
    const txtResumen = document.getElementById('fecha_seleccionada_txt'); 
    const msjError = document.getElementById('msj_error_rango');
    
    // Formato de fecha descriptivo
    const fechaInput = document.getElementById('input_fecha_evento').value;
    if (fechaInput) {
        const opciones = { weekday: 'long', day: 'numeric', month: 'long' };
        const fechaObj = new Date(fechaInput + 'T00:00:00');
        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);
        const inputDisplay = document.getElementById('fecha_display');
        if(inputDisplay) inputDisplay.value = fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);
    }

    grid.innerHTML = "";
    $(msjError).hide();

    for (let h = 7; h <= 19; h++) {
        const hInicioRaw = `${h.toString().padStart(2, '0')}:00`;
        const hTexto = `${h > 12 ? h - 12 : h}:00 ${h >= 12 ? 'PM' : 'AM'}`;
        const estaOcupado = ocupados.some(o => (hInicioRaw >= o.inicio && hInicioRaw < o.fin));

        const btn = document.createElement('button');
        btn.type = "button";
        btn.id = `btn-hora-${h}`; 
        btn.className = `btn btn-horario ${estaOcupado ? 'ocupado disabled' : ''}`;
        btn.innerHTML = estaOcupado ? `<i class="bi bi-x-circle me-1"></i> ${hTexto}` : hTexto;

        if (!estaOcupado) {
            btn.onclick = () => {
                if (!horaInicioSeleccionada) {
                    horaInicioSeleccionada = h;
                    document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('activo'));
                    btn.classList.add('activo');
                    document.getElementById('input_hora_inicio').value = hInicioRaw;
                    txtResumen.innerText = `Inicia: ${hTexto}. Selecciona el fin.`;
                } else {
                    const diff = h - horaInicioSeleccionada;
                    if (diff > 5 || diff < 0) { 
                        $(msjError).fadeIn();
                        horaInicioSeleccionada = null;
                        document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('activo'));
                    } else {
                        document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('activo'));
                        for (let i = horaInicioSeleccionada; i <= h; i++) {
                            const bRango = document.getElementById(`btn-hora-${i}`);
                            if (bRango) bRango.classList.add('activo');
                        }

                        const hFinReal = h + 1;
                        const hInicioTexto = `${horaInicioSeleccionada > 12 ? horaInicioSeleccionada - 12 : horaInicioSeleccionada}:00 ${horaInicioSeleccionada >= 12 ? 'PM' : 'AM'}`;
                        const hFinTexto = `${h > 12 ? h - 12 : h}:00 ${h >= 12 ? 'PM' : 'AM'}`;

                        document.getElementById('input_hora_fin').value = `${hFinReal.toString().padStart(2, '0')}:00`;
                        document.getElementById('btnConfirmarHorario').disabled = false;
                        txtResumen.innerHTML = `<i class="bi bi-clock-history me-1"></i> Horario: <b>${hInicioTexto}</b> a <b>${hFinTexto}</b>`;
                        horaInicioSeleccionada = null; 
                    }
                }
            };
        }
        grid.appendChild(btn);
    }
}

/**
 * 3. ENVÍO DE FORMULARIO (DETECTA SI ES POST O PUT)
 */
$(document).on('submit', '#formNuevaReservacion', async function(e) {
    e.preventDefault(); 
    
    // 1. Obtenemos el ID del input oculto
    const idEditando = $('#id_editando').val();
    console.log("ID detectado:", idEditando || "Ninguno (Es nueva reservación)");

    // 2. VALIDACIÓN INTELIGENTE:
    // Solo exigimos ID si el título del modal indica una modificación
    const esEdicion = $('#modalNuevaSolicitud .modal-title').text().includes('Modificar');
    
    if (esEdicion && !idEditando) {
        return Swal.fire('Error', 'No se detectó el ID de la reservación para editar.', 'error');
    }

    // 3. Definimos el método según la existencia del ID
    const metodo = idEditando ? 'PUT' : 'POST';

    // 4. Recolectamos extras de los checkboxes (Laptop/Grabación)
    const checks = Array.from(document.querySelectorAll('input[name="extras[]"]:checked')).map(el => el.value);
    // Recolectamos el texto manual del input "otros_servicios"
    const textoExtra = $('input[name="otros_servicios"]').val().trim();
    
    // Unificamos ambos en una sola cadena para la base de datos
    let totalServicios = [...checks];
    if (textoExtra) totalServicios.push(textoExtra);
    const serviciosFinales = totalServicios.join(', ') || 'Sin requerimientos extras';

    const datos = {
        id_editando: idEditando, 
        id_auditorio: $('#input_id_auditorio').val(),
        num_asistentes: $('input[name="num_asistentes"]').val() || 10,
        fecha_evento: $('#input_fecha_evento').val(),
        hora_inicio:  $('#input_hora_inicio').val(),
        hora_fin:     $('#input_hora_fin').val(),
        titulo:       $('input[name="titulo"]').val(),
        descripcion:  $('textarea[name="descripcion"]').val(),
        otros_servicios: serviciosFinales // Enviamos la cadena unificada
    };

    try {
        const res = await fetch('api/solicitudes/gestion_solicitudes.php', {
            method: metodo,
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
            },
            body: JSON.stringify(datos)
        });

        const result = await res.json();
        if (result.success) {
            Swal.fire({ title: '¡Éxito!', text: result.message || 'Operación completada.', icon: 'success' }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalNuevaSolicitud')).hide();
                location.reload(); 
            });
        } else {
            // Si el servidor dice "No se realizaron cambios", es por el estado o falta de cambios reales
            Swal.fire('Atención', result.error, 'warning');
        }
    } catch (error) {
        Swal.fire('Error', 'Fallo de red al conectar con el servidor.', 'error');
    }
});

/**
 * 4. EDICIÓN Y DETALLE
 */
window.editarMiSolicitud = async function(id) {
    const res = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
    });
    const data = await res.json();

    if(data.error) return Swal.fire('Error', data.error, 'error');

    // Cambiamos el contexto del modal
    $('#modalNuevaSolicitud .modal-title').text('Modificar Reservación');
    $('input[name="titulo"]').val(data.titulo_event);
    $('textarea[name="descripcion"]').val(data.descripcion);
    $('input[name="num_asistentes"]').val(data.num_asistentes);
    // Cargamos los datos de tiempo y espacio
    $('#input_id_auditorio').val(data.id_auditorio);
    $('#input_fecha_evento').val(data.fecha_evento);
    $('#input_hora_inicio').val(data.hora_inicio);
    $('#input_hora_fin').val(data.hora_fin);

    // --- LÓGICA DE EXTRAS Y CHECKBOXES ---
    // 1. Limpiamos checks anteriores
    document.querySelectorAll('input[name="extras[]"]').forEach(cb => cb.checked = false);
    $('input[name="otros_servicios"]').val('');

    if (data.otros_servicios) {
        const extrasPrevios = data.otros_servicios.split(', ');
        // Marcamos Laptop o Grabación si existen en el texto
        document.querySelectorAll('input[name="extras[]"]').forEach(cb => {
            if (extrasPrevios.includes(cb.value)) cb.checked = true;
        });
        
        // El resto del texto lo ponemos en el input manual
        const textoLimpio = extrasPrevios.filter(e => e !== 'Laptop' && e !== 'Grabacion').join(', ');
        $('input[name="otros_servicios"]').val(textoLimpio);
    }

    // Inyectamos el ID de forma segura
    if($('#id_editando').length === 0) {
        $('#formNuevaReservacion').append(`<input type="hidden" id="id_editando" name="id_editando" value="${id}">`);
    } else {
        $('#id_editando').val(id);
    }

    // Navegación visual del modal
    $('#paso_catalogo, #paso_calendario').hide();
    $('#paso_formulario').fadeIn();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaSolicitud')).show();
};

window.verDetalleUsuario = async function(id) {
    const res = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
    });
    const data = await res.json();

    // Debug: mira esto en tu consola (F12) para ver qué llega exactamente
    console.log("Datos de la solicitud:", data);

    const ahora = new Date();
    const fechaFinEvento = new Date(`${data.fecha_evento}T${data.hora_fin}`);
    const esPasado = ahora > fechaFinEvento;

    // Convertimos a mayúsculas para que 'Aceptada' y 'ACEPTADA' sean iguales
    const estado = data.estado ? data.estado.toUpperCase() : '';
const textoNotas = data.notas_admin || data.observaciones_admin || 'En revisión.';
    let htmlContent = `
        <div class="text-start p-2">
        
            <h5 class="fw-bold text-primary mb-3">${data.titulo_event}</h5>
            <p class="small mb-1"><strong>Folio:</strong> ${data.folio}</p>
            <p class="small"><strong>Estado:</strong> ${data.estado}</p>
            <div class="bg-light p-3 rounded-4 mt-2 border-start border-4 border-info">
                <label class="extra-small fw-bold text-uppercase d-block mb-1">Notas Administrativas:</label>
                <span class="small italic">"${textoNotas}"</span>
            </div>`;
if (estado === 'ACEPTADA' && esPasado && !data.incidentes_cierre) {
        htmlContent += `
            <hr class="my-4">
            <div class="mt-3 p-3 border border-danger rounded-4 bg-danger bg-opacity-10 shadow-sm">
                <label class="small fw-bold text-danger d-block mb-2 text-uppercase">Reportar Cierre e Incidentes</label>
                <textarea id="incidente_texto" class="form-control form-control-sm mb-2" rows="3" placeholder="¿Daños o problemas?"></textarea>
                <button onclick="guardarCierre(${data.id_solicitud})" class="btn btn-danger btn-sm w-100 fw-bold">Finalizar Reservación</button>
            </div>`;
    } else if (data.incidentes_cierre) {
        htmlContent += `<div class="alert alert-success mt-3 small"><strong>Cierre:</strong> ${data.incidentes_cierre}</div>`;
    }

    Swal.fire({ html: htmlContent + '</div>', showConfirmButton: false, showCloseButton: true });
};

window.guardarCierre = async function(id) {
    const texto = document.getElementById('incidente_texto').value;
    if (!texto.trim()) return Swal.showValidationMessage('Describe el estado de entrega.');

    try {
        const res = await fetch('api/solicitudes/reportar_cierre.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` },
            body: JSON.stringify({ id: id, incidentes: texto })
        });
        if ((await res.json()).success) {
            Swal.fire('¡Éxito!', 'Auditorio entregado.', 'success').then(() => location.reload());
        }
    } catch (e) { Swal.fire('Error', 'No se pudo cerrar.', 'error'); }
};

/**
 * 5. NAVEGACIÓN Y EXTRAS
 */
window.abrirModalNuevaReservacion = function() {
    $('#id_editando').val(''); // LIMPIAMOS EL ID PARA QUE SEA POST
    $('#formNuevaReservacion')[0].reset();
    $('#modalNuevaSolicitud .modal-title').text('Nueva Reservación');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaSolicitud')).show();
    window.regresarAlCatalogo();
};

window.regresarAlCatalogo = function() { $('#paso_calendario, #paso_formulario').hide(); $('#paso_catalogo').fadeIn(); };
window.manejarSesionExpirada = function() { localStorage.removeItem('sira_session_token'); window.location.href = 'index.php'; };
window.limpiarFiltros = function() { $('.check-filtro').prop('checked', false); cargarMisReservaciones(); };

window.irAlCalendario = function(id, nombre) {
    auditorioSeleccionado = id;
    document.getElementById('display_nombre_auditorio').innerText = nombre;
    document.getElementById('input_id_auditorio').value = id;
    $('#paso_catalogo').hide();
    $('#paso_calendario').fadeIn();
    setTimeout(() => renderizarCalendarioInteractivo(id), 300);
};

window.regresarAlCatalogo = function() {
    $('#paso_calendario, #paso_formulario').hide();
    $('#paso_catalogo').fadeIn();
};

window.irAlFormularioFinal = async function() {
    const idAud = $('#input_id_auditorio').val();
    try {
        const res = await fetch(`api/solicitudes/get_detalle.php?id_auditorio=${idAud}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        const auditorio = await res.json();

        // CONEXIÓN CON HTML (IDs exactos de modal_reservacion.php)
        const imgElement = document.getElementById('img_final_preview'); //
        if (imgElement) {
            imgElement.src = `assets/img/auditorios/${idAud}.jpg`;
            imgElement.onerror = () => imgElement.src = 'assets/img/placeholder.jpg';
        }

        const divEquipamiento = document.getElementById('check_equipamiento_fijo'); //
        if (divEquipamiento) {
            divEquipamiento.innerHTML = `<p class="small mb-0">${auditorio.equipamiento_fijo || 'Mobiliario básico.'}</p>`;
        }

        // Cambio de vista
        $('#paso_calendario').hide();
        $('#paso_formulario').fadeIn();
        document.getElementById('display_nombre_final').innerText = auditorio.nombre_espacio;
        
    } catch (e) {
        Swal.fire('Error', 'No se pudo conectar con el auditorio.', 'error');
    }
};

window.cancelarMiSolicitud = function(id) {
    Swal.fire({
        title: '¿Cancelar?',
        text: "No podrás deshacerlo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EE5253',
        confirmButtonText: 'Sí, cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const res = await fetch(`api/solicitudes/gestion_solicitudes.php?id=${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
            });
            const data = await res.json();
            if(data.success) {
                Swal.fire('Éxito', 'Cancelada.', 'success').then(() => cargarMisReservaciones());
            }
        }
    });
};

function renderizarCalendarioInteractivo(idAuditorio) {
    const calendarEl = document.getElementById('calendar_interactivo');
    if (!calendarEl) return;
    new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', locale: 'es', selectable: true,
        headerToolbar: { left: 'prev,next', center: 'title', right: '' },
        dateClick: function(info) {
            $('.fc-day').removeClass('bg-primary bg-opacity-10');
            $(info.dayEl).addClass('bg-primary bg-opacity-10');
            document.getElementById('fecha_display').value = info.dateStr;
            document.getElementById('input_fecha_evento').value = info.dateStr;
            window.obtenerDisponibilidadHoraria(idAuditorio, info.dateStr);
        }
    }).render();
}

window.manejarSesionExpirada = function() {
    localStorage.removeItem('sira_session_token');
    window.location.href = 'index.php';
};

/**
 * LÓGICA DE FILTRADO DINÁMICO - SIRA UTM
 */
/**
 * LÓGICA DE FILTRADO DINÁMICO POR CHECKBOXES
 */
$(document).on('change', '.check-filtro', function () {
    const table = $('#tablaMisReservas').DataTable();
    let seleccionados = [];

    // 1. Capturamos los valores (Pendiente, Aceptada, Rechazada)
    $('.check-filtro:checked').each(function () {
        seleccionados.push($(this).val());
    });

    if (seleccionados.length > 0) {
        // Creamos la regex: (Pendiente|Aceptada|Rechazada)
        // Usamos búsqueda inteligente para que ignore los <span> del badge
        const regex = seleccionados.join('|');
        
        table.column(4) // Columna ESTATUS
             .search(regex, true, false) 
             .draw();
    } else {
        // Si no hay nada marcado, limpiamos la columna
        table.column(4).search('').draw();
    }
});

// Ejemplo de cómo cargar los datos al saltar al formulario
window.prepararFormularioFinal = function(datosAuditorio) {
    // 1. Cargar imagen (asumiendo que las guardas con el ID del auditorio)
    const rutaImagen = `assets/img/auditorios/${datosAuditorio.id_auditorio}.jpg`;
    document.getElementById('img_auditorio_preview').src = rutaImagen;

    // 2. Mostrar equipamiento fijo (lo que ya incluye el salón)
    document.getElementById('equipamiento_incluido_txt').innerText = datosAuditorio.equipamiento_fijo || 'Sin equipamiento fijo registrado.';
};
/**
 * LIMPIAR FILTROS (Botón Negro)
 */
window.limpiarFiltros = function() {
    // 1. Desmarcar checkboxes
    $('.check-filtro').prop('checked', false);
    
    // 2. Resetear DataTables
    const table = $('#tablaMisReservas').DataTable();
    table.search('').columns().search('').draw();
    
    // 3. Opcional: Recargar los contadores de las cards
    cargarMisReservaciones();
};

window.guardarCierre = async function(id) {
    const texto = document.getElementById('incidente_texto').value;

    if (!texto.trim()) {
        return Swal.showValidationMessage('Por favor, describe el estado en que entregas el auditorio.');
    }

    try {
        const res = await fetch('api/solicitudes/reportar_cierre.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
            },
            body: JSON.stringify({ id: id, incidentes: texto })
        });

        const result = await res.json();
        if (result.success) {
            Swal.fire('¡Gracias!', 'El cierre del auditorio ha sido registrado.', 'success').then(() => {
                location.reload();
            });
        }
    } catch (e) {
        Swal.fire('Error', 'No se pudo registrar el cierre.', 'error');
    }
};