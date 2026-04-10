<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: GESTIÓN DE PADRÓN DE USUARIOS (ADMIN)
 * * @package     Frontend_Admin
 * @subpackage  User_Management_View
 * @author      Wilmer (Estudiante de Tecnologías de la Información, UTM)
 * @version     2.8.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Interfaz administrativa para el control de identidades institucionales. 
 * Implementa un buscador reactivo, gestión de perfiles (RBAC) y un sistema 
 * de visualización de contraseñas para soporte técnico inicial.
 */
include("config/db_local.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Gestión de Usuarios</title>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="assets/css/admin_usuarios.css">

    <script>
        /**
         * MIDDLEWARE DE SEGURIDAD (CLIENT-SIDE)
         * Verifica la existencia de 'sira_session_token' antes de procesar el DOM.
         * Redirige al punto de acceso si el token ha expirado o es nulo.
         */
        const token = localStorage.getItem('sira_session_token'); 
        if (!token) {
            window.location.href = 'login.php?error=expired';
        }
    </script>
</head>

<body class="bg-light">
    <div class="wrapper d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-0">Gestión de Usuarios</h1>
                    <p class="text-muted small">Control de credenciales, roles institucionales y permisos de área</p>
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
                        <input type="text" id="buscadorUsuarios" class="form-control border-start-0 ps-0" 
                               placeholder="Filtrar por nombre, matrícula o carrera en tiempo real...">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm table-card border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaUsuarios">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase" style="letter-spacing: 0.5px;">
                                    <th class="ps-4">Identidad</th>
                                    <th>Matrícula / ID</th>
                                    <th>Teléfono</th>
                                    <th>División Académica</th>
                                    <th>Nivel de Acceso</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="listaUsuariosBody"> 
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="spinner-border" style="color: #5B3D66;" role="status"></div>
                                        <p class="mt-2 text-muted">Sincronizando con el servidor de identidades...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="tituloModalUsuario">Configuración de Cuenta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form id="formUsuario">
                    <div class="modal-body p-4">
                        <input type="hidden" name="id_usuario" id="user_id">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Nombre Completo</label>
                            <input type="text" name="nombre" id="user_nombre" class="form-control rounded-3" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Teléfono de Contacto</label>
                            <input type="tel" name="telefono" id="user_telefono" class="form-control rounded-3" placeholder="10 dígitos">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Correo Institucional</label>
                            <input type="email" name="correo_electronico" id="user_correo" class="form-control rounded-3" required>
                        </div>
                        
                        <div class="mb-3" id="bloque_matricula">
                            <label class="form-label small fw-bold text-muted text-uppercase">Matrícula Universitaria</label>
                            <input type="text" name="matricula" id="user_matricula" class="form-control rounded-3">
                        </div>
                        
                        <div class="mb-3" id="bloque_password">
                            <label class="form-label small fw-bold text-muted text-uppercase">Contraseña de Acceso</label>
                            <div class="input-group position-relative sira-password-toggle">
                                <input type="password" name="password" id="user_pass" class="form-control rounded-3" 
                                       placeholder="Mínimo 8 caracteres">
                                
                                <button type="button" id="togglePassword" 
                                        class="btn position-absolute top-50 translate-middle-y end-0 me-1 p-1 text-muted" 
                                        style="border: none; background: none; z-index: 10;">
                                    <i class="bi bi-eye-slash fs-5" id="iconoOjo"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Carrera / Área</label>
                                <select name="carrera_area" id="user_carrera" class="form-select rounded-3" required>
                                    <option value="" selected disabled>Seleccionar...</option>
                                    <option value="Tecnologías de la Información e Innovación Digital">T.I.</option>
                                    <option value="Gastronomía">Gastronomía</option>
                                    <option value="Mecatrónica">Mecatrónica</option>
                                    </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Perfil (RBAC)</label>
                                <select name="perfil" id="user_perfil" class="form-select rounded-3" required>
                                    <option value="alumno">Alumno</option>
                                    <option value="docente">Docente</option>
                                    <option value="subdirector">Subdirector</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-utm rounded-pill px-4 fw-bold" id="btnGuardarUser">
                            Aplicar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/contrasena_toggle.js"></script>
    <script src="assets/js/admin_usuarios.js"></script>
    <script src="assets/js/auth_check.js"></script>
</body>
</html>