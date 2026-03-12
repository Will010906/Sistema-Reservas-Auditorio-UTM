// Variables globales
let idSeleccionado = null;
let bsModal = null; 

/**
 * 1. FUNCIÓN PARA ABRIR EL MODAL Y CARGAR DATOS
 */
function gestionar(id) {
    idSeleccionado = id;
    
    // Inicializamos el objeto Modal de Bootstrap solo una vez
    if (!bsModal) {
        const modalElement = document.getElementById('bsModalDetalle');
        if (modalElement) {
            bsModal = new bootstrap.Modal(modalElement);
        } else {
            console.error("No se encontró el modal con ID: bsModalDetalle");
            return;
        }
    }

    // Traemos los datos del servidor (PHP)
   fetch(`modules/get_detalle.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
        // Usamos una función auxiliar para llenar datos de forma segura
        const llenar = (id, texto) => {
            const el = document.getElementById(id);
            if (el) el.innerText = texto;
        };

        llenar('detFolio', "Folio: " + (data.folio || ''));
        llenar('detFechaSol', data.fecha_registro || '');
        llenar('detEstado', data.estado || '');
        llenar('detUsuarioNombre', data.nombre || '');
        llenar('detTituloEv', data.titulo_event || '');
        llenar('detDescripcion', data.descripcion || '');
        
        bsModal.show();
    });
       
}

/**
 * 2. FUNCIÓN PARA CERRAR EL MODAL
 */
function cerrarModal() {
    if (bsModal) {
        bsModal.hide();
    }
}

/**
 * 3. FUNCIÓN PARA ACTUALIZAR EL ESTADO (APROBAR/RECHAZAR)
 */
function actualizarEstado(nuevoEstado) {
    if (!idSeleccionado) return;

    fetch('modules/actualizar_estado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            id: idSeleccionado, 
            estado: nuevoEstado 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("¡Solicitud " + nuevoEstado + " con éxito!");
            location.reload(); // Recargamos para ver los cambios en la tabla y tarjetas
        } else {
            alert("Error al actualizar la base de datos.");
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * 4. LÓGICA DE FILTRADO POR FECHAS
 */
document.getElementById('btnFiltrar')?.addEventListener('click', () => {
    const inicio = document.getElementById('fecha_inicio').value;
    const fin = document.getElementById('fecha_fin').value;

    if (!inicio || !fin) {
        alert("Selecciona ambas fechas para filtrar.");
        return;
    }

    fetch(`modules/filtrar_solicitudes.php?inicio=${inicio}&fin=${fin}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#tablaSolicitudes tbody');
            tbody.innerHTML = ""; // Limpiamos la tabla
            
            data.forEach(sol => {
                // Determinamos la clase del badge según el estado
                const badgeClass = (sol.estado === 'Urgente') ? 'bg-danger' : 
                                   (sol.estado === 'Pendiente' ? 'bg-warning text-dark' : 'bg-success');

                tbody.innerHTML += `
                    <tr>
                        <td class="fw-bold">${sol.folio}</td>
                        <td>${sol.titulo_event}</td>
                        <td>ID Auditorio: ${sol.id_auditorio}</td>
                        <td>${sol.fecha_evento}</td>
                        <td><span class="badge-status ${badgeClass}">${sol.estado}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="gestionar(${sol.id_solicitud})">
                                Gestionar
                            </button>
                        </td>
                    </tr>`;
            });
        });
});