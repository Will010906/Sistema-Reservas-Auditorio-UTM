<?php

/**
 * MÓDULO DE GESTIÓN DE USUARIOS - UTM
 * Descripción: Interfaz administrativa para crear, editar y eliminar usuarios.
 * Funcionalidades:
 * - Listado dinámico de usuarios con buscador en tiempo real (JS).
 * - Generación de avatares iniciales automáticos.
 * - Modal adaptativo para registro y edición de perfiles.
 */
session_start();
include("config/db_local.php");

// Verificación de seguridad: Bloquea acceso a usuarios no autenticados
if (!isset($_SESSION['nombre'])) {
    header("Location: index.php");
    exit();
}

// Consulta de usuarios ordenados alfabéticamente por nombre
// En la parte superior de admin_usuarios.php
$query = "SELECT * FROM usuarios ORDER BY nombre ASC";
$resultado = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - UTM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        /* Avatar circular con la inicial del usuario */
        .avatar-circle-sm {
            width: 35px;
            height: 35px;
            background-color: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .table-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .x-small {
            font-size: 0.75rem;
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
                <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" onclick="prepararNuevoUsuario()">
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
                            <table class="table table-hover align-middle mb-0" id="tablaUsuarios">
                                <thead class="table-light">
                                    <tr class="small text-muted text-uppercase">
                                        <th class="ps-4">Usuario</th>
                                        <th>Matrícula</th>
                                        <th>Contacto</th>
                                        <th>Teléfono</th>
                                        <th>Área / Carrera</th>
                                        <th>Rol</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($resultado)): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle-sm me-3">
                                                        <?php echo substr($user['nombre'], 0, 1); ?>
                                                    </div>
                                                    <div class="fw-bold text-dark small"><?php echo $user['nombre']; ?></div>
                                                </div>
                                            </td>
                                            <td class="small text-muted"><?php echo $user['matricula']; ?></td>
                                            <td class="small"><?php echo $user['correo_electronico']; ?></td>

                                            <td class="small">
                                                <?php if (!empty($user['telefono'])): ?>
                                                    <a href="https://wa.me/52<?php echo $user['telefono']; ?>" target="_blank" class="text-decoration-none text-muted">
                                                        <i class="bi bi-whatsapp text-success me-1"></i>
                                                        <?php echo $user['telefono']; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted x-small italic">No registrado</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="small"><?php echo $user['carrera_area']; ?></td>
                                            <td>
                                                <span class="badge rounded-pill bg-primary-subtle text-primary x-small">
                                                    <?php echo strtoupper($user['perfil']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-light rounded-circle me-1" title="Editar" onclick='editarUsuario(<?php echo json_encode($user); ?>)'>
                                                        <i class="bi bi-pencil text-primary"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light rounded-circle" title="Eliminar" onclick="eliminarUsuario(<?php echo $user['id_usuario']; ?>)">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                        <div class="modal-header bg-dark text-white rounded-top-4">
                                            <h5 class="modal-title fw-bold" id="tituloModalUsuario">Usuario</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form id="formUsuario" method="POST">
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="id_usuario" id="user_id">

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Nombre Completo</label>
                                                    <input type="text" name="nombre" id="user_nombre" class="form-control rounded-3" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Teléfono / WhatsApp</label>
                                                    <input type="tel" name="telefono" id="user_telefono" class="form-control rounded-3">
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
                                                    <input type="password" name="password" id="user_pass" class="form-control rounded-3">
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
                                                        <select name="perfil" id="user_perfil" class="form-select rounded-3">
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
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnGuardarUser">Guardar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                            <script src="assets/js/admin_usuarios.js"></script>
</body>

</html>