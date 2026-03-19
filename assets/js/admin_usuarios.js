/**
 * GESTIÓN DE USUARIOS - SIRA UTM
 * Lógica para búsqueda y control de modales
 */

// Filtro de búsqueda rápida en la tabla
document.getElementById('buscadorUsuarios')?.addEventListener('keyup', function() {
    let valor = this.value.toLowerCase();
    let filas = document.querySelectorAll('#tablaUsuarios tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.innerText.toLowerCase().includes(valor) ? "" : "none";
    });
});

/**
 * Función para EDITAR usuario (Carga datos y abre modal)
 */
function editarUsuario(user) {
    // 1. Cambiar textos del modal
    document.getElementById('tituloModalUsuario').innerText = 'Editar Usuario';
    document.getElementById('formUsuario').action = 'modules/editar_usuario.php';

    // 2. Llenar los datos básicos (Aseguramos que no fallen si son null)
    document.getElementById('user_id').value = user.id_usuario || '';
    document.getElementById('user_nombre').value = user.nombre || '';
    document.getElementById('user_correo').value = user.correo_electronico || '';
    document.getElementById('user_telefono').value = user.telefono || '';
    document.getElementById('user_matricula').value = user.matricula || '';
    document.getElementById('user_carrera').value = user.carrera_area || '';
    document.getElementById('user_perfil').value = user.perfil || '';

    // 3. Control de bloques (Aquí es donde daba el error de la línea 40)
    const bMatricula = document.getElementById('bloque_matricula');
    const bPassword = document.getElementById('bloque_password');

    if (bMatricula) bMatricula.style.display = 'block';
    if (bPassword) bPassword.style.display = 'none';

    // 4. Abrir modal
    var modalElem = document.getElementById('modalUsuario');
    var modalInstance = bootstrap.Modal.getOrCreateInstance(modalElem);
    modalInstance.show();
}

/**
 * Función para NUEVO usuario (Limpia campos y abre modal)
 */
function prepararNuevoUsuario() {
    document.getElementById('tituloModalUsuario').innerText = 'Registrar Nuevo Usuario';
    document.getElementById('formUsuario').action = 'modules/registro_usuario.php';
    document.getElementById('formUsuario').reset();

    // Mostrar ambos bloques para el registro
    document.getElementById('bloque_matricula').style.display = 'block';
    document.getElementById('bloque_password').style.display = 'block';

    var modalElem = document.getElementById('modalUsuario');
    var modalInstance = bootstrap.Modal.getOrCreateInstance(modalElem);
    modalInstance.show();
}

/**
 * Función para ELIMINAR usuario
 */
function eliminarUsuario(id) {
    if (confirm("¿Estás SEGURO de eliminar este usuario?")) {
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
            else alert("Error: " + data.error);
        })
        .catch(error => console.error('Error:', error));
    }
}