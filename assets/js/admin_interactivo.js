/**
 * LÓGICA INTERACTIVA DEL PANEL ADMINISTRADOR
 * Maneja la gestión de solicitudes, filtrado combinado y reportes.
 */

let idSeleccionado = null;
let bsModal = null; 

/**
 * Carga el detalle de una solicitud mediante AJAX y abre el modal de gestión
 */
function gestionar(id) {
    idSeleccionado = id;
    
    if (!bsModal) {
        const modalElement = document.getElementById('bsModalDetalle');
        if (modalElement) {
            bsModal = new bootstrap.Modal(modalElement);
        } else {
            console.error("No se encontró el modal con ID: bsModalDetalle");
            return;
        }
    }

    fetch(`modules/get_detalle.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            const llenar = (id, texto) => {
                const el = document.getElementById(id);
                if (el) el.innerText = texto;
            };

            // Inyección de datos en la interfaz del modal
            llenar('detFolio', "Folio: " + (data.folio || ''));
            llenar('detFechaSol', data.fecha_registro || '');
            llenar('detFechaEvento', data.fecha_evento || ''); 
            llenar('detEstado', data.estado || '');
            llenar('detUsuarioNombre', data.nombre || '');
            llenar('detTituloEv', data.titulo_event || '');
            llenar('detDescripcion', data.descripcion || '');
            
            bsModal.show();
        });
}

function cerrarModal() {
    if (bsModal) bsModal.hide();
}

/**
 * Envía la decisión del administrador (Aprobar/Rechazar) al servidor
 */
function actualizarEstado(nuevoEstado) {
    if (!idSeleccionado) return;

    const motivo = document.getElementById('motivoRechazo').value;

    fetch('modules/actualizar_estado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            id: idSeleccionado, 
            estado: nuevoEstado,
            comentario: motivo 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("¡Solicitud " + nuevoEstado + " con éxito!");
            location.reload(); 
        } else {
            alert("Error al actualizar.");
        }
    });
}

/**
 * SISTEMA DE FILTRADO COMBINADO (Estatus + Fechas)
 */
document.addEventListener("change", function(e) {
    if (e.target.classList.contains("filter-check") || e.target.type === "date") {
        aplicarFiltros();
    }
});

function aplicarFiltros() {
    const seleccionados = Array.from(document.querySelectorAll(".filter-check:checked"))
                               .map(cb => cb.value);

    const inicio = document.getElementById("fecha_inicio").value;
    const fin = document.getElementById("fecha_fin").value;
    const filas = document.querySelectorAll("#tablaSolicitudes tbody tr");

    filas.forEach(fila => {
        const badge = fila.querySelector(".badge-status");
        const fechaFila = fila.querySelector("td:nth-child(4)").innerText; 
        
        if (!badge) return;
        const textoEstado = badge.innerText.trim().toUpperCase();

        const cumpleEstatus = seleccionados.includes(textoEstado);
        let cumpleFecha = true;
        if (inicio && fin) {
            cumpleFecha = (fechaFila >= inicio && fechaFila <= fin);
        }

        fila.style.display = (cumpleEstatus && cumpleFecha) ? "" : "none";
    });
}

function resetFiltros() {
    document.querySelectorAll(".filter-check").forEach(cb => cb.checked = true);
    document.getElementById("fecha_inicio").value = "";
    document.getElementById("fecha_fin").value = "";
    aplicarFiltros();
}

/**
 * Redirección al generador de PDF con los parámetros de filtro actuales
 */
function descargarReporte() {
    const inicio = document.getElementById("fecha_inicio").value;
    const fin = document.getElementById("fecha_fin").value;
    const seleccionados = Array.from(document.querySelectorAll(".filter-check:checked"))
                               .map(cb => cb.value)
                               .join(',');

    if (!inicio || !fin) {
        alert("Por favor selecciona un rango de fechas para el reporte.");
        return;
    }

    const url = `modules/generar_reporte.php?inicio=${inicio}&fin=${fin}&estatus=${seleccionados}`;
    window.open(url, '_blank');
}