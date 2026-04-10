/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: MOTOR DE RESERVACIONES E INTERFAZ DE CALENDARIO
 * * @package     Frontend_Logic
 * @subpackage  Calendar_Management
 * @version     2.4.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Orquestador de la lógica de reservaciones que integra FullCalendar para la 
 * gestión visual de fechas. Implementa un sistema de navegación por pasos 
 * (Catálogo -> Calendario -> Formulario) y validación de colisiones horarias.
 * * CAPACIDADES:
 * 1. Integración FullCalendar: Renderizado de eventos y selección de fechas.
 * 2. Disponibilidad Asíncrona: Consulta en tiempo real de bloques ocupados.
 * 3. Seguridad JWT: Inyección de Bearer Token en peticiones de datos sensibles.
 * 4. Gestión de Reportes: Interfaz para la generación de documentos PDF.
 */

/* global FullCalendar, Swal, bootstrap, $ */

/**
 * ESTADOS GLOBALES (RUNTIME CONTEXT)
 */
let auditorioSeleccionado = null;
let calendarInstance = null;
let ocupadosGlobal = [];
let equipamientoActual = "";

/**
 * 1. SUBSISTEMA DE NAVEGACIÓN Y FLUJO
 * Configura el contexto del auditorio y transiciona la vista hacia el calendario.
 * @param {number} id - ID del auditorio.
 * @param {string} nombre - Nombre descriptivo del espacio.
 * @param {string} equipamiento - Lista de recursos fijos del auditorio.
 */
function irAlCalendario(id, nombre, equipamiento) {
    auditorioSeleccionado = id;
    equipamientoActual = equipamiento || "";

    // Sincronización de metadatos en el DOM
    document.getElementById("input_id_auditorio").value = id;
    document.getElementById("display_nombre_auditorio").innerText = nombre;
    document.getElementById("display_nombre_final").innerText = nombre;

    // Gestión de visibilidad por capas (Wizard Step Navigation)
    document.getElementById("paso_catalogo").style.display = "none";
    document.getElementById("paso_formulario").style.display = "none";
    document.getElementById("paso_calendario").style.display = "block";

    // Inicialización diferida para asegurar el renderizado correcto del contenedor
    setTimeout(() => {
        initCalendar();
        let fechaPrecargada = document.getElementById("input_fecha_evento").value;
        if (fechaPrecargada && calendarInstance) {
            calendarInstance.gotoDate(fechaPrecargada);
            actualizarDisponibilidad(); 
        }
    }, 200);
}

/**
 * 2. SUBSISTEMA DE CALENDARIO (FULLCALENDAR)
 * Inicializa la instancia del calendario y configura los endpoints de eventos.
 */
function initCalendar() {
    const calendarEl = document.getElementById("calendar_interactivo");
    if (calendarInstance) calendarInstance.destroy();

    calendarInstance = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        locale: "es",
        selectable: true,
        // Restricción: No se permiten reservaciones en fechas pasadas
        validRange: { start: new Date().toISOString().split("T")[0] },
        
        // Feed dinámico de eventos para visualizar ocupación previa
        events: `api/solicitudes/get_eventos.php?id_auditorio=${auditorioSeleccionado}`,
        
        /**
         * MANEJADOR DE SELECCIÓN DE FECHA
         * Prepara la interfaz para la selección de bloques horarios.
         */
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
 * 3. CONSULTA DE DISPONIBILIDAD (ASYNC FETCH)
 * Recupera los rangos horarios ocupados para la fecha y espacio seleccionados.
 * @async
 */
async function actualizarDisponibilidad() {
    const fecha = document.getElementById("fecha_seleccionada").value;
    const grid = document.getElementById("grid_horarios");
    
    // Feedback visual de carga (Spinner)
    grid.innerHTML = '<div class="spinner-border text-primary spinner-border-sm"></div>';

    try {
        const response = await fetch(`api/solicitudes/get_disponibilidad.php?id=${auditorioSeleccionado}&fecha=${fecha}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('sira_session_token')}`
            }
        });

        if (response.status === 401) return manejarSesionExpirada();

        ocupadosGlobal = await response.json();
        // Dispara el renderizado de la cuadrícula de horas (Lógica interna del módulo)
        renderizarBotonesHorario();
    } catch (error) {
        grid.innerHTML = '<p class="text-danger">Fallo crítico al sincronizar con el motor de disponibilidad.</p>';
    }
}

/**
 * 4. SUBSISTEMA DE REPORTES
 * Genera el enlace de descarga para reportes institucionales en PDF.
 */
window.descargarReporte = function () {
    const fInicio = document.getElementById('fecha_inicio').value;
    const fFin = document.getElementById('fecha_fin').value;
    const token = localStorage.getItem('sira_session_token');

    if (!fInicio || !fFin) {
        Swal.fire('Rango Incompleto', 'Defina las fechas de inicio y fin para la generación del documento.', 'info');
        return;
    }
    
    // Apertura de reporte en nueva pestaña mediante el controlador de reportes
    window.open(`api/reportes/generar_reporte.php?inicio=${fInicio}&fin=${fFin}&token=${token}`, '_blank');
};

/**
 * 5. SEGURIDAD Y CONTROL DE SESIÓN
 */
function manejarSesionExpirada() {
    localStorage.removeItem('sira_session_token');
    Swal.fire({
        icon: 'error',
        title: 'Sesión Finalizada',
        text: 'Por seguridad institutional, reingrese sus credenciales.',
        confirmButtonColor: '#5B3D66'
    }).then(() => window.location.href = 'login.php');
}