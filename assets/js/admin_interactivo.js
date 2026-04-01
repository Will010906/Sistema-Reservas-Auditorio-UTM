/**
 * LÓGICA INTERACTIVA DEL PANEL - NIVEL TSU
 * Estado: Producción Segura (JWT + Async/Await)
 */

/* global Swal, bootstrap */

let idSeleccionado = null;
let bsModal = null;
let idSolicitudActualAdmin = null; // Variable global para reasignación

/**
 * GESTIÓN DE DETALLES - Abre el modal de información
 */
async function gestionar(id) {
    idSeleccionado = id;
    
    // Guardamos el ID para la posible reasignación
    idSolicitudActualAdmin = id; 

    if (!bsModal) {
        const modalElement = document.getElementById("bsModalDetalle");
        if (modalElement) bsModal = new bootstrap.Modal(modalElement);
    }

    try {
        const response = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`,
                'Content-Type': 'application/json'
            }
        });

        if (response.status === 401) return manejarErrorAutenticacion();
        const data = await response.json();

        // 1. LLENADO DEL BOTÓN DE WHATSAPP (Solución al error)
        const btnWhatsApp = document.getElementById("btnWhatsApp");
        if (btnWhatsApp && data.telefono) {
            // Limpiamos el número (solo dejamos dígitos)
            const telLimpio = data.telefono.replace(/\D/g, '');
            // Creamos el mensaje predeterminado
            const msj = encodeURIComponent(`Hola ${data.nombre_usuario}, te contacto del sistema SIRA UTM sobre tu reservación con folio ${data.folio}...`);
            // Asignamos el link (Asumiendo lada +52 de México)
            btnWhatsApp.href = `https://wa.me/52${telLimpio}?text=${msj}`;
            btnWhatsApp.style.display = 'flex'; // Aseguramos que se vea
        } else if (btnWhatsApp) {
            btnWhatsApp.style.display = 'none'; // Si no hay teléfono, lo ocultamos
        }

        // 2. Llenado de textos básicos
        document.getElementById("detMatricula").innerText = data.matricula || 'N/A';
        document.getElementById("detCorreo").innerText = data.correo || 'N/A';
        document.getElementById("detFolio").innerText = "Folio: " + (data.folio || 'N/A');
        document.getElementById("detTituloEv").innerText = data.titulo_event;
        
        const nombre = data.nombre_usuario || data.nombre || 'Sin nombre';
        const rol = data.perfil ? `(${data.perfil.toUpperCase()})` : ''; 
        document.getElementById("detUsuarioNombre").innerText = `${nombre} ${rol}`;

        const carreraElem = document.getElementById("detCarrera");
        if (carreraElem) {
            carreraElem.innerText = data.carrera || 'Carrera no especificada';
        }
        
        document.getElementById("detAuditorio").innerText = data.nombre_espacio;
        document.getElementById("detAsistentes").innerText = `👥 ${data.num_asistentes || 0} asistentes aprox.`;
        document.getElementById("detFechaEvento").innerText = data.fecha_evento_limpia || data.fecha_evento;
        document.getElementById("detHorario").innerText = `${data.hora_inicio} a ${data.hora_fin}`;
        document.getElementById("detDescription").innerText = data.descripcion;

        // 3. EQUIPAMIENTO (EXTRAS)
        const contenedorEquipos = document.getElementById("detEquipamiento");
        const extras = data.extras_texto || data.otros_servicios || '';

        if (contenedorEquipos) {
            contenedorEquipos.innerHTML = ""; 

            if (extras && extras.trim() !== "" && extras.toLowerCase() !== 'sin requerimientos extras') {
                const listaExtras = extras.split(',').map(item => item.trim());
                const listaUnica = [...new Set(listaExtras)];

                contenedorEquipos.innerHTML = listaUnica.map(e => 
                    `<span class="badge bg-light text-dark border me-1 shadow-sm" style="padding: 5px 10px;">${e}</span>`
                ).join('');
            } else {
                contenedorEquipos.innerHTML = '<span class="text-muted small italic">Sin requerimientos extras.</span>';
            }
        }

        // 4. Bitácora y Botones de Acción
        const seccionBitacora = document.getElementById("seccionBitacoraAdmin");
        if (seccionBitacora) {
            seccionBitacora.style.display = (data.estado === 'Pendiente' || data.incidentes_cierre) ? "block" : "none";
            document.getElementById("detBitacoraTexto").innerText = data.incidentes_cierre ? `"${data.incidentes_cierre}"` : "Esperando reporte de cierre del solicitante.";
        }

        bsModal.show();

    } catch (error) {
        console.error("Error:", error);
        Swal.fire('Error', 'No se pudo obtener la información.', 'error');
    }
}

/**
 * MOTOR DE FILTRADO (Corregido para Badges Dinámicos)
 */
function aplicarFiltros() {
    const inicio = document.getElementById("fecha_inicio")?.value;
    const fin = document.getElementById("fecha_fin")?.value;
    const chkTodos = document.getElementById("chkTodos");
    const todosActivo = chkTodos ? chkTodos.checked : true;

    // Array de estatus seleccionados (ej: ["URGENTE", "DEMORADA"])
    const seleccionados = todosActivo 
        ? [] 
        : Array.from(document.querySelectorAll(".filter-check:checked")).map(cb => cb.value.toUpperCase());

    const filas = document.querySelectorAll(".solicitud-fila");

    filas.forEach((fila) => {
        // 1. Filtro de Fechas
        const celdaFecha = fila.querySelector(".date-cell")?.innerText.trim();
        let cumpleFecha = true;
        if (inicio && fin) cumpleFecha = (celdaFecha >= inicio && celdaFecha <= fin);

        // 2. Filtro de Semáforo (Basado en el texto del Badge)
        const badge = fila.querySelector(".badge-status");
        let cumpleEstatus = true;

        if (badge && !todosActivo) {
            const textoEstado = badge.innerText.trim().toUpperCase();
            // Comprobamos si el texto del semáforo está en la lista de marcados
            cumpleEstatus = seleccionados.includes(textoEstado);
        }

        // Aplicar visibilidad final
        fila.style.display = (cumpleFecha && cumpleEstatus) ? "" : "none";
    });
}

/**
 * CARGAR DASHBOARD
 */
async function cargarDashboard() {
    try {
        const response = await fetch('api/solicitudes/get_solicitudes.php', {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });

        if (response.status === 401) return manejarErrorAutenticacion();

        const data = await response.json();

        // Llenar contadores de las Cards
        if (data.stats) {
            document.getElementById("countUrgentes").innerText   = data.stats.urgentes || 0;
            document.getElementById('countDemoradas').innerText = data.stats.demoradas;
            document.getElementById("countAtiempo").innerText     = data.stats.atiempo || 0;
            document.getElementById("countAceptadas").innerText   = data.stats.aceptadas || 0;
            document.getElementById("countRechazadas").innerText  = data.stats.rechazadas || 0;
        }

        // Llenar tabla dinámicamente
        const contenedor = document.getElementById("contenedorSolicitudes");
        if (contenedor && data.solicitudes) {
            contenedor.innerHTML = "";
           data.solicitudes.forEach(sol => {
    // Definimos la clase de color según la prioridad visual
    // Dentro de data.solicitudes.forEach(sol => { ...

// Creamos la clase CSS: st-urgente, st-atiempo, st-aceptada, etc.
const claseStatus = sol.prioridad_visual.replace(" ", "").toLowerCase(); 

contenedor.innerHTML += `
    <tr class="solicitud-fila">
        <td class="ps-4 fw-bold" style="color: #5B3D66;">#${sol.folio}</td>
        <td>
            <div class="fw-bold">${sol.titulo_event}</div>
            <div class="text-muted x-small">Por: ${sol.nombre_usuario || 'Docente UTM'}</div>
        </td>
        <td><span class="badge rounded-pill bg-light text-dark border px-3">${sol.nombre_espacio || 'Auditorio'}</span></td>
        <td class="fw-bold text-muted date-cell">${sol.fecha_evento}</td>
        <td class="text-center">
            <span class="badge-status st-${claseStatus} shadow-sm">
                ${sol.prioridad_visual.toUpperCase()}
            </span>
        </td>
        <td class="text-center">
            <button class="btn btn-gestionar-sira btn-sm shadow-sm" onclick="gestionar(${sol.id_solicitud})">
                <i class="bi bi-folder2-open me-1"></i> Gestionar
            </button>
        </td>
    </tr>`;
});
        }
        
        aplicarFiltros(); 

    } catch (error) {
        console.error("Error al cargar dashboard:", error);
    }
}

/**
 * AUXILIAR: Seguridad
 */
function manejarErrorAutenticacion() {
    localStorage.removeItem('sira_session_token');
    Swal.fire({
        icon: 'error',
        title: 'Sesión no válida',
        text: 'Por seguridad reingresa al sistema.',
        confirmButtonColor: '#5B3D66'
    }).then(() => {
        window.location.href = 'login.php';
    });
}

// --- EVENTOS ---
// --- EVENTOS DE FILTRADO SINCRONIZADOS ---
document.addEventListener("DOMContentLoaded", () => {
    const chkTodos = document.getElementById("chkTodos");
    const filtrosIndividuales = document.querySelectorAll(".filter-check");

    // 1. Si marco "TODOS", desmarco los individuales
    chkTodos?.addEventListener("change", function() {
        if (this.checked) {
            filtrosIndividuales.forEach(cb => cb.checked = false);
        }
        aplicarFiltros();
    });

    // 2. Si marco uno individual, desmarco "TODOS"
    filtrosIndividuales.forEach(cb => {
        cb.addEventListener("change", function() {
            if (this.checked && chkTodos) {
                chkTodos.checked = false;
            }
            // Si desmarco todos los individuales, vuelvo a marcar "TODOS"
            const algunoMarcado = Array.from(filtrosIndividuales).some(i => i.checked);
            if (!algunoMarcado && chkTodos) chkTodos.checked = true;
            
            aplicarFiltros();
        });
    });

    // Escuchar cambios en las fechas
    document.querySelectorAll("#fecha_inicio, #fecha_fin").forEach(el => {
        el.addEventListener("change", aplicarFiltros);
    });

    cargarDashboard(); 
    cargarPerfilHeader();
});

window.resetFiltros = function () {
    const chkTodos = document.getElementById("chkTodos");
    if (chkTodos) chkTodos.checked = true;
    document.querySelectorAll(".filter-check").forEach(cb => cb.checked = false);
    document.getElementById("fecha_inicio").value = "";
    document.getElementById("fecha_fin").value = "";
    aplicarFiltros();
};

window.descargarReporte = function () {
    const fInicio = document.getElementById('fecha_inicio').value;
    const fFin = document.getElementById('fecha_fin').value;
    const token = localStorage.getItem('sira_session_token'); // Token correcto

    if (!token) {
        return Swal.fire('Error', 'Sesión no válida', 'error');
    }

    // Si no hay fechas, avisamos (o puedes dejar que mande todo el histórico)
    if (!fInicio || !fFin) {
        Swal.fire({
            title: '¿Exportar todo?',
            text: "No has seleccionado un rango de fechas. Se exportará el histórico completo.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, exportar todo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(`api/reportes/generar_reporte.php?token=${token}`, '_blank');
            }
        });
        return;
    }

    // CORRECCIÓN: Ruta a la subcarpeta reportes
    window.open(`api/reportes/generar_reporte.php?inicio=${fInicio}&fin=${fFin}&token=${token}`, '_blank');
};

/**
 * ACTUALIZA EL PERFIL DE LA ESQUINA (JWT DECODER)
 */
function cargarPerfilHeader() {
    const token = localStorage.getItem('sira_session_token');
    
    if (token) {
        try {
            // Decodificamos la parte central del JWT (Payload)
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const payload = JSON.parse(atob(base64));

            // 1. Ponemos el nombre del Admin
            const nombreElemento = document.getElementById("nombreAdmin");
            if (nombreElemento && payload.nombre) {
                nombreElemento.innerText = payload.nombre;
            }

            // 2. Generamos la inicial dinámica para el avatar
            const avatarElemento = document.getElementById("inicialAvatar");
            if (avatarElemento && payload.nombre) {
                avatarElemento.innerText = payload.nombre.charAt(0).toUpperCase();
            }

        } catch (error) {
            console.error("Error al decodificar perfil:", error);
        }
    }
}

/**
 * PROCESAR SOLICITUD (Aceptar/Rechazar)
 * @param {string} nuevoEstado - 'ACEPTADA' o 'RECHAZADA'
 */
async function procesarSolicitud(nuevoEstado) {
    const notas = document.querySelector('textarea[placeholder*="Opcional"]').value.trim();
    
    // Feedback visual
    Swal.fire({ title: 'Procesando...', didOpen: () => Swal.showLoading() });

    try {
        const response = await fetch('api/solicitudes/gestion_solicitudes.php', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
            },
           body: JSON.stringify({
    id: idSeleccionado, 
    estado: nuevoEstado,
    // CAMBIO AQUÍ: de 'observaciones' a 'observaciones_admin'
    observaciones_admin: notas 
})
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('¡Éxito!', `La solicitud ha sido ${nuevoEstado.toLowerCase()}.`, 'success')
                .then(() => location.reload());
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

/**
 * ELIMINAR SOLICITUD PERMANENTEMENTE
 */
async function eliminarSolicitudDesdeModal() {
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará el folio permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    });

    if (confirmacion.isConfirmed) {
        try {
            const response = await fetch(`api/solicitudes/gestion_solicitudes.php?id=${idSeleccionado}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
                }
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire('Eliminado', 'El registro desapareció del sistema.', 'success')
                    .then(() => location.reload());
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo eliminar.', 'error');
        }
    }
}

// Variable global para rastrear qué solicitud estamos viendo en el panel admin
let idSolicitudEnGestion = null;

window.abrirReasignacion = async function(id) {
    idSolicitudEnGestion = id; // Guardamos el ID para no perderlo
    
    try {
        // 1. Consultamos los datos actuales de esa solicitud
        const res = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        const data = await res.json();

        if(data.error) return Swal.fire('Error', data.error, 'error');

        // 2. Seteamos el ID en el modal de reservación (el input oculto que ya tienes)
        $('#id_editando').val(id);
        
        // 3. Llenamos los campos del formulario para que el admin no tenga que reescribirlos
        $('input[name="titulo"]').val(data.titulo_event);
        $('textarea[name="descripcion"]').val(data.descripcion);
        $('input[name="num_asistentes"]').val(data.num_asistentes);
        $('input[name="otros_servicios"]').val(data.otros_servicios);

        // 4. IMPORTANTE: Mandamos al admin al PASO 1 (Seleccionar Auditorio)
        // para que pueda elegir un salón diferente si hay choque de horarios
        $('#modalNuevaSolicitud .modal-title').text('Reasignar Solicitud: ' + data.folio);
        $('#display_nombre_auditorio').text('Selecciona el nuevo espacio');
        
        // Escondemos los pasos de calendario y formulario, mostramos el catálogo
        $('#paso_calendario, #paso_formulario').hide();
        $('#paso_catalogo').fadeIn();

        // 5. Cerramos el modal de detalles y abrimos el de reservación
        const modalDetalle = bootstrap.Modal.getInstance(document.getElementById('bsModalDetalle'));
        if(modalDetalle) modalDetalle.hide();

        const modalReserva = new bootstrap.Modal(document.getElementById('modalNuevaSolicitud'));
        modalReserva.show();

    } catch (e) {
        console.error("Error al preparar reasignación:", e);
        Swal.fire('Error', 'No se pudo conectar con la solicitud.', 'error');
    }
};


// Variable global para que persista el ID de la solicitud abierta


// Esta función debe ejecutarse cuando el admin abre el modal de detalle
// Asegúrate de que tu función que llena el modal asigne el ID aquí
window.asignarIdActual = function(id) {
    idSolicitudActualAdmin = id;
};

window.prepararReasignacion = function() {
    if (!idSolicitudActualAdmin) {
        return Swal.fire('Atención', 'No se pudo identificar la solicitud.', 'warning');
    }

    // 1. Identificamos el modal que está abierto (Detalle)
    const modalElement = document.getElementById('bsModalDetalle');
    const modalDetalle = bootstrap.Modal.getInstance(modalElement);

    if (modalDetalle) {
        // 2. Cerramos el modal de forma inmediata
        modalDetalle.hide();

        // 3. LIMPIEZA AGRESIVA DE INTERFAZ (Para quitar lo opaco y desbloquear clics)
        // Eliminamos todos los fondos oscuros que Bootstrap haya dejado
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());

        // Quitamos la clase que bloquea el scroll y los clics en el body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = 'auto';
        document.body.style.paddingRight = '0';

        // 4. Pequeña pausa para que el navegador se entere de que ya no hay modal
        setTimeout(() => {
            console.log("Interfaz desbloqueada. Abriendo catálogo de reasignación...");
            abrirReasignacion(idSolicitudActualAdmin);
        }, 200); 
    } else {
        abrirReasignacion(idSolicitudActualAdmin);
    }
};

window.abrirReasignacion = async function(id) {
    try {
        const res = await fetch(`api/solicitudes/get_detalle.php?id=${id}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}` }
        });
        const data = await res.json();

        if(data.error) return Swal.fire('Error', data.error, 'error');

        // 1. LLENADO DE DATOS ORIGINALES
        $('input[name="titulo"]').val(data.titulo_event);
        $('textarea[name="descripcion"]').val(data.descripcion);
        $('input[name="num_asistentes"]').val(data.num_asistentes);
        $('input[name="otros_servicios"]').val(data.otros_servicios);
        
        // Seteamos el ID de la solicitud que estamos editando (CRUCIAL para el UPDATE)
        $('#id_editando').val(id);

        // 2. LÓGICA DE CHECKBOXES (EXTRAS)
        const extrasDB = data.otros_servicios || '';
        // Limpiamos todos primero
        $('#modalNuevaSolicitud input[type="checkbox"]').prop('checked', false);

        if (extrasDB) {
            // Convertimos la cadena "Proyector, Extensiones" en un array minúsculo
            const listaExtras = extrasDB.split(',').map(item => item.trim().toLowerCase());
            
            $('#modalNuevaSolicitud input[type="checkbox"]').each(function() {
                const valorCheck = $(this).val().toLowerCase();
                if (listaExtras.includes(valorCheck)) {
                    $(this).prop('checked', true);
                }
            });
        }

        // 3. CONFIGURACIÓN VISUAL Y BLOQUEO
        $('#modalNuevaSolicitud .modal-title').text('Reasignar Folio: ' + data.folio);
        
        // Bloqueamos campos para que el Admin solo cambie lugar/fecha si así lo deseas
        $('input[name="titulo"], textarea[name="descripcion"]').attr('readonly', true);

        // Forzamos ir al catálogo para elegir el NUEVO auditorio
        $('#paso_calendario, #paso_formulario').hide();
        $('#paso_catalogo').show();

        // 4. APERTURA CON EXTRACCIÓN (Para evitar pantalla opaca)
        const modalElement = document.getElementById('modalNuevaSolicitud');
        document.body.appendChild(modalElement); 
        
        modalElement.style.setProperty('display', 'block', 'important');
        modalElement.style.setProperty('z-index', '10850', 'important'); // Z-index alto para Admin
        modalElement.style.setProperty('position', 'fixed', 'important');
        modalElement.style.setProperty('top', '50%', 'important');
        modalElement.style.setProperty('left', '50%', 'important');
        modalElement.style.setProperty('transform', 'translate(-50%, -50%)', 'important');

        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
        
        console.log("Datos de reasignación cargados con éxito.");

    } catch (e) {
        console.error("Error al cargar datos para reasignar:", e);
        Swal.fire('Error', 'No se pudo sincronizar la información.', 'error');
    }
};

$(document).on('click', '#modalNuevaSolicitud .btn-close', function() {
    $('#modalNuevaSolicitud').hide();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('overflow', 'auto');
});