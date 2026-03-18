/**
 * LÓGICA INTERACTIVA DEL PANEL ADMINISTRADOR - SIRA UTM
 * Corregido: Filtrado por estatus y rango de fechas sincronizado.
 */

let idSeleccionado = null;
let bsModal = null; 

/**
 * Carga el detalle y abre el modal
 */
function gestionar(id) {
    idSeleccionado = id;
    if (!bsModal) {
        const modalElement = document.getElementById('bsModalDetalle');
        if (modalElement) bsModal = new bootstrap.Modal(modalElement);
    }

    fetch(`modules/get_detalle.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            const llenar = (id, texto) => {
                const el = document.getElementById(id);
                if (el) el.innerText = texto || '';
            };

            llenar('detFolio', "Folio: " + data.folio);
            llenar('detFechaEvento', data.fecha_evento); 
            llenar('detHorario', (data.hora_inicio || '') + ' a ' + (data.hora_fin || ''));
            llenar('detAuditorio', data.nombre_espacio);
            llenar('detEstado', data.estado);
            llenar('detUsuarioNombre', data.nombre);
            llenar('detTituloEv', data.titulo_event);
            llenar('detDescription', data.descripcion);
            
            const btnBorrar = document.getElementById('btnBorrarModal');
            if(btnBorrar) btnBorrar.setAttribute('onclick', `eliminarSolicitud(${id})`);
            
            bsModal.show();
        });
}

/**
 * SISTEMA DE FILTRADO (Estatus + Fechas)
 */

// Escuchar cambios en checkboxes y fechas
document.addEventListener("change", function(e) {
    if (e.target.classList.contains("filter-check") || e.target.type === "date") {
        aplicarFiltros();
    }
});

function aplicarFiltros() {
    // 1. Obtener valores seleccionados de los checkboxes
    const seleccionados = Array.from(document.querySelectorAll(".filter-check:checked"))
                               .map(cb => cb.value.toUpperCase());

    // 2. Obtener valores de fecha
    const inicio = document.getElementById("fecha_inicio").value; // Formato YYYY-MM-DD
    const fin = document.getElementById("fecha_fin").value;       // Formato YYYY-MM-DD
    
    const filas = document.querySelectorAll("#tablaSolicitudes tbody tr");

    filas.forEach(fila => {
        // Obtenemos el texto del estatus del badge
        const badge = fila.querySelector(".badge-status, .badge-status-pro");
        // Obtenemos la fecha de la celda (asumiendo que es la 4ta columna)
        const fechaCeldaRaw = fila.querySelector("td:nth-child(4)").innerText.trim();
        
        // Convertir dd/mm/aaaa a aaaa-mm-dd para poder comparar fechas correctamente
        const partes = fechaCeldaRaw.split('/');
        const fechaFila = `${partes[2]}-${partes[1]}-${partes[0]}`;

        if (!badge) return;
        const textoEstado = badge.innerText.trim().toUpperCase();

        // Lógica de validación
        const cumpleEstatus = seleccionados.includes(textoEstado);
        
        let cumpleFecha = true;
        if (inicio && fin) {
            cumpleFecha = (fechaFila >= inicio && fechaFila <= fin);
        } else if (inicio) {
            cumpleFecha = (fechaFila >= inicio);
        } else if (fin) {
            cumpleFecha = (fechaFila <= fin);
        }

        // Aplicar visibilidad
        fila.style.display = (cumpleEstatus && cumpleFecha) ? "" : "none";
    });
}

/**
 * Función Limpiar (RESTAURADA)
 */
function resetFiltros() {
    // Marcar los 3 principales por defecto, desmarcar los otros
    document.getElementById("chkUrg").checked = true;
    document.getElementById("chkPen").checked = true;
    document.getElementById("chkTie").checked = true;
    document.getElementById("chkAce").checked = false;
    document.getElementById("chkRec").checked = false;

    // Limpiar fechas
    document.getElementById("fecha_inicio").value = "";
    document.getElementById("fecha_fin").value = "";

    // Refrescar tabla
    aplicarFiltros();
}

/**
 * Reporte PDF
 */
function descargarReporte() {
    const inicio = document.getElementById("fecha_inicio").value;
    const fin = document.getElementById("fecha_fin").value;
    const seleccionados = Array.from(document.querySelectorAll(".filter-check:checked"))
                               .map(cb => cb.value)
                               .join(',');

    if (!inicio || !fin) {
        alert("Por favor selecciona un rango de fechas para generar el PDF.");
        return;
    }

    const url = `modules/generar_reporte.php?inicio=${inicio}&fin=${fin}&estatus=${seleccionados}`;
    window.open(url, '_blank');
}

/**
 * Actualizar Estado (Aprobar/Rechazar)
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
            location.reload(); 
        } else {
            alert("Error al actualizar: " + data.error);
        }
    });
}

function eliminarSolicitud(id) {
    if (confirm("¿Eliminar permanentemente este registro?")) {
        fetch(`modules/eliminar_solicitud.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert("Error: " + data.error);
            });
    }
}