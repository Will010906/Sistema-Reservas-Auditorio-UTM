<?php

/**
 * PANEL DE ADMINISTRACIÓN - SIRA UTM 
 * Ajustado para coincidir con los IDs de admin_interactivo.js
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

    <style>
        :root {
            --sira-purple-dark: #2D1B33;
            --sira-purple-primary: #5B3D66;
            --sira-bg: #EBEFF2;
            --grad-urgent: linear-gradient(135deg, #FF6B6B 0%, #EE5253 100%);
            --grad-pending: linear-gradient(135deg, #FFD93D 0%, #F9A825 100%);
            --grad-ontime: linear-gradient(135deg, #6BCB77 0%, #46A351 100%);
            --grad-accepted: linear-gradient(135deg, #42A5F5 0%, #1E88E5 100%);
            --grad-rejected: linear-gradient(135deg, #B0BEC5 0%, #78909C 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--sira-bg);
            margin: 0;
            color: #2D2D2D;
        }

        .activity-bar {
            background-color: var(--sira-purple-dark);
            width: 80px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1001;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 25px;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .side-bar {
            background-color: #FDFBFF;
            width: 230px;
            min-height: 100vh;
            position: fixed;
            left: 80px;
            top: 0;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            padding: 30px 15px;
            z-index: 1000;
        }

        .main-content {
            margin-left: 310px;
            padding: 30px 40px;
            width: calc(100% - 310px);
        }

        .card-sira {
            border: none;
            border-radius: 20px;
            padding: 12px 15px;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.04);
            height: 95px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-sira .count {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
            z-index: 2;
            color: white;
        }

        .card-sira h6 {
            font-size: 0.55rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            z-index: 2;
            margin-bottom: 2px;
            color: white;
        }

        .watermark {
            position: absolute;
            bottom: -5px;
            right: -5px;
            font-size: 3.2rem;
            opacity: 0.15;
            transform: rotate(-10deg);
            color: white;
        }

        .bg-urg {
            background: var(--grad-urgent);
        }

        .bg-pen {
            background: var(--grad-pending);
        }

        .bg-pen h6,
        .bg-pen .count,
        .bg-pen .watermark {
            color: #2D1B33;
        }

        .bg-ont {
            background: var(--grad-ontime);
        }

        .bg-acc {
            background: var(--grad-accepted);
        }

        .bg-rej {
            background: var(--grad-rejected);
        }

        .table-container {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            min-width: 105px;
            text-align: center;
            display: inline-block;
        }

        .st-urgente {
            background: linear-gradient(135deg, #FF6B6B 0%, #EE5253 100%) !important;
            color: white !important;
        }

        .st-aceptada {
            background: var(--grad-accepted);
            color: white !important;
        }

        .st-rechazada {
            background: var(--grad-rejected);
            color: white !important;
        }

        .st-demorada {
            background: linear-gradient(135deg, #FFD93D 0%, #F9A825 100%) !important;
            color: #2D1B33 !important;
        }

        .st-atiempo {
            background: linear-gradient(135deg, #6BCB77 0%, #46A351 100%) !important;
            color: white !important;
        }

        .user-header-profile {
            background: white;
            padding: 6px 16px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Botón Gestionar con Identidad UTM */
        .btn-gestionar-sira {
            background-color: transparent !important;
            border: 1.2px solid var(--sira-purple-primary) !important;
            color: var(--sira-purple-primary) !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            padding: 6px 16px !important;
            border-radius: 50px !important;
            transition: all 0.3s ease !important;
        }

        .btn-gestionar-sira:hover {
            background-color: var(--sira-purple-primary) !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 61, 102, 0.2) !important;
        }
    </style>
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
                    <thead>
                        <tr class="text-muted x-small fw-bold text-uppercase">
                            <th class="ps-4">Folio</th>
                            <th>Evento / Solicitante</th>
                            <th>Auditorio</th>
                            <th>Fecha</th>
                            <th class="text-center">Estatus</th>
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

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/admin_interactivo.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</body>

</html>