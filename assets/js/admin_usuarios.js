/**
 * GESTIÓN DE USUARIOS - SIRA UTM
 * Lógica unificada para búsqueda, edición y eliminación.
 */

// Búsqueda en tiempo real
document.getElementById('buscadorUsuarios')?.addEventListener('keyup', function() {
    let valor = this.value.toLowerCase();
    let filas = document.querySelectorAll('#tablaUsuarios tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.innerText.toLowerCase().includes(valor) ? "" : "none";
    });
});

// Preparar Modal para Nuevo Usuario
function prepararNuevoUsuario() {
    const form = document.getElementById('formUsuario');
    if (!form) return;
    
    document.getElementById('tituloModalUsuario').innerText = 'Registrar Nuevo Usuario';
    form.action = 'modules/registro_usuario_admin.php';
    form.reset();

    if (document.getElementById('bloque_matricula')) document.getElementById('bloque_matricula').style.display = 'block';
    if (document.getElementById('bloque_password')) document.getElementById('bloque_password').style.display = 'block';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario')).show();
}

// Cargar datos para Editar Usuario
function editarUsuario(user) {
    document.getElementById('tituloModalUsuario').innerText = 'Editar Usuario';
    document.getElementById('formUsuario').action = 'modules/editar_usuario.php';

    document.getElementById('user_id').value = user.id_usuario || '';
    document.getElementById('user_nombre').value = user.nombre || '';
    document.getElementById('user_correo').value = user.correo_electronico || '';
    document.getElementById('user_telefono').value = user.telefono || '';
    document.getElementById('user_matricula').value = user.matricula || '';
    document.getElementById('user_carrera').value = user.carrera_area || '';
    document.getElementById('user_perfil').value = user.perfil || '';

    if (document.getElementById('bloque_matricula')) document.getElementById('bloque_matricula').style.display = 'block';
    if (document.getElementById('bloque_password')) document.getElementById('bloque_password').style.display = 'none';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario')).show();
}

// Función de Eliminación Segura
function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: "Esta acción es permanente y el usuario perderá acceso.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#5B3D66',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `modules/eliminar_usuario.php?id=${id}`;
        }
    });
}