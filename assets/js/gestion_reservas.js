/**
 * GESTIÓN DE RESERVAS SIRA UTM - NIVEL TSU
 * Implementa: FullCalendar, Async/Await, JWT Security y Fetch API.
 */

/* global FullCalendar, Swal, bootstrap, $ */

let auditorioSeleccionado = null;
let horaSeleccionada = null;
let calendarInstance = null;
let ocupadosGlobal = [];
let seleccionInicio = null;
let seleccionFin = null;
let equipamientoActual = "";

// --- 1. NAVEGACIÓN Y RENDERIZADO ---

function irAlCalendario(id, nombre, equipamiento) {
    auditorioSeleccionado = id;
    equipamientoActual = equipamiento || "";

    document.getElementById("input_id_auditorio").value = id;
    document.getElementById("display_nombre_auditorio").innerText = nombre;
    document.getElementById("display_nombre_final").innerText = nombre;

    document.getElementById("paso_catalogo").style.display = "none";
    document.getElementById("paso_formulario").style.display = "none";
    document.getElementById("paso_calendario").style.display = "block";

    setTimeout(() => {
        initCalendar();
        let fechaPrecargada = document.getElementById("input_fecha_evento").value;
        if (fechaPrecargada && calendarInstance) {
            calendarInstance.gotoDate(fechaPrecargada);
            actualizarDisponibilidad(); 
        }
    }, 200);
}

// --- 2. CALENDARIO Y DISPONIBILIDAD ---

function initCalendar() {
    const calendarEl = document.getElementById("calendar_interactivo");
    if (calendarInstance) calendarInstance.destroy();

    calendarInstance = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        locale: "es",
        selectable: true,
        validRange: { start: new Date().toISOString().split("T")[0] },
        // CORRECCIÓN: Ruta al subdirectorio 'solicitudes'
        events: `api/solicitudes/get_eventos.php?id_auditorio=${auditorioSeleccionado}`,
        dateClick: function (info) {
            const fechaTxt = info.dateStr.split("-").reverse().join("/");
            document.getElementById("fecha_display").value = fechaTxt;
            document.getElementById("fecha_seleccionada").value = info.dateStr;
            document.getElementById("fecha_seleccionada_txt").innerText = `Horarios para el ${fechaTxt}`;
            
            actualizarDisponibilidad();
        },
    });
    calendarInstance.render();
}

/**
 * CONSUMO DE API CON ASYNC/AWAIT Y TOKEN
 */
async function actualizarDisponibilidad() {
    const fecha = document.getElementById("fecha_seleccionada").value;
    const grid = document.getElementById("grid_horarios");
    grid.innerHTML = '<div class="spinner-border text-primary spinner-border-sm"></div>';

    try {
        // CORRECCIÓN: Ruta al subdirectorio y nombre de token sira_session_token
        const response = await fetch(`api/solicitudes/get_disponibilidad.php?id=${auditorioSeleccionado}&fecha=${fecha}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
            }
        });

        if (response.status === 401) return manejarSesionExpirada();

        ocupadosGlobal = await response.json();
        renderizarBotonesHorario();
    } catch (error) {
        grid.innerHTML = '<p class="text-danger">Error de conexión con el núcleo.</p>';
    }
}

// --- 3. LÓGICA DE RANGOS DE HORAS (Resumen) ---
// (Mantiene tu lógica de validación de 2 horas y choques)

// --- 4. UTILIDADES Y SEGURIDAD ---

function manejarSesionExpirada() {
    // CORRECCIÓN: Limpieza del token correcto
    localStorage.removeItem('sira_session_token');
    Swal.fire('Sesión Expirada', 'Por favor, inicia sesión de nuevo.', 'error')
        .then(() => window.location.href = 'login.php');
}

// --- 5. REPORTES Y PDF ---

window.descargarReporte = function () {
    const fInicio = document.getElementById('fecha_inicio').value;
    const fFin = document.getElementById('fecha_fin').value;
    // CORRECCIÓN: Nombre de token sira_session_token
    const token = localStorage.getItem('sira_session_token');

    if (!fInicio || !fFin) {
        Swal.fire('Rango incompleto', 'Selecciona fechas para el reporte.', 'info');
        return;
    }
    // CORRECCIÓN: Ruta al subdirectorio 'reportes'
    window.open(`api/reportes/generar_reporte.php?inicio=${fInicio}&fin=${fFin}&token=${token}`, '_blank');
};