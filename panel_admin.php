<?php

/**
 * PANEL DE ADMINISTRACIÓN - SIRA UTM
 * Versión Final: Recuperando estructura original con diseño SIRA.
 */
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: index.php");
    exit();
}
include 'config/db_local.php';

// --- SECCIÓN: ESTADÍSTICAS ---
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
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - SIRA UTM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sira-purple-dark: #714B75;
            --sira-purple-bg: #F4EFFF;
            --status-urgent: #FF8A80;
            --status-pending: #FFD180;
            --status-on-time: #B9F6CA;
            --status-accepted: #80D8FF;
            --status-rejected: #CFD8DC;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--sira-purple-bg);
        }

        .main-content {
            padding: 40px;
        }

        /* Estilos de Tarjetas Recuperados */
        .card-custom {
            border: none;
            border-radius: 15px;
            transition: transform 0.2s;
            color: #3D2C40;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        .card-urgent {
            background-color: var(--status-urgent) !important;
        }

        .card-pending {
            background-color: var(--status-pending) !important;
        }

        .card-on-time {
            background-color: var(--status-on-time) !important;
        }

        .card-accepted {
            background-color: var(--status-accepted) !important;
        }

        .card-rejected {
            background-color: var(--status-rejected) !important;
        }

        .badge-status {
            padding: 0.5em 1em;
            border-radius: 50px;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }

        .x-small {
            font-size: 0.7rem;
        }

        /* Estilo de Tabla SIRA */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .btn-gestionar {
            background-color: var(--sira-purple-dark);
            color: white;
            border-radius: 50px;
            border: none;
            padding: 5px 15px;
            font-weight: 600;
        }

        .btn-gestionar:hover {
            background-color: #5A3A5D;
            color: white;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content w-100">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-0">Panel Administrador</h1>
                    <p class="text-muted small">Bienvenido, <?php echo $_SESSION['nombre']; ?> (SIRA UTM)</p>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-9">
                    <div class="row g-3 h-100">
                        <div class="col-md-4">
                            <div class="card card-custom card-urgent shadow-sm h-100">
                                <div class="card-body text-center py-4">
                                    <h6 class="text-uppercase small opacity-75 fw-bold">Urgentes</h6>
                                    <div class="display-5 fw-bold"><?php echo $urgentes; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-custom card-pending shadow-sm h-100">
                                <div class="card-body text-center py-4">
                                    <h6 class="text-uppercase small opacity-75 fw-bold">Pendientes</h6>
                                    <div class="display-5 fw-bold"><?php echo $demorados; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-custom card-on-time shadow-sm h-100">
                                <div class="card-body text-center py-4">
                                    <h6 class="text-uppercase small opacity-75 fw-bold">A Tiempo</h6>
                                    <div class="display-5 fw-bold"><?php echo $a_tiempo; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex flex-column gap-3 h-100">
                        <div class="card card-custom card-accepted shadow-sm flex-fill d-flex align-items-center justify-content-center py-3">
                            <div class="text-center">
                                <h6 class="text-uppercase x-small mb-1 opacity-75 fw-bold">Aceptadas</h6>
                                <div class="h3 fw-bold mb-0"><?php echo $total_aceptadas; ?></div>
                            </div>
                        </div>
                        <div class="card card-custom card-rejected shadow-sm flex-fill d-flex align-items-center justify-content-center py-3">
                            <div class="text-center">
                                <h6 class="text-uppercase x-small mb-1 opacity-75 fw-bold">Rechazadas</h6>
                                <div class="h3 fw-bold mb-0"><?php echo $total_rechazadas; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4 align-items-end bg-white p-3 rounded-4 shadow-sm border">
                <div class="col-lg-6">
                    <label class="form-label fw-bold small text-muted text-uppercase">Filtrar por Estatus</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="URGENTE" id="chkUrg" checked><label class="form-check-label small fw-bold" style="color: #d32f2f" for="chkUrg">Urgentes</label></div>
                        <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="PENDIENTE" id="chkPen" checked><label class="form-check-label small fw-bold text-warning" for="chkPen">Pendientes</label></div>
                        <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="CON TIEMPO" id="chkTie" checked><label class="form-check-label small fw-bold text-success" for="chkTie">A Tiempo</label></div>
                        <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="ACEPTADA" id="chkAce"><label class="form-check-label small fw-bold text-info" for="chkAce">Aceptadas</label></div>
                        <div class="form-check"><input class="form-check-input filter-check" type="checkbox" value="RECHAZADA" id="chkRechazada"><label class="form-check-label small fw-bold text-secondary" for="chkRechazada">Rechazadas</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Rango de Fechas</label>
                    <div class="input-group input-group-sm">
                        <input type="date" id="fecha_inicio" class="form-control">
                        <span class="input-group-text bg-white"><i class="bi bi-arrow-right"></i></span>
                        <input type="date" id="fecha_fin" class="form-control">
                    </div>
                </div>

                <div class="col-lg-2 d-flex gap-2">
    <button class="btn btn-dark btn-sm w-100 rounded-pill" onclick="resetFiltros()">
        <i class="bi bi-trash-fill me-1"></i> Limpiar
    </button>
    
    <button class="btn btn-danger btn-sm w-100 rounded-pill" onclick="descargarReporte()">
        <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
    </button>
</div>

            <div class="table-container shadow-sm border-0 overflow-hidden">
                <table class="table table-hover align-middle mb-0" id="tablaSolicitudes">
                    <thead class="table-light text-uppercase small">
                        <tr>
                            <th class="ps-4">Folio</th>
                            <th>Solicitante / Evento</th>
                            <th>Auditorio</th>
                            <th>Fecha</th>
                            <th>Estatus</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
                                  FROM solicitudes s
                                  JOIN usuarios u ON s.id_usuario = u.id_usuario
                                  JOIN auditorio a ON s.id_auditorio = a.id_auditorio
                                  ORDER BY s.id_solicitud DESC";
                        $resultado = mysqli_query($conexion, $query);
                        while ($row = mysqli_fetch_assoc($resultado)):
                            if ($row['estado'] == 'Pendiente') {
                                $bg_status = ($row['prioridad'] == 'Urgente') ? 'card-urgent' : (($row['prioridad'] == 'Pendiente') ? 'card-pending' : 'card-on-time');
                                $texto_mostrar = $row['prioridad'];
                            } else {
                                $bg_status = ($row['estado'] == 'Aceptada') ? 'card-accepted' : 'card-rejected';
                                $texto_mostrar = $row['estado'];
                            }
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary small"><?php echo $row['folio']; ?></td>
                                <td>
                                    <div class="fw-bold text-dark small"><?php echo $row['titulo_event']; ?></div>
                                    <div class="text-muted x-small">Solicitó: <?php echo $row['nombre_usuario']; ?></div>
                                </td>
                                <td><span class="badge rounded-pill bg-light text-dark border px-3"><?php echo $row['nombre_espacio']; ?></span></td>
                                <td class="small text-muted"><?php echo $row['fecha_evento']; ?></td>
                                <td><span class="badge-status <?php echo $bg_status; ?>"><?php echo strtoupper($texto_mostrar); ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-gestionar shadow-sm" onclick="gestionar(<?php echo $row['id_solicitud']; ?>)">Gestionar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bsModalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header text-white p-4" style="background-color: var(--sira-purple-dark); border-radius: 25px 25px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi- eye-fill me-2"></i> Revisión de Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5 border-end">
                        <h4 class="fw-bold mb-3" style="color: var(--sira-purple-dark);" id="detFolio"></h4>
                        
                        <div class="mb-3">
                            <label class="text-muted x-small fw-bold text-uppercase d-block">Fecha del Evento</label>
                            <span class="fw-bold fs-5 text-dark" id="detFechaEvento"></span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted x-small fw-bold text-uppercase d-block">Horario</label>
                            <span class="fw-bold text-dark" id="detHorario"></span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted x-small fw-bold text-uppercase d-block">Estado Actual</label>
                            <span id="detEstado" class="badge-status card-pending"></span>
                        </div>
                        
                        <hr class="my-4">
                        
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2">Tomar Decisión</label>
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-success flex-fill fw-bold rounded-pill" onclick="actualizarEstado('Aceptada')">Aprobar</button>
                            <button class="btn btn-danger flex-fill fw-bold rounded-pill" onclick="actualizarEstado('Rechazada')">Rechazar</button>
                        </div>
                        
                        <textarea id="motivoRechazo" class="form-control mb-3 border-0 bg-light" rows="2" placeholder="Escribe un comentario o motivo..."></textarea>
                        
                        <button class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-pill" id="btnBorrarModal">
                            <i class="bi bi-trash3-fill me-1"></i> Eliminar Registro
                        </button>
                    </div>
                    
                    <div class="col-md-7">
                        <div class="mb-4">
                            <label class="text-primary x-small fw-bold text-uppercase d-block">Solicitante</label>
                            <p class="h5 fw-bold text-dark" id="detUsuarioNombre"></p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-muted x-small fw-bold text-uppercase d-block">Espacio / Auditorio</label>
                            <p class="fw-bold text-dark" id="detAuditorio"></p>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted x-small fw-bold text-uppercase d-block">Título del Evento</label>
                            <p class="fw-bold text-dark" id="detTituloEv"></p>
                        </div>
                        
                        <div>
                            <label class="text-muted x-small fw-bold text-uppercase d-block">Descripción detallada</label>
                            <div class="p-3 rounded-4 mt-1 shadow-sm border" id="detDescription" style="background-color: #fafafa; min-height: 120px; font-size: 0.9rem; color: #555;">
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin_interactivo.js"></script>
</body>

</html>