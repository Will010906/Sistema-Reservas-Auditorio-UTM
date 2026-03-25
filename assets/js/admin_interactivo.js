/**
 * LÓGICA INTERACTIVA DEL PANEL - NIVEL TSU
 * Estado: Producción Segura (JWT + Async/Await)
 */

/* global Swal, bootstrap */

let idSeleccionado = null;
let bsModal = null;

/**
 * GESTIÓN DE DETALLES
 * Obtiene datos del servidor usando el estándar Bearer Token
 */
/**
 * GESTIÓN DE DETALLES - SIRA UTM
 * Actualizado: Soporte para Bitácora de Cierre y Equipamiento Especial
 */
async function gestionar(id) {
    idSeleccionado = id;

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

        // 1. Llenado de textos básicos
       document.getElementById("detFolio").innerText = "Folio: " + (data.folio || 'N/A');
document.getElementById("detTituloEv").innerText = data.titulo_event;

// --- CAMBIO AQUÍ: Agregamos el ROL al lado del nombre ---
const nombre = data.nombre_usuario || data.nombre || 'Sin nombre';
const rol = data.perfil ? `(${data.perfil.toUpperCase()})` : ''; 
document.getElementById("detUsuarioNombre").innerText = `${nombre} ${rol}`;

document.getElementById("detAuditorio").innerText = data.nombre_espacio;

// --- CAMBIO AQUÍ: Aseguramos que pinte el número de la DB ---
document.getElementById("detAsistentes").innerText = `👥 ${data.num_asistentes || 0} asistentes aprox.`;

document.getElementById("detFechaEvento").innerText = data.fecha_evento_limpia || data.fecha_evento;
document.getElementById("detHorario").innerText = `${data.hora_inicio} a ${data.hora_fin}`;
document.getElementById("detDescription").innerText = data.descripcion;

        // 2. EQUIPAMIENTO ESPECIAL (EXTRAS)
      // 2. EQUIPAMIENTO ESPECIAL (EXTRAS)
// En lugar de buscar 'equipos_solicitados', buscamos 'otros_servicios'
// En admin_interactivo.js
// admin_interactivo.js - Dentro de gestionar(id)
// admin_interactivo.js
// 2. EQUIPAMIENTO ESPECIAL (EXTRAS)
const contenedorEquipos = document.getElementById("detEquipamiento");

// Usamos 'extras_texto' (que viene del alias en tu PHP) o 'otros_servicios'
const extras = data.extras_texto || data.otros_servicios || '';

if (contenedorEquipos) {
    if (extras && extras.trim() !== "" && extras.toLowerCase() !== 'sin requerimientos extras') {
        // Convertimos el texto "Laptop, Sonido" en Badges elegantes
        const listaExtras = extras.split(', ');
        contenedorEquipos.innerHTML = listaExtras.map(e => 
            `<span class="badge bg-purple-soft text-purple border-purple-light me-1 shadow-sm" 
                   style="background-color: #f3e5f5; color: #5B3D66; border: 1px solid #d1c4e9; padding: 5px 10px;">
                ${e}
            </span>`
        ).join('');
    } else {
        // Si no hay datos, mostramos el mensaje por defecto
        contenedorEquipos.innerHTML = '<span class="text-muted small italic">Sin requerimientos extras.</span>';
    }
}

        // 3. BITÁCORA DE CIERRE (COHERENCIA CON USUARIO)
        // Si el docente ya reportó el cierre, el admin debe verlo aquí.
        const seccionBitacora = document.getElementById("seccionBitacoraAdmin");
        if (seccionBitacora) {
            if (data.incidentes_cierre) {
                seccionBitacora.style.display = "block";
                document.getElementById("detBitacoraTexto").innerText = `"${data.incidentes_cierre}"`;
            } else {
                seccionBitacora.style.display = "none";
            }
        }

        // 4. WhatsApp Dinámico
        const btnWA = document.getElementById("btnWhatsApp");
        if (data.telefono) {
            btnWA.href = `https://wa.me/52${data.telefono.replace(/\D/g, "")}`;
            btnWA.style.display = "inline-block";
        } else {
            btnWA.style.display = "none";
        }

        bsModal.show();

    } catch (error) {
        console.error("Error de conexión:", error);
        Swal.fire('Error', 'No se pudo obtener la información de la base de datos.', 'error');
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