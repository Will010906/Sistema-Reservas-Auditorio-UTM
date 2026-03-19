<?php

/**
 * PANEL DE ADMINISTRACIÓN - SIRA UTM 
 * VERSIÓN FINAL INTEGRADA: Sidebar Completo, Cards Compactas y Modal funcional.
 */
session_start();
if (!isset($_SESSION['nombre'])) {
    header("Location: index.php");
    exit();
}
include 'config/db_local.php';

// Consultas estadísticas
$res_urg = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Urgente' AND estado = 'Pendiente'");
$urgentes = mysqli_fetch_assoc($res_urg)['t'];
$res_dem = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Pendiente' AND estado = 'Pendiente'");
$demorados = mysqli_fetch_assoc($res_dem)['t'];
$res_tiem = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Con tiempo' AND estado = 'Pendiente'");
$a_tiempo = mysqli_fetch_assoc($res_tiem)['t'];
$res_acep = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE estado = 'Aceptada'");
$total_aceptadas = mysqli_fetch_assoc($res_acep)['t'];
$res_rech = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE estado = 'Rechazada'");
$total_rechazadas = mysqli_fetch_assoc($res_rech)['t'];

$rol_usuario = $_SESSION['rol'] ?? 'Admin';
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

        /* --- SIDEBAR CORREGIDO (3 SECCIONES) --- */
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

        .nav-section-title {
            font-size: 0.65rem;
            font-weight: 800;
            color: #adb5bd;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin: 20px 0 10px 10px;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 14px;
            color: #6c757d;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .nav-link-custom i {
            font-size: 1.1rem;
            margin-right: 12px;
        }

        .nav-link-custom.active {
            background-color: #F4EFFF;
            color: var(--sira-purple-primary);
        }

        .nav-link-custom:hover:not(.active) {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        /* --- CARDS COMPACTAS --- */
        .card-sira {
            border: none;
            border-radius: 20px;
            padding: 15px 20px;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.04);
            height: 105px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-sira .count {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            z-index: 2;
            color: white;
        }

        .card-sira h6 {
            font-size: 0.6rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            z-index: 2;
            margin-bottom: 2px;
            color: white;
        }

        .bg-pen h6,
        .bg-pen .count {
            color: #2D1B33;
        }

        /* Ajuste para amarillo */
        .watermark {
            position: absolute;
            bottom: -5px;
            right: -5px;
            font-size: 3.2rem;
            opacity: 0.15;
            transform: rotate(-10deg);
            color: white;
        }

        .bg-pen .watermark {
            color: #2D1B33;
            opacity: 0.1;
        }

        .bg-urg {
            background: var(--grad-urgent);
        }

        .bg-pen {
            background: var(--grad-pending);
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

        /* --- TABLA Y BOTONES --- */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        }

        .btn-stalked {
            border: none;
            border-radius: 10px;
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-gestionar-sira {
            background-color: var(--sira-purple-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 6px 18px;
            transition: 0.3s;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            min-width: 105px;
            text-align: center;
        }

        .st-urgente {
            background: var(--grad-urgent);
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

        /* Colores para los estados que faltaban */
        .st-pendiente {
            background: var(--grad-pending) !important;
            color: #2D1B33 !important;
        }

        .st-tiempo {
            background: var(--grad-ontime) !important;
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
    </style>
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-800 h3 mb-0" style="color: var(--sira-purple-dark); font-weight: 800;">Panel Administrador</h1>
                <p class="text-muted x-small mb-0">UTM • Sistema de Reservación de Auditorios</p>
            </div>
            <div class="user-header-profile">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold small" style="font-size: 0.75rem;"><?php echo $_SESSION['nombre']; ?></div>
                    <small class="text-muted fw-bold" style="font-size: 0.55rem;">ADMINISTRADOR</small>
                </div>
                <div style="width: 32px; height: 32px; background: var(--sira-purple-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-9">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card-sira bg-urg">
                            <h6>Urgentes</h6>
                            <div class="count"><?php echo $urgentes; ?></div><i class="bi bi-exclamation-octagon watermark"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-sira bg-pen">
                            <h6>Pendientes</h6>
                            <div class="count"><?php echo $demorados; ?></div><i class="bi bi-clock-history watermark"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-sira bg-ont">
                            <h6>A Tiempo</h6>
                            <div class="count"><?php echo $a_tiempo; ?></div><i class="bi bi-check2-circle watermark"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex flex-column gap-2 h-100">
                    <div class="card-sira bg-acc flex-fill">
                        <h6>Aceptadas</h6>
                        <div class="count" style="font-size: 1.6rem;"><?php echo $total_aceptadas; ?></div><i class="bi bi-hand-thumbs-up watermark" style="font-size: 2.2rem;"></i>
                    </div>
                    <div class="card-sira bg-rej flex-fill">
                        <h6>Rechazadas</h6>
                        <div class="count" style="font-size: 1.6rem;"><?php echo $total_rechazadas; ?></div><i class="bi bi-hand-thumbs-down watermark" style="font-size: 2.2rem;"></i>
                    </div>
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
            <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="URGENTE" id="chkUrg"><label class="form-check-label x-small fw-bold text-danger">Urgentes</label></div>
            <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="PENDIENTE" id="chkPen"><label class="form-check-label x-small fw-bold text-warning">Pendientes</label></div>
            <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="CON TIEMPO" id="chkTie"><label class="form-check-label x-small fw-bold text-success">A Tiempo</label></div>
            <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="ACEPTADA" id="chkAce"><label class="form-check-label x-small fw-bold text-info">Aceptadas</label></div>
            <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="RECHAZADA" id="chkRec"><label class="form-check-label x-small fw-bold text-secondary">Rechazadas</label></div>
        </div>
    </div>

    <div class="col-lg-3">
        <label class="form-label fw-bold x-small text-muted text-uppercase mb-2">Rango de Fechas</label>
        <div class="d-flex align-items-center gap-1 bg-light p-1 rounded-3 border" style="max-width: 250px;">
            <input type="date" id="fecha_inicio" class="form-control form-control-sm border-0 bg-transparent p-1" style="font-size: 0.7rem;">
            <i class="bi bi-arrow-right text-muted small"></i>
            <input type="date" id="fecha_fin" class="form-control form-control-sm border-0 bg-transparent p-1" style="font-size: 0.7rem;">
        </div>
    </div>

    <div class="col-lg-2">
        <button class="btn btn-stalked btn-dark btn-sm w-100 mb-1" onclick="resetFiltros()">Limpiar</button>
        <button id="btnPDF" class="btn btn-stalked btn-danger btn-sm w-100" onclick="descargarReporte()">PDF</button>
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
                    <tbody>
                        <?php
                        $query = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio FROM solicitudes s JOIN usuarios u ON s.id_usuario = u.id_usuario JOIN auditorio a ON s.id_auditorio = a.id_auditorio ORDER BY s.id_solicitud DESC";
                        $resultado = mysqli_query($conexion, $query);
                        while ($row = mysqli_fetch_assoc($resultado)):
                            if ($row['estado'] == 'Pendiente') {
                                if ($row['prioridad'] == 'Urgente') {
                                    $st_class = 'st-urgente';
                                    $txt = 'URGENTE';
                                } elseif ($row['prioridad'] == 'Pendiente') {
                                    $st_class = 'st-pendiente';
                                    $txt = 'PENDIENTE';
                                } else {
                                    $st_class = 'st-tiempo';
                                    $txt = 'A TIEMPO';
                                }
                            } else {
                                $st_class = ($row['estado'] == 'Aceptada') ? 'st-aceptada' : 'st-rechazada';
                                $txt = strtoupper($row['estado']);
                            }
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold" style="color: var(--sira-purple-primary);">#<?php echo $row['folio']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo $row['titulo_event']; ?></div>
                                    <div class="text-muted x-small">Por: <?php echo $row['nombre_usuario']; ?></div>
                                </td>
                                <td><span class="badge rounded-pill bg-light text-dark border px-3"><?php echo $row['nombre_espacio']; ?></span></td>
                                <td class="fw-bold text-muted"><?php echo $row['fecha_evento']; ?></td>
                                <td class="text-center"><span class="badge-status <?php echo $st_class; ?> shadow-sm"><?php echo $txt; ?></span></td>
                                <td class="text-center"><button class="btn btn-gestionar-sira btn-sm shadow-sm" onclick="gestionar(<?php echo $row['id_solicitud']; ?>)">Gestionar</button></td>
                            </tr>
                        <?php endwhile; ?>
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

</body>

</html>