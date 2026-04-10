<?php

/**
 * SIRA - DASHBOARD ADMINISTRATIVO 
 * * MÓDULO: NÚCLEO DE GESTIÓN Y ANALÍTICA
 * * @package     Frontend_Admin
 * @subpackage  Dashboard_Controller
 * @version     4.2.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN:
 * Centraliza las herramientas de dictamen, monitoreo de indicadores de urgencia
 * y auditoría de reservaciones de auditorios.
 */
include 'config/db_local.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Dashboard Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="assets/js/auth_check.js"></script>
    <link rel="stylesheet" href="assets/css/admin_dashboard.css?v=1.1">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-800 h3 mb-0" style="color: var(--sira-purple-dark);">Panel Administrador</h1>
                <p class="text-muted x-small mb-0">UTM • Sistema de Reservación de Auditorios</p>
            </div>
            <div class="user-header-profile" id="perfilUsuarioHeader">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold small" style="font-size: 0.75rem;" id="nombreAdmin">Cargando...</div>
                    <small class="text-muted fw-bold" style="font-size: 0.55rem;">ADMINISTRADOR</small>
                </div>
                <div id="inicialAvatar" style="width: 32px; height: 32px; background: var(--sira-purple-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    U
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col">
                <div class="card-sira bg-urg">
                    <h6>Urgentes</h6>
                    <div class="count" id="countUrgentes">0</div>
                    <i class="bi bi-exclamation-octagon watermark"></i>
                </div>
            </div>
            <div class="col">
                <div class="card-sira bg-pen">
                    <h6>Demoradas</h6>
                    <div class="count" id="countDemoradas">0</div>
                    <i class="bi bi-clock-history watermark"></i>
                </div>
            </div>

            <div class="col">
                <div class="card-sira bg-ont">
                    <h6>A Tiempo</h6>
                    <div class="count" id="countAtiempo">0</div>
                    <i class="bi bi-check2-circle watermark"></i>
                </div>
            </div>
            <div class="col">
                <div class="card-sira bg-acc">
                    <h6>Aceptadas</h6>
                    <div class="count" id="countAceptadas">0</div>
                    <i class="bi bi-hand-thumbs-up watermark"></i>
                </div>
            </div>
            <div class="col">
                <div class="card-sira bg-rej">
                    <h6>Rechazadas</h6>
                    <div class="count" id="countRechazadas">0</div>
                    <i class="bi bi-hand-thumbs-down watermark"></i>
                </div>
            </div>
        </div>


        <div class="bg-white p-3 mb-4 rounded-4 shadow-sm border-0">
            <div class="row g-3 align-items-end">
                <div class="col-lg-7">
                    <label class="form-label fw-bold x-small text-muted text-uppercase mb-2">Filtrar por Estatus</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="chkTodos" checked>
                            <label class="form-check-label x-small fw-bold text-primary">TODOS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="URGENTE" id="chkUrg">
                            <label class="form-check-label x-small fw-bold text-danger">Urgentes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="DEMORADA" id="chkDem">
                            <label class="form-check-label x-small fw-bold text-warning">Demoradas</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="A TIEMPO" id="chkTie">
                            <label class="form-check-label x-small fw-bold text-success">A Tiempo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="ACEPTADA" id="chkAce">
                            <label class="form-check-label x-small fw-bold text-info">Aceptadas</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="RECHAZADA" id="chkRec">
                            <label class="form-check-label x-small fw-bold text-secondary">Rechazadas</label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-bold x-small text-muted text-uppercase mb-2">Rango de Fechas</label>
                    <div class="d-flex align-items-center gap-1 bg-light p-1 rounded-3 border">
                        <input type="date" id="fecha_inicio" class="form-control form-control-sm border-0 bg-transparent p-1" style="font-size: 0.7rem;">
                        <i class="bi bi-arrow-right text-muted small"></i>
                        <input type="date" id="fecha_fin" class="form-control form-control-sm border-0 bg-transparent p-1" style="font-size: 0.7rem;">
                    </div>
                </div>
                <div class="col-lg-2">
                    <button class="btn btn-dark btn-sm w-100 mb-1 rounded-3 fw-bold" onclick="resetFiltros()">Limpiar</button>
                    <button id="btnPDF" class="btn btn-danger btn-sm w-100 rounded-3 fw-bold" onclick="descargarReporte()">PDF</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaSolicitudes" style="font-size: 0.85rem;">
                   <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Folio</th> 
                    
                    <th>Evento</th> 
                    
                    <th class="text-center">Auditorio</th> 
                    
                    <th class="text-center">Fecha</th> 
                    
                    <th class="text-center">Estado</th> 
                    
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
                    <tbody id="contenedorSolicitudes">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Sincronizando reservaciones...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/modal_detalle.php'; ?>
    <?php include 'includes/modal_reservacion.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="assets/js/usuario_reservas.js"></script>

    <script src="assets/js/admin_interactivo.js"></script>

    <link rel="stylesheet" href="assets/css/style.css">
</body>

</html>