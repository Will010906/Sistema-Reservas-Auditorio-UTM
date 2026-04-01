<?php
/**
 * GESTIÓN DE USUARIOS - SIRA UTM
 * Actualizado: Seguridad JWT y Comunicación Asíncrona (Fetch API).
 */
include("config/db_local.php");

// Nota: La seguridad se delega al Token JWT en el cliente para cumplir
// con el estándar de arquitectura desacoplada exigido.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - UTM</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">

    <script>
       // BLOQUEO DE SEGURIDAD JWT (Requisito 30% Seguridad)
const token = localStorage.getItem('sira_session_token'); // <-- Cambiado de 'token' a 'sira_session_token'
if (!token) {
    window.location.href = 'login.php?error=expired';
}
    </script>

    <style>
        body { background-color: #f8f9fa; }
        .avatar-circle-sm {
            width: 35px; height: 35px;
            background-color: #5B3D66; color: white;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 0.85rem; font-weight: bold;
            text-transform: uppercase;
        }
        .table-card { border: none; border-radius: 15px; overflow: hidden; background: #fff; }
        .x-small { font-size: 0.75rem; }
        .bg-primary-subtle { background-color: #e0d4e5 !important; }
        .text-primary { color: #5B3D66 !important; }

        .btn-utm {
    background-color: #5B3D66 !important; /* El color de tu sidebar */
    border-color: #5B3D66 !important;
    color: white !important;
}

.btn-utm:hover {
    background-color: #4a3254 !important; /* Un tono más oscuro para el hover */
    border-color: #4a3254 !important;
}
/* Colores de Roles SIRA Premium */
.sira-badge-admin { 
    background-color: #f8d7da !important; 
    color: #842029 !important; 
    border: 1px solid #f5c2c7;
    font-weight: 800;
}

.sira-badge-sub { 
    background-color: #fff3cd !important; 
    color: #856404 !important; 
    border: 1px solid #ffeeba;
    font-weight: 800;
}

.sira-badge-doc { 
    background-color: #cfe2ff !important; 
    color: #084298 !important; 
    border: 1px solid #b6d4fe;
    font-weight: 800;
}

.sira-badge-alu { 
    background-color: #d1e7dd !important; 
    color: #0f5132 !important; 
    border: 1px solid #badbcc;
    font-weight: 800;
}
    </style>
</head>

<body class="bg-light">
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-0">Gestión de Usuarios</h1>
                    <p class="text-muted small">Administra las cuentas y permisos del sistema</p>
                </div>
                <button class="btn btn-utm rounded-pill px-4 shadow-sm fw-bold" onclick="prepararNuevoUsuario()">
    <i class="bi bi-person-plus-fill me-2"></i> Nuevo Usuario
</button>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="buscadorUsuarios" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre, matrícula o carrera...">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm table-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaUsuarios">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-4">Usuario</th>
                                    <th>Matrícula</th>
                                    <th>Teléfono</th>
                                    <th>Área / Carrera</th>
                                    <th>Rol</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                           <tbody id="listaUsuariosBody"> 
    <tr>
        <td colspan="7" class="text-center py-5">
          <div class="spinner-border" style="color: #5B3D66;" role="status"></div>
            <p class="mt-2 text-muted">Sincronizando base de datos...</p>
        </td>
    </tr>
</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="tituloModalUsuario">Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formUsuario">
                    <div class="modal-body p-4">
                        <input type="hidden" name="id_usuario" id="user_id">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nombre Completo</label>
                            <input type="text" name="nombre" id="user_nombre" class="form-control rounded-3" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Teléfono / WhatsApp</label>
                            <input type="tel" name="telefono" id="user_telefono" class="form-control rounded-3" placeholder="Ej. 4431234567">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                            <input type="email" name="correo_electronico" id="user_correo" class="form-control rounded-3" required>
                        </div>
                        
                        <div class="mb-3" id="bloque_matricula">
                            <label class="form-label small fw-bold text-muted">Matrícula / ID</label>
                            <input type="text" name="matricula" id="user_matricula" class="form-control rounded-3">
                        </div>
                        
                        <div class="mb-3" id="bloque_password">
                            <label class="form-label small fw-bold text-muted">Contraseña Inicial</label>
                            <input type="password" name="password" id="user_pass" class="form-control rounded-3" placeholder="Mínimo 8 caracteres">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Carrera / Área</label>
                                <select name="carrera_area" id="user_carrera" class="form-select rounded-3" required>
                                    <option value="" selected disabled>Selecciona...</option>
                                    <option value="Enfermería">Enfermería</option>
                                    <option value="Electromovilidad">Electromovilidad</option>
                                    <option value="Asesor Financiero">Asesor Financiero</option>
                                    <option value="Tecnologías de la Información e Innovación Digital">Tecnologías de la Información e Innovación Digital</option>
                                    <option value="Mecatrónica">Mecatrónica</option>
                                    <option value="Mantenimiento Industrial">Mantenimiento Industrial</option>
                                    <option value="Gastronomía">Gastronomía</option>
                                    <option value="Energía y Desarrollo Sostenible">Energía y Desarrollo Sostenible</option>
                                    <option value="Diseño Textil y Moda">Diseño Textil y Moda</option>
                                    <option value="Biotecnología">Biotecnología</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Perfil</label>
                                <select name="perfil" id="user_perfil" class="form-select rounded-3" required>
                                    <option value="alumno">Alumno</option>
                                    <option value="docente">Docente</option>
                                    <option value="subdirector">Subdirector</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-utm rounded-pill px-4 fw-bold" id="btnGuardarUser">
    Guardar Cambios
</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_usuarios.js"></script>
    <script src="assets/js/auth_check.js"></script>
</body>
</html>