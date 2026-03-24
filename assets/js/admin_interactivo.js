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

        // Llenado dinámico del modal
        document.getElementById("detFolio").innerText = "Folio: " + (data.folio || 'N/A');
        document.getElementById("detTituloEv").innerText = data.titulo_event;
        document.getElementById("detUsuarioNombre").innerText = data.nombre;
        document.getElementById("detAuditorio").innerText = data.nombre_espacio;
        document.getElementById("detFechaEvento").innerText = data.fecha_evento;
        document.getElementById("detHorario").innerText = `${data.hora_inicio} a ${data.hora_fin}`;
        document.getElementById("detDescription").innerText = data.descripcion;

        // WhatsApp
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

    // Obtenemos array de estatus marcados
    const seleccionados = todosActivo 
        ? [] 
        : Array.from(document.querySelectorAll(".filter-check:checked")).map(cb => cb.value.toUpperCase());

    const filas = document.querySelectorAll(".solicitud-fila");

    filas.forEach((fila) => {
        // 1. Filtro de Fechas
        const celdaFecha = fila.querySelector(".date-cell");
        const fechaFila = celdaFecha ? celdaFecha.innerText.trim() : "";
        let cumpleFecha = true;

        if (inicio && fin) cumpleFecha = (fechaFila >= inicio && fechaFila <= fin);
        else if (inicio) cumpleFecha = (fechaFila >= inicio);
        else if (fin) cumpleFecha = (fechaFila <= fin);

        // 2. Filtro de Estatus (Sincronizado con el Badge)
        const badge = fila.querySelector(".badge-status");
        let cumpleEstatus = true;

        if (badge && !todosActivo) {
            const textoEstado = badge.innerText.trim().toUpperCase();
            // Si el texto del Badge no está en la lista de seleccionados, se oculta
            cumpleEstatus = seleccionados.includes(textoEstado);
        }

        // Ejecutar visibilidad
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
    // Estas líneas deben coincidir con los id="count..." de tu HTML
    document.getElementById("countUrgentes").innerText   = data.stats.urgentes || 0;
    document.getElementById("countPendientes").innerText  = data.stats.pendientes || 0;
    document.getElementById("countAtiempo").innerText     = data.stats.atiempo || 0;
    document.getElementById("countAceptadas").innerText   = data.stats.aceptadas || 0;
    document.getElementById("countRechazadas").innerText  = data.stats.rechazadas || 0;

    // Log para depuración (revisa la consola F12 para ver si llegan los números)
    console.log("Estadísticas recibidas:", data.stats);
}

        // Llenar tabla dinámicamente
        const contenedor = document.getElementById("contenedorSolicitudes");
        if (contenedor) {
            contenedor.innerHTML = "";
            data.solicitudes.forEach(sol => {
                contenedor.innerHTML += `
                    <tr class="solicitud-fila">
                        <td class="ps-4 fw-bold" style="color: #5B3D66;">#${sol.folio}</td>
                        <td>
                            <div class="fw-bold">${sol.titulo_event}</div>
                            <div class="text-muted x-small">Por: ${sol.nombre_usuario}</div>
                        </td>
                        <td><span class="badge rounded-pill bg-light text-dark border px-3">${sol.nombre_espacio}</span></td>
                        <td class="fw-bold text-muted date-cell">${sol.fecha_evento}</td>
                        <td class="text-center">
                            <span class="badge-status st-${sol.estado.toLowerCase()} shadow-sm">
                                ${sol.estado.toUpperCase()}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-gestionar-sira btn-sm shadow-sm" onclick="gestionar(${sol.id_solicitud})">
    <i class="bi bi-folder2-open me-1"></i> Gestionar
</button>
                        </td>
                    </tr>
                `;
            });
        }
        
        aplicarFiltros(); // Aplicar filtros tras cargar datos

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
                id: idSeleccionado, // Esta variable global se llena al abrir el modal
                estado: nuevoEstado,
                observaciones: notas
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