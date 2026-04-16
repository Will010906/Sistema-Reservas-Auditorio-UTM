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
 
});

/**
 * 1. CARGA DE TABLA
 */
/**
 * SINCRONIZACIÓN DE HISTORIAL ACADÉMICO
 * @async
 * @description Recupera el listado de solicitudes del usuario autenticado 
 * y reconstruye la interfaz de seguimiento (Grid y KPIs).
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
        
        if ($.fn.DataTable.isDataTable('#tablaMisReservas')) {
            $('#tablaMisReservas').DataTable().destroy();
        }
        
        contenedor.innerHTML = ""; 

        if (data.success && data.solicitudes.length > 0) {
            data.solicitudes.forEach(sol => {
                // 1. DETECCIÓN DE REASIGNACIÓN (El corazón del aviso)
                const fueReasignado = sol.notificacion_admin == 1;
                const esEditable = sol.estado.toUpperCase() === 'PENDIENTE' && !fueReasignado;

                // 2. CREACIÓN DEL BADGE ROJO PARPADEANTE
                const alertaCambio = fueReasignado 
                    ? `<span class="badge bg-danger animate__animated animate__flash animate__infinite ms-2" 
                             style="font-size: 0.65rem; vertical-align: middle;">
                          <i class="bi bi-bell-fill"></i> ¡REASIGNADO!
                       </span>` 
                    : '';

                contenedor.innerHTML += `
                    <tr class="solicitud-fila animate__animated animate__fadeIn">
                        <td class="ps-4 fw-bold" style="color: #5B3D66;">
                            #${sol.folio} ${alertaCambio}
                        </td>
                        <td>
                            <div class="fw-bold">${sol.titulo_event}</div>
                            <div class="text-muted x-small">${sol.nombre_espacio}</div>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-light text-dark border px-3">
                                ${sol.nombre_espacio}
                            </span>
                        </td>
                        <td class="fw-bold text-muted">${sol.fecha_evento}</td>
                        <td class="text-center">
                            <span class="badge-status st-${sol.estado.toLowerCase()} shadow-sm">
                                ${sol.estado}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                ${fueReasignado ? `
    <div class="btn-group shadow-sm">
       <button class="btn btn-sm btn-success fw-bold me-1" 
        onclick="window.confirmarReasignacionConSwal(${sol.id_solicitud})">
    <i class="bi bi-check-lg"></i> Aceptar
</button>
        <button class="btn btn-sm btn-danger fw-bold" onclick="window.rechazarReasignacion(${sol.id_solicitud})">
            <i class="bi bi-x-lg"></i> Rechazar
        </div>
    </div>
` : ''}
                                <button class="btn btn-sm btn-light border" onclick="window.verDetalleUsuario(${sol.id_solicitud})">
                                    <i class="bi bi-eye-fill text-primary"></i>
                                </button>
                                
                                ${esEditable ? `
                                    <button class="btn btn-sm btn-light border" onclick="window.editarMiSolicitud(${sol.id_solicitud})">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border" onclick="window.cancelarMiSolicitud(${sol.id_solicitud})">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>` : ''}
                            </div>
                        </td>
                    </tr>`;
            });
            
            $('#tablaMisReservas').DataTable({
                retrieve: true,
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                dom: 'rtip',
                pageLength: 10,
                order: [[0, "desc"]],
                columnDefs: [{ targets: [5], orderable: false }]
            });

            if (data.stats) {
                document.getElementById("countPendientes").innerText = data.stats.pendientes || 0;
                document.getElementById("countAprobadas").innerText = data.stats.aprobadas || 0;
                document.getElementById("countRechazadas").innerText = data.stats.rechazadas || 0;
            }
        }
    } catch (error) { 
        console.error("Falla crítica en la sincronización:", error); 
    }
}

/**
 * 2. LÓGICA DE HORARIOS Y GRID
 */
window.obtenerDisponibilidadHoraria = async function(id, fecha) {
    const txt = document.getElementById('fecha_seleccionada_txt');
    const btnConfirmar = document.getElementById('btnConfirmarHorario');
    
    // 1. IMPORTANTE: Detectamos si estamos editando una solicitud existente
    const idExcluir = $('#id_editando').val(); 
    
    horaInicioSeleccionada = null; 
    if(btnConfirmar) btnConfirmar.disabled = true;
    document.getElementById('input_hora_fin').value = "";

    try {
        txt.innerText = "Consultando...";
        
        // 2. Construimos la URL. Si idExcluir tiene valor, lo añadimos como parámetro
        let url = `api/solicitudes/get_disponibilidad.php?id=${id}&fecha=${fecha}`;
        if (idExcluir) {
            url += `&id_excluir=${idExcluir}`;
        }

        const response = await fetch(url, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        
        const data = await response.json();
        txt.innerText = `Horarios para el ${fecha}:`;
        
        // 3. Generamos la cuadrícula (ahora vendrá sin el bloqueo de tu propia reserva)
        generarGridHorarios(data); 
        
    } catch (e) {
        txt.innerText = "Error de conexión.";
        console.error("Error al obtener disponibilidad:", e);
    }
};
function generarGridHorarios(ocupados) {
    const grid = document.getElementById('grid_horarios');
    const txtResumen = document.getElementById('fecha_seleccionada_txt'); 
    const msjError = document.getElementById('msj_error_rango');
    
    grid.style.setProperty('display', 'flex', 'important'); 
    grid.innerHTML = "";
    if(msjError) $(msjError).hide();

    const fechaInput = document.getElementById('input_fecha_evento').value;
    if (fechaInput) {
        const opciones = { weekday: 'long', day: 'numeric', month: 'long' };
        const fechaObj = new Date(fechaInput + 'T00:00:00');
        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);
        const inputDisplay = document.getElementById('fecha_display');
        if(inputDisplay) inputDisplay.value = fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);
    }

    for (let h = 7; h <= 20; h++) { 
        const hInicioRaw = `${h.toString().padStart(2, '0')}:00`;
        const hTexto = `${h > 12 ? h - 12 : h}:00 ${h >= 12 ? 'PM' : 'AM'}`;
        
        const estaOcupado = ocupados.some(o => (hInicioRaw >= o.inicio && hInicioRaw < o.fin));

        const btn = document.createElement('button');
        btn.type = "button";
        btn.id = `btn-hora-${h}`; 
        btn.className = `btn btn-horario ${estaOcupado ? 'ocupado disabled' : ''}`;

        if (estaOcupado) {
            btn.innerHTML = `<i class="bi bi-lock-fill"></i> ${hTexto}`;
            btn.style.backgroundColor = "#ffe5e5"; 
            btn.style.color = "#d9534f";
            btn.style.border = "1px solid #f5c2c7";
            btn.style.cursor = "not-allowed";
        } else {
            btn.innerHTML = hTexto;
            btn.onclick = () => {
                const inputFin = document.getElementById('input_hora_fin');
                const btnConfirmar = document.getElementById('btnConfirmarHorario');

                if (inputFin.value !== "" && horaInicioSeleccionada === null) {
                    document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('activo'));
                    inputFin.value = "";
                    btnConfirmar.disabled = true;
                }

                if (horaInicioSeleccionada === null) {
                    horaInicioSeleccionada = h;
                    document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('activo'));
                    btn.classList.add('activo');
                    document.getElementById('input_hora_inicio').value = hInicioRaw;
                    btnConfirmar.disabled = true; 
                    txtResumen.innerHTML = `Inicia: <b>${hTexto}</b>. Selecciona la hora de salida.`;
                } else {
                    if (h === horaInicioSeleccionada) {
                        horaInicioSeleccionada = null;
                        document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('activo'));
                        inputFin.value = "";
                        btnConfirmar.disabled = true;
                        txtResumen.innerText = "Selección cancelada.";
                        return;
                    }

                    if (h > horaInicioSeleccionada) {
                        document.querySelectorAll('.btn-horario').forEach(b => b.classList.remove('activo'));
                        for (let i = horaInicioSeleccionada; i <= h; i++) {
                            const bRango = document.getElementById(`btn-hora-${i}`);
                            if (bRango) bRango.classList.add('activo');
                        }

                        // --- 🟢 LÍNEA CLAVE: SUMAMOS 1 PARA LA HORA DE SALIDA REAL ---
                        // --- 🟢 CORRECCIÓN DE VARIABLES (Línea 170+) ---
const horaFinReal = h; 
const hInicioNum = parseInt(document.getElementById('input_hora_inicio').value.split(':')[0]);

// Definimos hFinTexto correctamente para que no salga "not defined"
const hFinTexto = `${horaFinReal > 12 ? horaFinReal - 12 : horaFinReal}:00 ${horaFinReal >= 12 ? 'PM' : 'AM'}`;
const hInicioDisplay = `${hInicioNum > 12 ? hInicioNum - 12 : hInicioNum}:00 ${hInicioNum >= 12 ? 'PM' : 'AM'}`;

// Guardamos en formato 24h para la base de datos
document.getElementById('input_hora_fin').value = `${horaFinReal.toString().padStart(2, '0')}:00`;

// 🟢 Aquí usamos hFinTexto que ya está definida arriba
txtResumen.innerHTML = `<i class="bi bi-clock-history me-1"></i> Rango: <b>${hInicioDisplay}</b> a <b>${hFinTexto}</b>`;

btnConfirmar.disabled = false; 
btnConfirmar.style.opacity = "1"; 
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
    
    const idEditando = $('#id_editando').val();
    const esEdicion = $('#modalNuevaSolicitud .modal-title').text().includes('Modificar') || 
                      $('#modalNuevaSolicitud .modal-title').text().includes('Reasignar');
    
    if (esEdicion && !idEditando) {
        return Swal.fire('Error', 'No se detectó el ID de la reservación para editar.', 'error');
    }

    const metodo = idEditando ? 'PUT' : 'POST';
    const checks = Array.from(document.querySelectorAll('input[name="extras[]"]:checked')).map(el => el.value);
    const textoExtra = $('input[name="otros_servicios"]').val().trim();
    
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
        otros_servicios: serviciosFinales 
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
            /**
             * SOLUCIÓN AL ERROR VISUAL:
             * 1. Cerramos el modal primero para liberar el z-index.
             */
            const modalElem = document.getElementById('modalNuevaSolicitud');
            const instance = bootstrap.Modal.getInstance(modalElem);
            if (instance) instance.hide();

            /**
             * 2. Limpieza agresiva del DOM para que el SweetAlert sea visible.
             */
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = 'auto';
            document.body.style.paddingRight = '0';

            /**
             * 3. Ahora el recuadrito de éxito saldrá al frente sin problemas.
             */
            Swal.fire({ 
                title: '¡Éxito!', 
                text: result.message || 'Operación completada.', 
                icon: 'success',
                confirmButtonColor: '#5B3D66'
            }).then(() => {
                location.reload(); 
            });
        } else {
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

    // 1. CARGA DE IDENTIDAD (Evitamos undefined)
    auditorioSeleccionado = data.id_auditorio; 
    $('#input_id_auditorio').val(data.id_auditorio); 
    $('#display_nombre_auditorio').text(data.nombre_espacio); 

    // 2. LLENADO DE FORMULARIO (Datos persistentes)
    $('#modalNuevaSolicitud .modal-title').text('Modificar Reservación');
    $('input[name="titulo"]').val(data.titulo_event);
    $('textarea[name="descripcion"]').val(data.descripcion);
    $('input[name="num_asistentes"]').val(data.num_asistentes);
    
    // Resumen lateral
    $('#display_nombre_final').text(data.nombre_espacio); 
    $('#capacidad_numero_txt').text(data.capacidad_maxima);
    
    const imgPreview = document.getElementById('img_final_preview');
    if (imgPreview) {
        imgPreview.src = `assets/img/auditorios/${data.id_auditorio}.jpg`;
        imgPreview.onerror = () => imgPreview.src = 'assets/img/placeholder.jpg';
    }

    // 3. LÓGICA DE EXTRAS (Checkboxes y manual)
    document.querySelectorAll('input[name="extras[]"]').forEach(cb => cb.checked = false);
    $('input[name="otros_servicios"]').val('');

    if (data.otros_servicios) {
        const serviciosPrevios = data.otros_servicios.split(', ');
        const valoresCheckboxes = ['Proyector', 'Extensiones', 'Mobiliario', 'Microfono', 'Cafetera', 'Manteles', 'Insumos'];
        let textosManuales = [];

        serviciosPrevios.forEach(item => {
            const itemLimpio = item.trim();
            if (valoresCheckboxes.includes(itemLimpio)) {
                const cb = Array.from(document.querySelectorAll('input[name="extras[]"]'))
                                .find(c => c.value === itemLimpio);
                if (cb) cb.checked = true;
            } else if(itemLimpio !== "") {
                textosManuales.push(itemLimpio);
            }
        });
        $('input[name="otros_servicios"]').val(textosManuales.join(', '));
    }

    // 4. GESTIÓN DE ID (Modo Edición Activo)
    if($('#id_editando').length === 0) {
        $('#formNuevaReservacion').append(`<input type="hidden" id="id_editando" name="id_editando" value="${id}">`);
    } else {
        $('#id_editando').val(id);
    }

    // 5. 🟢 NAVEGACIÓN VISUAL: INICIO DESDE EL CATÁLOGO
    // Ocultamos los pasos avanzados y mostramos el Paso 1
    $('#paso_calendario, #paso_formulario').hide(); 
    $('#paso_catalogo').fadeIn(); 
    
    // Cambiamos el estilo del botón para que diga "Confirmar Movimiento"
    actualizarInterfazBotonSIRA(); 

    // Mostramos el modal
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaSolicitud')).show();

    // 6. AVISO TOAST (Opcional para UX)
    Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    }).fire({
        icon: 'info',
        title: 'Modo Edición',
        text: 'Por favor, confirma o cambia el auditorio.'
    });
};

window.verDetalleUsuario = async function(id) {
    const res = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
    });
    const data = await res.json();

    const hInicio = data.hora_inicio ? data.hora_inicio.substring(0, 5) : '--:--';
    const hFin = data.hora_fin ? data.hora_fin.substring(0, 5) : '--:--';
    const estado = data.estado ? data.estado.toUpperCase() : 'PENDIENTE';

    let htmlContent = `
        <div class="text-start px-1">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="fw-900 mb-1" style="color: #5B3D66; font-weight: 900; font-size: 1.4rem;">${data.titulo_event}</h5>
                    <span class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">FOLIO: #${data.folio}</span>
                </div>
                <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-bold shadow-sm" style="font-size: 0.65rem;">
                    <i class="bi bi-clock-history me-1"></i> ${estado}
                </span>
            </div>

            <div class="rounded-4 p-3 mb-4 shadow-sm border" style="background: linear-gradient(135deg, #ffffff 0%, #f9f6fa 100%); border-left: 6px solid #5B3D66 !important;">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Espacio Asignado:</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-building-fill text-primary me-2 fs-5"></i>
                            <span class="fw-bold text-dark" style="font-size: 1.1rem;">${data.nombre_espacio}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Fecha del Evento:</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar3 text-primary me-2"></i>
                            <span class="fw-bold">${data.fecha_evento}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.6rem; letter-spacing: 1px;">Horario:</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock-fill text-primary me-2"></i>
                            <span class="fw-bold">${hInicio} - ${hFin}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded-4 bg-light border">
                <label class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.6rem; letter-spacing: 1px;">Notas Administrativas:</label>
                <p class="mb-0 italic text-dark" style="font-size: 0.9rem; line-height: 1.5;">
                    <i class="bi bi-quote fs-4 text-secondary opacity-25"></i>
                    ${data.notas_admin || "Su solicitud está siendo procesada por la administración de la UTM."}
                </p>
            </div>
        </div>`;

    Swal.fire({
        html: htmlContent,
        showConfirmButton: false,
        showCloseButton: true,
        width: '500px',
        customClass: {
            container: 'sira-detail-modal',
            popup: 'rounded-5'
        }
    });
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
    $('#id_editando').val('');
    actualizarInterfazBotonSIRA();
    $('#formNuevaReservacion')[0].reset();
    limpiarSeleccionHorario(); // <--- Y esto aquí
    $('#modalNuevaSolicitud .modal-title').text('Nueva Reservación');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevaSolicitud')).show();
    window.regresarAlCatalogo();
};

window.regresarAlCatalogo = function()  {limpiarSeleccionHorario(); $('#paso_calendario, #paso_formulario').hide(); $('#paso_catalogo').fadeIn(); };
window.manejarSesionExpirada = function() { localStorage.removeItem('sira_session_token'); window.location.href = 'index.php'; };
window.limpiarFiltros = function() { $('.check-filtro').prop('checked', false); cargarMisReservaciones(); };

window.irAlCalendario = function(id, nombre) {
    // 1. Limpiamos ruidos visuales previos
    resetearInterfazHorarios(); 
    auditorioSeleccionado = id;
    
    // 2. 🟢 LA CLAVE: Vaciamos el contenedor del calendario
    const calendarEl = document.getElementById('calendar_interactivo');
    if (calendarEl) calendarEl.innerHTML = ""; 

    // 3. Cambiamos de paso en el modal
    $('#paso_catalogo, #paso_formulario').hide(); 
    $('#paso_calendario').fadeIn();
    
    // 4. Actualizamos el ID en el input oculto
    const inputId = document.getElementById('input_id_auditorio');
    if(inputId) inputId.value = id;

    // 5. Renderizamos el calendario "fresco"
    setTimeout(() => renderizarCalendarioInteractivo(id), 300);
};

window.regresarAlCatalogo = function() {
    $('#paso_calendario, #paso_formulario').hide();
    $('#paso_catalogo').fadeIn();
};

window.irAlFormularioFinal = async function() {
    let idAud = $('#input_id_auditorio').val() || auditorioSeleccionado;
    
    if (!idAud || idAud === "0" || idAud === "") {
        Swal.fire('Atención', 'No se detectó el auditorio.', 'warning');
        return;
    }

    try {
        const res = await fetch(`api/solicitudes/get_detalle.php?id_auditorio=${idAud}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        
        const auditorio = await res.json();
        if (!auditorio || auditorio.error) return;

        const nombreReal = auditorio.nombre_espacio || auditorio.nombre || 'Auditorio';
        const capacidadReal = parseInt(auditorio.capacidad_maxima || auditorio.capacidad || '0');

        document.getElementById('display_nombre_auditorio').innerText = nombreReal;
        document.getElementById('display_nombre_final').innerText = nombreReal;
        
        const txtCapacidad = document.getElementById('capacidad_numero_txt');
        if (txtCapacidad) txtCapacidad.innerText = capacidadReal;

        // --- 🟢 BLOQUE DE RESTRICCIÓN PARA REASIGNACIÓN (Punto 5 Mejorado) ---
        const inputAsis = document.getElementById('num_asistentes_input');
      if (inputAsis) {
        // 🟢 Permitimos la edición
        inputAsis.readOnly = false; 
        
        // 🟢 Actualizamos el límite físico
        inputAsis.max = capacidadReal; 

        // Si el valor actual de la reservación vieja supera al nuevo auditorio, 
        // lo ajustamos automáticamente al máximo para evitar errores
        if (parseInt(inputAsis.value) > capacidadReal) {
            inputAsis.value = capacidadReal;
            Swal.fire({
                icon: 'info',
                title: 'Ajuste de Asistentes',
                text: `La capacidad se ajustó a ${capacidadReal} (límite del nuevo espacio).`,
                confirmButtonColor: '#5B3D66'
            });
        }
    }
        // --------------------------------------------------------------------

        // 3. IMAGEN PREVIEW
        const imgElement = document.getElementById('img_final_preview');
        if (imgElement) {
            imgElement.src = `assets/img/auditorios/${idAud}.jpg`;
            imgElement.onerror = () => imgElement.src = 'assets/img/placeholder.jpg';
        }

        // 4. LÓGICA DE ICONOS PARA "INCLUYE"
        const divEquipamiento = document.getElementById('check_equipamiento_fijo');
        if (divEquipamiento) {
            const lista = (auditorio.equipamiento_fijo || '').split(',');
            let htmlIconos = '<div class="mt-1">';
            if (lista.length > 0 && lista[0].trim() !== "") {
                lista.forEach(item => {
                    htmlIconos += `
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-check2-circle text-primary me-2" style="font-size: 0.8rem;"></i>
                            <span class="text-dark" style="font-size: 0.75rem; font-weight: 500;">${item.trim()}</span>
                        </div>`;
                });
            } else {
                htmlIconos += '<span class="text-muted small">Mobiliario básico.</span>';
            }
            htmlIconos += '</div>';
            divEquipamiento.innerHTML = htmlIconos;
        }

        // 6. CAMBIO DE VISTA
        $('#paso_calendario').hide();
        $('#paso_formulario').fadeIn();
        
    } catch (e) {
        console.error("Error crítico:", e);
        Swal.fire('Error', 'No se pudieron cargar los datos del auditorio.', 'error');
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

    // 1. Obtenemos la fecha de hoy en formato ISO (YYYY-MM-DD)
    const hoy = new Date().toISOString().split('T')[0];

    new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    // --- AGREGA ESTAS LÍNEAS ---
    dayMaxEvents: true, 
    showNonCurrentDates: true, // Muestra días del mes anterior/siguiente
    fixedMirror: true,
    contentHeight: 'auto',
    // ---------------------------
    validRange: { start: hoy },

        headerToolbar: { left: 'prev,next', center: 'title', right: '' },
        
        dateClick: function(info) {
            // --- MEJORA 2: Validación Lógica de seguridad ---
            if (info.dateStr < hoy) {
                // Si por alguna razón logran hacer clic en un día pasado, no hacemos nada
                return false; 
            }

            $('.fc-day').removeClass('bg-primary bg-opacity-10');
            $(info.dayEl).addClass('bg-primary bg-opacity-10');
            
            document.getElementById('fecha_display').value = info.dateStr;
            document.getElementById('input_fecha_evento').value = info.dateStr;
            
            // Consultamos disponibilidad solo para fechas válidas
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
// --- MOTOR DE FILTRADO TÁCTICO SIRA (ESCANEO TOTAL) ---
// --- MOTOR DE FILTRADO TÁCTICO SIRA (ESCANEO TOTAL) ---
$(document).on('change', '.check-filtro', function () {
    const table = $('#tablaMisReservas').DataTable();
    
    // 1. Limpiamos cualquier rastro de filtros anteriores
    $.fn.dataTable.ext.search = [];

    // 2. Capturamos lo que el usuario marcó (Normalizado: MAYÚSCULAS y sin 'S')
    const seleccionados = $('.check-filtro:checked').map(function() {
        return $(this).val().toUpperCase().replace(/S$/, '').trim();
    }).get();

    console.log("Filtros UTM activos:", seleccionados);

    // 3. Si hay algo seleccionado, aplicamos el escáner de fila
    if (seleccionados.length > 0) {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            // Unimos todo el texto de la fila (Folio, Título, Auditorio, Estatus)
            const textoCompletoFila = data.join(" ").toUpperCase();
            
            // Si alguna palabra seleccionada está en el texto de la fila, se muestra.
            return seleccionados.some(s => textoCompletoFila.includes(s));
        });
    }

    // 4. Redibujamos la tabla
    table.draw();
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
 * RESTAURACIÓN DE VISTA ORIGINAL SIRA
 * @description Desactiva filtros activos y reinicia el motor de búsqueda de DataTables.
 */
window.limpiarFiltros = function() {
    // 1. Desmarcar físicamente todos los checkboxes de la interfaz
    $('.check-filtro').prop('checked', false);
    
    // 2. LIMPIEZA DE MOTOR: Vaciamos el array de búsqueda personalizada
    // Esto es vital para que la tabla "vuelva a mirar" todos los registros.
    $.fn.dataTable.ext.search = [];
    
    // 3. RESET DE DATATABLES: Limpiamos búsquedas internas y redibujamos
    const table = $('#tablaMisReservas').DataTable();
    table.search('').columns().search('').draw();
    
    // 4. OPCIONAL: Sincronizamos KPIs por si hubo cambios en el servidor
    cargarMisReservaciones();

    console.log("Sinergia restaurada: Filtros eliminados.");
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

window.limpiarSeleccionHorario = function() {
    // 1. Reiniciar variables lógicas
    horaInicioSeleccionada = null;

    // 2. Limpiar inputs visuales y ocultos
    const inputs = ['input_hora_inicio', 'input_hora_fin', 'fecha_display', 'input_fecha_evento'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = "";
    });

    // 3. Resetear textos de ayuda
    const txtResumen = document.getElementById('fecha_seleccionada_txt');
    if (txtResumen) txtResumen.innerText = "Selecciona un día para ver bloques";

    // 4. Desactivar botón de confirmar
    const btnConfirmar = document.getElementById('btnConfirmarHorario');
    if (btnConfirmar) btnConfirmar.disabled = true;

    // 5. Quitar clases "activo" de todos los botones de la grid
    document.querySelectorAll('.btn-horario').forEach(btn => {
        btn.classList.remove('activo');
    });
    
    // 6. Limpiar selección visual en el calendario (FullCalendar)
    $('.fc-day').removeClass('bg-primary bg-opacity-10');
};

/**
 * RESETEO INTEGRAL DE LA CAPA DE TEMPORALIDAD
 * Limpia los ruidos visuales para evitar colisiones con el Dashboard.
 */
function resetearInterfazHorarios() {
    // 1. Reiniciar estados lógicos
    horaInicioSeleccionada = null;

    // 2. Limpiar inputs del formulario
    const campos = ['fecha_display', 'input_fecha_evento', 'input_hora_inicio', 'input_hora_fin'];
    campos.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = "";
    });

    // 3. Ocultar y vaciar la grid de horas
    const grid = document.getElementById('grid_horarios');
    if (grid) {
        grid.style.setProperty('display', 'none', 'important');
        grid.innerHTML = ""; 
    }

    /** * CORRECCIÓN DE SINERGIA:
     * Apuntamos EXCLUSIVAMENTE al ID del modal para no secuestrar la tarjeta naranja.
     */
    const txtResumen = document.getElementById('modal_ayuda_calendario'); // ID BLINDADO
    if (txtResumen) {
        txtResumen.innerText = "Selecciona un día para ver bloques";
    }

    const btnConfirmar = document.getElementById('btnConfirmarHorario');
    if (btnConfirmar) btnConfirmar.disabled = true;

    // 4. Limpiar rastro visual en el calendario
    $('.fc-day').removeClass('bg-primary bg-opacity-10');
}

window.confirmarCambioAdmin = async function(id) {
    const res = await fetch('api/solicitudes/confirmar_notificacion.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
        },
        body: JSON.stringify({ id: id })
    });
    
    const data = await res.json();
    if(data.success) {
        Swal.fire({
            title: '¡Confirmado!',
            text: 'Has aceptado los nuevos términos de tu reservación.',
            icon: 'success',
            confirmButtonColor: '#5B3D66'
        }).then(() => {
            cargarMisReservaciones(); // Recargamos la tabla para que desaparezca el badge rojo
        });
    }
};

window.rechazarReasignacion = async function(id) {
    const confirmacion = await Swal.fire({
        title: '¿Rechazar cambio?',
        text: "Al rechazar, tu solicitud será cancelada permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar solicitud',
        cancelButtonText: 'Regresar'
    });

    if (confirmacion.isConfirmed) {
        try {
            const res = await fetch(`api/solicitudes/gestion_solicitudes.php?id=${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
            });
            const data = await res.json();
            if(data.success) {
                Swal.fire('Cancelada', 'La solicitud ha sido eliminada.', 'success')
                    .then(() => cargarMisReservaciones());
            }
        } catch (e) {
            console.error("Error al rechazar:", e);
        }
    }
};

window.confirmarReasignacionConSwal = async function(id) {
    // 1. Mostrar la confirmación visual (como en tu imagen)
    const result = await Swal.fire({
        title: '¿Confirmar Nuevos Términos?',
        text: "Al aceptar, tu reservación quedará asegurada con el nuevo auditorio y horario.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2e7d32', // Un verde UTM
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, Aceptar Cambio',
        cancelButtonText: 'Revisar'
    });

    // 2. Si el usuario acepta, procedemos al backend
    if (result.isConfirmed) {
        Swal.showLoading(); // Mostramos el cargador para que espere
        
        try {
            // Reutilizamos la función que ya teníamos para llamar al PHP
            const res = await fetch('api/solicitudes/confirmar_notificacion.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
                },
                body: JSON.stringify({ id: id })
            });
            
            const data = await res.json();
            if(data.success) {
                // Alerta de éxito final
                Swal.fire({
                    title: '¡Sinergia Exitosa!',
                    text: 'Tu reservación ha sido confirmada.',
                    icon: 'success',
                    confirmButtonColor: '#5B3D66'
                }).then(() => {
                    cargarMisReservaciones(); // Recargamos la tabla para que desaparezca el aviso
                });
            } else {
                Swal.fire('Error', 'No se pudo confirmar la reservación.', 'error');
            }
        } catch (e) {
            Swal.fire('Error de Conexión', 'Fallo al conectar con el servidor SIRA.', 'error');
        }
    }
};


async function enviarNuevaSolicitud() {
    // 1. Extraemos los datos del formulario
    const formulario = document.getElementById('formNuevaReservacion');
    const fd = new FormData(formulario);
    const objetoDatos = Object.fromEntries(fd.entries());

    // --- 🟢 BLOQUE DE RESTRICCIONES DE SEGURIDAD ---
    // Validamos manualmente antes del fetch para que no se creen folios vacíos
    if (!objetoDatos.titulo || objetoDatos.titulo.trim() === "" || 
        !objetoDatos.num_asistentes || objetoDatos.num_asistentes <= 0 ||
        !objetoDatos.descripcion || objetoDatos.descripcion.trim() === "") {
        
        return Swal.fire({
            icon: 'error',
            title: 'Datos Incompletos',
            text: 'Debes llenar el título, la cantidad de asistentes y la descripción.',
            confirmButtonColor: '#5B3D66'
        });
    }

    console.log("SIRA - Enviando datos validados:", objetoDatos);

    try {
        const response = await fetch('api/solicitudes/guardar_solicitud.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` 
            },
            body: JSON.stringify(objetoDatos)
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: '¡Reservación Creada!',
                text: `Tu folio es: ${data.folio}`,
                icon: 'success',
                confirmButtonColor: '#5B3D66'
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.error, 'error');
        }
    } catch (error) {
        console.error("Falla de red:", error);
        Swal.fire('Error', 'No se pudo conectar con el servidor UTM.', 'error');
    }
}

window.procesarSolicitudSIRA = function(event) {
    if (event) event.preventDefault();

    // 1. RECOGER DATOS
    const hInicio = document.getElementById('input_hora_inicio').value;
    const hFin = document.getElementById('input_hora_fin').value;
    const fecha = document.getElementById('input_fecha_evento').value;

    // 2. 🟢 BLOQUE DE SEGURIDAD PARA LAS HORAS (Evita el 00:00)
    if (!fecha || !hInicio || !hFin || hInicio === "00:00" || hFin === "00:00") {
        Swal.fire({
            icon: 'warning',
            title: 'Horario Requerido',
            text: 'Por favor, selecciona un día y un rango de horas en el calendario.',
            confirmButtonColor: '#5B3D66'
        });
        return; // Detenemos el envío si no hay horas reales
    }

    // ... (Mantén aquí tus validaciones de título, asistentes y descripción)

    const campoId = document.getElementById('id_editando');
    const idEdicion = campoId ? campoId.value : "";
    const esAdmin = window.location.href.includes('panel_admin');

    if (idEdicion && idEdicion !== "0") {
        if (esAdmin) {
            confirmarReasignacionFinal(); 
        } else {
            // 🟢 Al usar trigger, el navegador enviará los datos actuales de los inputs
            $('#formNuevaReservacion').trigger('submit');
        }
    } else {
        enviarNuevaSolicitud(); 
    }
};

window.actualizarInterfazBotonSIRA = function() {
    const btn = document.getElementById('btnConfirmarGeneral');
    const idEdicion = $('#id_editando').val();
    
    if (!btn) return;

    if (idEdicion && idEdicion !== "0" && idEdicion !== "") {
        // --- MODO EDICIÓN (Personalizado con JS) ---
        btn.innerHTML = 'Confirmar Cambios <i class="bi bi-pencil-square ms-1"></i>';
        
        // Aplicamos el color directamente
        btn.style.backgroundColor = "#8E6B9E"; // Morado claro para edición
        btn.style.color = "#white";
        btn.style.border = "none";
        
        // Removemos clases que puedan estorbar
        btn.classList.remove('btn-warning', 'btn-enviar-sira', 'text-dark');
    } else {
        // --- MODO NUEVA SOLICITUD (Vuelve al original) ---
        btn.innerHTML = 'Enviar Solicitud SIRA <i class="bi bi-send-fill ms-1"></i>';
        
        // Limpiamos los estilos directos para que use su clase de CSS normal
        btn.style.backgroundColor = ""; 
        btn.style.color = "";
        btn.style.border = "";
        
        btn.classList.add('btn-enviar-sira');
    }
};

// Agrega esto a tu archivo JS de reservaciones
document.getElementById('num_asistentes_input').addEventListener('input', function() {
    // Obtenemos la capacidad máxima actual desde el resumen visual
    const capacidadMax = parseInt(document.getElementById('capacidad_numero_txt').innerText);
    const valorIngresado = parseInt(this.value);

    if (valorIngresado > capacidadMax) {
        // Bloqueamos el exceso
        this.value = capacidadMax;
        
        // Alerta visual para el usuario
        Swal.fire({
            icon: 'warning',
            title: 'Capacidad Excedida',
            text: `El auditorio seleccionado solo permite un máximo de ${capacidadMax} personas.`,
            confirmButtonColor: '#5B3D66',
            target: document.getElementById('modalNuevaSolicitud')
        });
    }
});