/**
 * LÓGICA INTERACTIVA DEL PANEL ADMINISTRADOR - SIRA UTM
 * Estado: Producción - Filtrado Sincronizado (Estatus + Fechas ISO)
 */

let idSeleccionado = null;
let bsModal = null;

// --- GESTIÓN DE DETALLES ---
function gestionar(id) {
    idSeleccionado = id;
    if (!bsModal) {
        const modalElement = document.getElementById("bsModalDetalle");
        if (modalElement) bsModal = new bootstrap.Modal(modalElement);
    }

    fetch(`modules/get_detalle.php?id=${id}`)
        .then((res) => res.json())
        .then((data) => {
            document.getElementById("detFolio").innerText = "Folio: " + data.folio;
            document.getElementById("detTituloEv").innerText = data.titulo_event;
            document.getElementById("detUsuarioNombre").innerText = data.nombre;
            document.getElementById("detAuditorio").innerText = data.nombre_espacio;
            document.getElementById("detFechaEvento").innerText = data.fecha_evento;
            document.getElementById("detHorario").innerText = `${data.hora_inicio} a ${data.hora_fin}`;
            document.getElementById("detDescription").innerText = data.descripcion;

            const btnWA = document.getElementById("btnWhatsApp");
            if (data.telefono) {
                btnWA.href = `https://wa.me/52${data.telefono.replace(/\D/g, "")}`;
                btnWA.style.display = "inline-block";
            } else {
                btnWA.style.display = "none";
            }

            const asistentes = parseInt(data.num_asistentes || 0);
            const capacidad = parseInt(data.capacidad_maxima || 0);
            const alertaCap = document.getElementById("detAsistentes");
            if (asistentes > capacidad) {
                alertaCap.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> Sobrecupo: ${asistentes}/${capacidad}</span>`;
            } else {
                alertaCap.innerHTML = `<span class="text-muted small">${asistentes} asistentes (Capacidad: ${capacidad})</span>`;
            }

            const contenedorEquip = document.getElementById("detEquipamiento");
            contenedorEquip.innerHTML = "";
            if (data.equipos_solicitados) {
                data.equipos_solicitados.split(", ").forEach((item) => {
                    contenedorEquip.innerHTML += `<span class="badge bg-light text-dark border small me-1">${item}</span>`;
                });
            } else {
                contenedorEquip.innerHTML = '<span class="text-muted small italic">Ninguno solicitado</span>';
            }
            bsModal.show();
        });
}

// --- MOTOR DE FILTRADO (ESTRICTO Y SINCRONIZADO) ---
function aplicarFiltros() {
    const inicio = document.getElementById("fecha_inicio").value;
    const fin = document.getElementById("fecha_fin").value;
    const chkTodos = document.getElementById("chkTodos");
    const todosActivo = chkTodos ? chkTodos.checked : true;

    const seleccionados = todosActivo
        ? []
        : Array.from(document.querySelectorAll(".filter-check:checked")).map(cb => cb.value.toUpperCase());

    const filas = document.querySelectorAll(".solicitud-fila");

    filas.forEach((fila) => {
        const celdaFecha = fila.querySelector(".date-cell");
        if (!celdaFecha) return;
        const fechaFila = celdaFecha.innerText.trim();

        // FILTRO DE FECHA: Se evalúa siempre, sin importar el estatus
        let cumpleFecha = true;
        if (inicio && fin) {
            cumpleFecha = (fechaFila >= inicio && fechaFila <= fin);
        } else if (inicio) {
            cumpleFecha = (fechaFila >= inicio);
        } else if (fin) {
            cumpleFecha = (fechaFila <= fin);
        }

        // FILTRO DE ESTATUS: Se relaja si TODOS está marcado
        const badge = fila.querySelector(".badge-status");
        let cumpleEstatus = true;
        if (badge) {
            const textoEstado = badge.innerText.trim().toUpperCase();
            cumpleEstatus = todosActivo || seleccionados.includes(textoEstado);
        }

        // RESULTADO: Si la fecha falla, se oculta aunque TODOS esté activo
        if (cumpleFecha && cumpleEstatus) {
            fila.style.setProperty('display', '', 'important');
        } else {
            fila.style.setProperty('display', 'none', 'important');
        }
    });
}

// --- GESTIÓN DE EVENTOS (SINCRONIZACIÓN TOTAL) ---
document.addEventListener("DOMContentLoaded", () => {
    const chkTodos = document.getElementById("chkTodos");
    const filtrosEspecificos = document.querySelectorAll(".filter-check");

    // 1. Comportamiento de exclusión de checkboxes
    if (chkTodos) {
        chkTodos.addEventListener("change", function() {
            if (this.checked) {
                // Si marcas TODOS, desmarcas los demás
                filtrosEspecificos.forEach(cb => cb.checked = false);
            }
            aplicarFiltros();
        });
    }

    filtrosEspecificos.forEach(cb => {
        cb.addEventListener("change", function() {
            if (this.checked && chkTodos) {
                // Si marcas un estatus específico, desmarcas TODOS
                chkTodos.checked = false;
            }
            aplicarFiltros();
        });
    });

    // 2. Eventos para fechas (Reforzados)
    const fInicio = document.getElementById("fecha_inicio");
    const fFin = document.getElementById("fecha_fin");

    if (fInicio) fInicio.addEventListener("input", aplicarFiltros);
    if (fFin) fFin.addEventListener("input", aplicarFiltros);
    if (fInicio) fInicio.addEventListener("change", aplicarFiltros);
    if (fFin) fFin.addEventListener("change", aplicarFiltros);

    // Ejecución inicial por si el navegador guardó valores en los inputs
    aplicarFiltros();
});

// --- FUNCIONES DE UTILIDAD GLOBALES ---
window.resetFiltros = function () {
    const chkTodos = document.getElementById("chkTodos");
    if (chkTodos) chkTodos.checked = true;
    
    document.querySelectorAll(".filter-check").forEach(cb => cb.checked = false);
    document.getElementById("fecha_inicio").value = "";
    document.getElementById("fecha_fin").value = "";
    
    aplicarFiltros();
};

window.descargarReporte = function () {
    const inicio = document.getElementById("fecha_inicio").value;
    const fin = document.getElementById("fecha_fin").value;
    const seleccionados = Array.from(document.querySelectorAll(".filter-check:checked")).map(cb => cb.value).join(",");

    if (!inicio || !fin) {
        Swal.fire({ icon: "info", title: "Rango incompleto", text: "Selecciona fechas de inicio y fin para el reporte.", confirmButtonColor: "#5B3D66" });
        return;
    }
    window.open(`modules/generar_reporte.php?inicio=${inicio}&fin=${fin}&estatus=${seleccionados}`, '_blank');
};