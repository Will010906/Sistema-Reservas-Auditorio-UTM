/**
 * GESTIÓN DE USUARIOS - BÚSQUEDA Y EDICIÓN
 * Implementa búsqueda en tiempo real y persistencia de datos en modales.
 */

// Filtro de búsqueda rápida en la tabla de usuarios
document.getElementById('buscadorUsuarios')?.addEventListener('keyup', function() {
    let valor = this.value.toLowerCase();
    let filas = document.querySelectorAll('#tablaUsuarios tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.innerText.toLowerCase().includes(valor) ? "" : "none";
    });
});

/**
 * Configura el modal para un nuevo registro (Resetea campos y muestra matrícula/pass)
 */
function prepararNuevoUsuario() {
    document.getElementById('tituloModalUsuario').innerText = 'Registrar Nuevo Usuario';
    document.getElementById('formUsuario').action = 'modules/registro_usuario_admin.php';
    document.getElementById('formUsuario').reset();
    document.getElementById('div_matricula').style.display = 'block';
    document.getElementById('div_password').style.display = 'block';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

/**
 * Configura el modal para editar (Oculta campos sensibles y carga datos existentes)
 */
function editarUsuario(user) {
    document.getElementById('tituloModalUsuario').innerText = 'Editar Usuario';
    document.getElementById('formUsuario').action = 'modules/editar_usuario.php';
    document.getElementById('user_id').value = user.id_usuario;
    document.getElementById('user_nombre').value = user.nombre;
    document.getElementById('user_matricula').value = user.matricula;
    document.getElementById('user_correo').value = user.correo_electronico;
    document.getElementById('user_carrera').value = user.carrera_area;
    document.getElementById('user_perfil').value = user.perfil;

    document.getElementById('div_matricula').style.display = 'none';
    document.getElementById('div_password').style.display = 'none';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

/**
 * Procesa la baja de un usuario mediante Fetch API
 */
function eliminarUsuario(id) {
    if (confirm("¿Estás SEGURO de eliminar este usuario? No podrá volver a entrar al sistema.")) {
        const datos = new FormData();
        datos.append('id', id);
        datos.append('accion', 'eliminar_usuario');

        fetch('modules/acciones_usuarios.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert("Error al eliminar: " + data.error);
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Ocurrió un error en el servidor.");
        });
    }
}
