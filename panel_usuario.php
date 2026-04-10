<?php

/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: PANEL OPERATIVO DEL USUARIO (ALUMNOS/DOCENTES)
 * * @package     Frontend_User
 * @subpackage  Dashboard_View
 * @version     3.5.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Interfaz de seguimiento y creación de solicitudes. Implementa un sistema de 
 * indicadores (KPIs) asíncronos y una tabla de datos dinámica con filtrado 
 * multicriterio en el lado del cliente.
 */
include("config/db_local.php");
// SIRA - Bloqueo de caché a nivel servidor
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Mis Reservaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="assets/css/panel_usuario.css">

    <script src="assets/js/auth_check.js"></script>


</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1 fw-800" style="color: var(--sira-purple-dark);">Mis Reservaciones</h1>
                <p class="text-muted small mb-0">Gestiona tus solicitudes de auditorio en la UTM.</p>
            </div>

            <div class="user-header-profile shadow-sm bg-white p-2 px-3 rounded-pill d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold small" id="nombreUsuarioHeader" style="font-size: 0.85rem; color: #2D1B33;">Cargando...</div>
                    <small class="text-muted fw-bold text-uppercase" id="rolUsuarioHeader" style="font-size: 0.6rem;">Cargando...</small>
                </div>
                <div id="inicialAvatarUsuario" class="d-flex align-items-center justify-content-center text-white fw-bold"
                    style="width: 40px; height: 40px; background: var(--sira-purple-primary); border-radius: 50%;">U</div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-nueva-solicitud shadow-sm" onclick="abrirModalNuevaReservacion()">
                <i class="bi bi-plus-lg me-2"></i> Nueva Solicitud
            </button>
        </div>

        <div class="row g-3 mb-4">
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card-user bg-pend">
                    <h6>En Revisión</h6>
                    <div class="count" id="countPendientes">0</div><i class="bi bi-clock-history watermark"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-user bg-acep">
                    <h6>Aprobadas</h6>
                    <div class="count" id="countAprobadas">0</div><i class="bi bi-check-circle-fill watermark"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-user bg-rech">
                    <h6>Rechazadas</h6>
                    <div class="count" id="countRechazadas">0</div><i class="bi bi-x-circle-fill watermark"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 mb-4 rounded-4 shadow-sm border-0">
    <div class="d-flex justify-content-between align-items-center">
        
        <div id="filtros_estatus">
            <span class="small fw-bold text-muted me-3 text-uppercase" style="letter-spacing: 0.5px;">
                Filtrar Estatus:
            </span>
            
            <div class="form-check form-check-inline">
                <input class="form-check-input check-filtro" type="checkbox" value="Pendiente" id="f_pen">
                <label class="form-check-label text-warning fw-bold small" for="f_pen">Pendientes</label>
            </div>
            
            <div class="form-check form-check-inline">
                <input class="form-check-input check-filtro" type="checkbox" value="Aceptada" id="f_apr">
                <label class="form-check-label text-info fw-bold small" for="f_apr">Aprobadas</label>
            </div>
            
            <div class="form-check form-check-inline">
                <input class="form-check-input check-filtro" type="checkbox" value="Rechazada" id="f_rec">
                <label class="form-check-label text-danger fw-bold small" for="f_rec">Rechazadas</label>
            </div>
        </div>

        <button class="btn btn-dark btn-sm rounded-pill px-4 fw-bold shadow-sm" 
                onclick="window.limpiarFiltros()">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar Filtros
        </button>
        
    </div>
</div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaMisReservas" style="font-size: 0.85rem;">
                    <thead>
                        <tr class="text-muted x-small fw-bold text-uppercase border-bottom">
                            <th class="ps-4">Folio</th>
                            <th>Título del Evento</th>
                            <th>Auditorio</th>
                            <th>Fecha</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="contenedorMisReservas">
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No tienes solicitudes registradas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/modal_reservacion.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="assets/js/usuario_reservas.js"></script>
</body>

</html>