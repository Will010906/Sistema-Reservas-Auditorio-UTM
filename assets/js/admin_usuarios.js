/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: GESTIÓN DE USUARIOS Y CONTROL DE ACCESO (ADMIN)
 * * @package     Frontend_Logic
 * @subpackage  User_Management
 * @version     2.5.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Orquestador de operaciones CRUD (Create, Read, Update, Delete) para la entidad 'Usuarios'.
 * Implementa un modelo de comunicación asíncrona mediante Fetch API, autenticación 
 * persistente vía JWT (JSON Web Token) y renderizado reactivo del DOM.
 */

/* global Swal, bootstrap */

/**
 * 1. SUBSISTEMA DE BÚSQUEDA Y FILTRADO
 * Implementa búsqueda predictiva en tiempo real sobre el set de datos cargado.
 */
document.getElementById('buscadorUsuarios')?.addEventListener('keyup', function() {
    let valor = this.value.toLowerCase();
    let filas = document.querySelectorAll('#tablaUsuarios tbody tr');
    
    filas.forEach(fila => {
        // Evaluación de coincidencia de texto en todas las celdas de la fila
        const coincide = fila.innerText.toLowerCase().includes(valor);
        fila.style.setProperty('display', coincide ? '' : 'none', 'important');
    });
});

/**
 * 2. CONTROLADORES DE INTERFAZ (UI HANDLERS)
 * Gestionan el estado de los modales y el mapeo de datos al formulario.
 */

/**
 * Configura el formulario para la creación de una nueva identidad.
 */
function prepararNuevoUsuario() {
    const form = document.getElementById('formUsuario');
    if (!form) return;
    
    document.getElementById('tituloModalUsuario').innerText = 'Registrar Nuevo Usuario';
    form.action = 'api/admin/gestion_usuarios.php'; 
    form.reset();

    // Reset visual de bloques de datos obligatorios
    if (document.getElementById('bloque_matricula')) document.getElementById('bloque_matricula').style.display = 'block';
    if (document.getElementById('bloque_password')) document.getElementById('bloque_password').style.display = 'block';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario')).show();
}

/**
 * Mapea los metadatos de un usuario existente al formulario de edición.
 * @param {Object} user - Entidad usuario recuperada de la base de datos.
 */
function editarUsuario(user) {
    document.getElementById('tituloModalUsuario').innerText = 'Editar Usuario';
    document.getElementById('formUsuario').action = 'api/admin/gestion_usuarios.php';

    // Inyección de valores en los campos del DOM
    document.getElementById('user_id').value = user.id_usuario || '';
    document.getElementById('user_nombre').value = user.nombre || '';
    document.getElementById('user_correo').value = user.correo_electronico || '';
    document.getElementById('user_telefono').value = user.telefono || '';
    document.getElementById('user_matricula').value = user.matricula || '';
    document.getElementById('user_carrera').value = user.carrera_area || '';
    document.getElementById('user_perfil').value = user.perfil || '';

    // Lógica de visualización: En edición no se expone la contraseña por seguridad
    if (document.getElementById('bloque_matricula')) document.getElementById('bloque_matricula').style.display = 'block';
    if (document.getElementById('bloque_password')) document.getElementById('bloque_password').style.display = 'none';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario')).show();
}

/**
 * 3. SUBSISTEMA DE COMUNICACIÓN ASÍNCRONA (CRUD + JWT)
 * Orquesta las peticiones HTTP hacia la API unificada.
 */

/**
 * Ejecuta la eliminación lógica/física de un usuario mediante método DELETE.
 * @async
 * @param {number} id - Identificador único del usuario.
 */
async function eliminarUsuario(id) {
    const result = await Swal.fire({
        title: '¿Confirmar eliminación?',
        text: "Esta acción revocará permanentemente el acceso del usuario al sistema.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#5B3D66',
        confirmButtonText: 'Sí, eliminar',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            const tokenSeguridad = localStorage.getItem('token'); 

            const response = await fetch('api/admin/gestion_usuarios.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${tokenSeguridad}`
                },
                body: JSON.stringify({ id_usuario: parseInt(id) }) 
            });

            if (response.status === 401) return manejarSesionExpirada();

            const data = await response.json();

            if (data.success) {
                Swal.fire('Eliminado', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.error, 'error');
            }
        } catch (error) {
            Swal.fire('Fallo de Red', 'No se pudo sincronizar con el servidor institucional.', 'error');
        }
    }
}

/**
 * MANEJADOR DE TRANSACCIONES (POST/PUT)
 * Intercepta el evento submit para procesar el registro o actualización vía JSON.
 */
document.getElementById('formUsuario')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const idInput = document.getElementById('user_id').value;

    // Construcción del DTO (Data Transfer Object)
    const datos = {
        id_usuario: idInput ? idInput.toString() : null,
        nombre: formData.get('nombre').trim(),
        matricula: formData.get('matricula').trim(),
        telefono: formData.get('telefono').trim(),
        correo_electronico: formData.get('correo_electronico').trim(),
        carrera_area: formData.get('carrera_area'),
        perfil: formData.get('perfil'),
        password: formData.get('password') || "" 
    };

    // Determinación dinámica del verbo HTTP basado en la presencia de ID
    const metodo = datos.id_usuario ? 'PUT' : 'POST';

    try {
        const response = await fetch('api/admin/gestion_usuarios.php', {
            method: metodo,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(datos)
        });

        if (response.status === 401) return manejarSesionExpirada();

        const data = await response.json();
        if (data.success) {
            Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Validación', data.error, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Fallo crítico al procesar la solicitud.', 'error');
    }
});

/**
 * 4. SUBSISTEMA DE RENDERIZADO DINÁMICO
 * Consume la API mediante GET y reconstruye la tabla de usuarios.
 * @async
 */
async function cargarUsuarios() {
    const cuerpoTabla = document.getElementById("listaUsuariosBody");
    if (!cuerpoTabla) return;

    try {
        const response = await fetch("api/admin/gestion_usuarios.php", {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${localStorage.getItem("token")}` }
        });

        if (response.status === 401) return manejarSesionExpirada();
        const data = await response.json();
        
        cuerpoTabla.innerHTML = ""; 

        if (data.length === 0) {
            cuerpoTabla.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No existen registros en la base de datos.</td></tr>';
            return;
        }

        data.forEach(user => {
            // Generación de Iniciales para Avatar mediante manipulación de strings
            const iniciales = user.nombre ? user.nombre.split(' ').map(n => n[0]).join('').substring(0, 2) : '??';
            
            // Mapeo de Identidad Visual por Rol
            const roleClass = {
                'administrador': 'sira-badge-admin',
                'subdirector': 'sira-badge-sub',
                'docente': 'sira-badge-doc',
                'alumno': 'sira-badge-alu'
            }[user.perfil.toLowerCase()] || 'bg-light text-dark';

            // Inyección de fragmento HTML enriquecido
            cuerpoTabla.innerHTML += `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle-sm me-3">${iniciales}</div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">${user.nombre}</div>
                                <div class="text-muted x-small">${user.correo_electronico}</div>
                            </div>
                        </div>
                    </td>
                    <td><code class="fw-bold text-primary" style="font-size:0.75rem;">${user.matricula || 'N/A'}</code></td>
                    <td><span class="text-muted small">${user.telefono || '---'}</span></td>
                    <td><span class="small text-muted">${user.carrera_area}</span></td>
                    <td><span class="badge ${roleClass} text-uppercase px-3 py-1" style="font-size: 0.6rem; border-radius:10px;">${user.perfil}</span></td>
                    <td class="text-center">
                        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <button class="btn btn-sm btn-white border-end" onclick='editarUsuario(${JSON.stringify(user)})'>
                                <i class="bi bi-pencil-square text-primary"></i>
                            </button>
                            <button class="btn btn-sm btn-white" onclick="eliminarUsuario(${user.id_usuario})">
                                <i class="bi bi-trash3 text-danger"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        });
    } catch (error) {
        cuerpoTabla.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error de conexión con el servicio de datos.</td></tr>';
    }
}

/**
 * 5. SEGURIDAD Y CONTROL DE SESIÓN
 */
function manejarSesionExpirada() {
    localStorage.removeItem('token'); 
    Swal.fire({
        title: 'Sesión Expirada',
        text: 'Por motivos de seguridad institutional, su sesión ha finalizado. Por favor, reingrese sus credenciales.',
        icon: 'error',
        confirmButtonColor: '#5B3D66'
    }).then(() => window.location.href = 'login.php');
}

// Inicialización de la carga de datos al completar el ciclo de vida del DOM
document.addEventListener("DOMContentLoaded", cargarUsuarios);