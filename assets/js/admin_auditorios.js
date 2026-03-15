/**
 * GESTIÓN DE AUDITORIOS - LÓGICA DE INTERFAZ
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Funcionalidades: Activación/Desactivación, Eliminación y Edición dinámica.
 */

/**
 * Cambia la disponibilidad de un auditorio (Disponible / Mantenimiento)
 * @param {number} id - ID único del auditorio
 * @param {number} estadoActual - Estado actual (1 para activo, 0 para inactivo)
 */
function cambiarEstado(id, estadoActual) {
    const nuevoEstado = estadoActual === 1 ? 0 : 1;
    const msg = nuevoEstado === 1 ? "¿Activar auditorio?" : "¿Poner auditorio en mantenimiento?";

    if (confirm(msg)) {
        const datos = new URLSearchParams();
        datos.append('id', id);
        datos.append('estado', nuevoEstado);
        datos.append('accion', 'estado');

        fetch('modules/acciones_auditorio.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert("Error: " + data.error);
        });
    }
}

/**
 * Elimina permanentemente un registro de auditorio
 */
function eliminarAuditorio(id) {
    if (confirm("¿Estás SEGURO de eliminar este auditorio? Esta acción no se puede deshacer.")) {
        const datos = new URLSearchParams();
        datos.append('id', id);
        datos.append('accion', 'eliminar');

        fetch('modules/acciones_auditorio.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert("Error al eliminar.");
        });
    }
}

/**
 * Prepara el modal de registro para funcionar como formulario de edición
 * @param {Object} auditorio - Objeto con los datos provenientes de la base de datos
 */
function editarAuditorio(auditorio) {
    // 1. Cambiamos el comportamiento del modal (Título y Acción del Formulario)
    document.getElementById('tituloModal').innerText = 'Editar Auditorio';
    document.getElementById('formAuditorio').action = 'modules/editar_auditorio.php';

    // 2. Mapeo de datos a los inputs del formulario
    document.getElementById('edit_id').value = auditorio.id_auditorio;
    document.getElementById('edit_nombre').value = auditorio.nombre_espacio;
    document.getElementById('edit_ubicacion').value = auditorio.ubicacion;
    document.getElementById('edit_capacidad').value = auditorio.capacidad_maxima;
    document.getElementById('edit_equipamiento').value = auditorio.equipamiento_fijo;

    // 3. Disparo visual del modal mediante Bootstrap 5
    const modal = new bootstrap.Modal(document.getElementById('modalNuevoAuditorio'));
    modal.show();
}