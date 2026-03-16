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
            llenar('detFechaEvento', data.fecha_evento || ''); 
            
            // --- NUEVOS DATOS TÉCNICOS ---
            llenar('detHorario', (data.hora_inicio || '') + ' a ' + (data.hora_fin || ''));
            llenar('detAuditorio', data.nombre_espacio || '');
            
            llenar('detEstado', data.estado || '');
            llenar('detUsuarioNombre', data.nombre || '');
            llenar('detTituloEv', data.titulo_event || '');
            llenar('detDescripcion', data.descripcion || '');
            
            // Asignación dinámica del ID al botón de eliminar dentro del modal
            const btnBorrar = document.getElementById('btnBorrarModal');
            if(btnBorrar) {
                btnBorrar.setAttribute('onclick', `eliminarSolicitud(${id})`);
            }
            
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

/**
 * Elimina permanentemente una solicitud de reservación
 * @param {number} id - ID de la solicitud a eliminar de la base de datos
 */
function eliminarSolicitud(id) {
    if (confirm("¿Estás seguro de eliminar este registro? Esta acción no se puede deshacer.")) {
        
        // Ponemos un alert para saber que sí entró a la función y qué ID está mandando
        alert("Intentando borrar el ID: " + id);

        fetch(`modules/eliminar_solicitud.php?id=${id}`)
            .then(res => res.text()) // Lo pedimos como texto para leer los errores ocultos de PHP
            .then(texto => {
                console.log("Respuesta cruda del servidor:", texto); // Se guarda en la consola (F12)
                
                try {
                    const data = JSON.parse(texto);
                    if (data.success) {
                        alert("¡Borrado exitoso!");
                        location.reload(); 
                    } else {
                        // Si la base de datos falla (ej. llaves foráneas), nos dirá el porqué
                        alert("Error en la Base de Datos: " + data.error); 
                    }
                } catch(e) {
                    // Si el archivo PHP no existe o tiene un error de sintaxis, nos avisará aquí
                    alert("Error crítico del servidor. La respuesta fue: " + texto);
                }
            })
            .catch(err => alert("Error de red o ruta: " + err));
    }
}