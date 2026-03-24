/**
 * GESTIÓN DE USUARIOS - NIVEL TSU
 * Implementa: Async/Await, Seguridad JWT y Respuesta JSON
 */

/* global Swal, bootstrap */

// --- BÚSQUEDA EN TIEMPO REAL (15% Filtros Útiles) ---
document.getElementById('buscadorUsuarios')?.addEventListener('keyup', function() {
    let valor = this.value.toLowerCase();
    let filas = document.querySelectorAll('#tablaUsuarios tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.innerText.toLowerCase().includes(valor) ? "" : "none";
    });
});

// --- FUNCIONES DE INTERFAZ (Síncronas) ---
function prepararNuevoUsuario() {
    const form = document.getElementById('formUsuario');
    if (!form) return;
    
    document.getElementById('tituloModalUsuario').innerText = 'Registrar Nuevo Usuario';
    // CORRECCIÓN: Ruta unificada
    form.action = 'api/admin/gestion_usuarios.php'; 
    form.reset();

    if (document.getElementById('bloque_matricula')) document.getElementById('bloque_matricula').style.display = 'block';
    if (document.getElementById('bloque_password')) document.getElementById('bloque_password').style.display = 'block';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario')).show();
}

function editarUsuario(user) {
    document.getElementById('tituloModalUsuario').innerText = 'Editar Usuario';
    // CORRECCIÓN: Ruta unificada para que el submit sepa a dónde ir
    document.getElementById('formUsuario').action = 'api/admin/gestion_usuarios.php';

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

// --- FUNCIONES DE COMUNICACIÓN (40% CRUD + 30% JWT) ---

/**
 * ELIMINACIÓN ASÍNCRONA
 */
async function eliminarUsuario(id) {
    const result = await Swal.fire({
        title: '¿Eliminar usuario?',
        text: "Esta acción es permanente y no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#5B3D66',
        confirmButtonText: 'Sí, eliminar',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            // USAMOS 'token' que es el nombre que definiste en tu bloqueo de seguridad
            const tokenSeguridad = localStorage.getItem('token'); 

            const response = await fetch('api/admin/gestion_usuarios.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${tokenSeguridad}`
                },
                // ENVIAMOS exactamente id_usuario como lo espera el PHP
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
            console.error("Error en el catch:", error);
            Swal.fire('Error', 'No se pudo conectar con el backend o el servidor falló.', 'error');
        }
    }
}

/**
 * FUNCIÓN PARA MANEJAR EL GUARDADO (NUEVO/EDITAR) VÍA FETCH
 */
document.getElementById('formUsuario')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    // IMPORTANTE: Asegúrate de que los nombres coincidan con el PHP
    const datos = {
     id_usuario: formData.get('id_usuario') ? parseInt(formData.get('id_usuario')) : null,
        nombre: formData.get('nombre'),
        matricula: formData.get('matricula'),
        telefono: formData.get('telefono'),
        correo_electronico: formData.get('correo_electronico'), // Nombre exacto del PHP
        carrera_area: formData.get('carrera_area'),
        perfil: formData.get('perfil'),
        password: formData.get('password')
    };

    const metodo = datos.id_usuario ? 'PUT' : 'POST'; 

    try {
        const response = await fetch('api/admin/gestion_usuarios.php', {
            method: metodo,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}` // Usa 'token'
            },
            body: JSON.stringify(datos)
        });

        if (response.status === 401) return manejarSesionExpirada();

        const data = await response.json();
        if (data.success) {
            Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.error, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error al procesar la solicitud.', 'error');
    }
});

function manejarSesionExpirada() {
    // CORRECCIÓN: Usamos el nombre 'token' para ser consistentes con el resto del JS
    localStorage.removeItem('token'); 
    Swal.fire({
        title: 'Sesión Expirada',
        text: 'Por seguridad, inicia sesión nuevamente.',
        icon: 'error',
        confirmButtonColor: '#5B3D66'
    }).then(() => window.location.href = 'login.php');
}
/**
 * CARGA DINÁMICA DE USUARIOS - SIRA UTM
 */
/**
 * CARGA DINÁMICA DE USUARIOS
 * Elimina el spinner y renderiza la tabla con datos reales.
 */
/**
 * CARGA DINÁMICA DE USUARIOS - SIRA UTM
 * Obtiene los datos del backend y genera las filas de la tabla.
 */
/**
 * CARGA DINÁMICA DE USUARIOS - SIRA UTM
 * Obtiene los datos del backend y genera las filas de la tabla.
 */
async function cargarUsuarios() {
    const cuerpoTabla = document.getElementById("listaUsuariosBody");
    if (!cuerpoTabla) return;

    try {
        // 1. Petición GET a tu API
// BUSCA ESTA LÍNEA (alrededor de la 158):
const response = await fetch("api/admin/gestion_usuarios.php", {
    method: 'GET',
    headers: {
        // CAMBIA 'token' por 'sira_session_token' o viceversa. 
        // Usa el que definiste en tu login. Si en el PHP usas 'token', úsalo aquí:
        'Authorization': `Bearer ${localStorage.getItem("token")}` 
    }
});

        if (response.status === 401) return manejarSesionExpirada();

        const data = await response.json();
        
        // 2. Limpiar el spinner de "Sincronizando..."
        cuerpoTabla.innerHTML = ""; 

        if (data.length === 0) {
            cuerpoTabla.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron usuarios registrados.</td></tr>';
            return;
        }

        // 3. Renderizar cada fila con el diseño de círculos (avatars)
        data.forEach(user => {
            // Generar iniciales para el avatar (Ej: Wilmer A -> WA)
            const iniciales = user.nombre ? user.nombre.split(' ').map(n => n[0]).join('').substring(0, 2) : '??';
            
            // Badge de Rol con estilo institucional
            const badgeClass = user.perfil === 'administrador' ? 'bg-primary-subtle text-primary' : 
                              user.perfil === 'subdirector' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-light text-dark border';

            cuerpoTabla.innerHTML += `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle-sm me-3">${iniciales}</div>
                            <div>
                                <div class="fw-bold text-dark">${user.nombre}</div>
                                <div class="text-muted x-small">${user.correo_electronico}</div>
                            </div>
                        </div>
                    </td>
                    <td><code class="fw-bold" style="color: #5B3D66;">${user.matricula || 'N/A'}</code></td>
                    <td><small class="text-muted">${user.correo_electronico}</small></td>
                    <td>${user.telefono || '<span class="text-muted">---</span>'}</td>
                    <td><span class="small">${user.carrera_area}</span></td>
                    <td><span class="badge ${badgeClass} text-uppercase px-2 py-1" style="font-size: 0.65rem;">${user.perfil}</span></td>
                    <td class="text-center">
                        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <button class="btn btn-sm btn-white border-end" onclick='editarUsuario(${JSON.stringify(user)})' title="Editar">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </button>
                            <button class="btn btn-sm btn-white" onclick="eliminarUsuario(${user.id_usuario})" title="Eliminar">
                                <i class="bi bi-trash3 text-danger"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        });
    } catch (error) {
        console.error("Error cargando usuarios:", error);
        cuerpoTabla.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error de conexión con el servidor.</td></tr>';
    }
}


// Inicializar carga al abrir la página o al refrescar el DOM
document.addEventListener("DOMContentLoaded", cargarUsuarios);

