<?php
/**
 * PANEL DE ADMINISTRACIÓN - UTM
 * Descripción: Dashboard principal para el administrador. 
 * Funcionalidades: 
 * - Conteo de solicitudes por prioridad y estado.
 * - Tabla interactiva con filtros de estatus y fechas.
 * - Modal de gestión para aprobar/rechazar solicitudes.
 */
session_start();

// Validación de sesión activa
if (!isset($_SESSION['nombre'])) {
    header("Location: index.php");
    exit();
}
include 'config/db_local.php';

// --- SECCIÓN: ESTADÍSTICAS DE PENDIENTES ---
// Conteo de solicitudes agrupadas por el "semáforo" de prioridad
$res_urg = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Urgente' AND estado = 'Pendiente'");
$urgentes = mysqli_fetch_assoc($res_urg)['t'];

$res_dem = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Pendiente' AND estado = 'Pendiente'");
$demorados = mysqli_fetch_assoc($res_dem)['t'];

$res_tiem = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Con tiempo' AND estado = 'Pendiente'");
$a_tiempo = mysqli_fetch_assoc($res_tiem)['t'];

// --- SECCIÓN: HISTORIAL DE DECISIONES ---
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
    <title>Panel Administrador - UTM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .card-custom { border: none; border-radius: 15px; transition: transform 0.2s; }
        .card-custom:hover { transform: translateY(-5px); }
        .badge-status { padding: 0.5em 1em; border-radius: 50px; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; display: inline-block; min-width: 100px; text-align: center; }
        .x-small { font-size: 0.7rem; }
        /* Clases para el coloreado dinámico de badges */
        .card-urgent { background-color: #dc3545 !important; color: white; }
        .card-pending { background-color: #ffc107 !important; color: #212529; }
        .card-on-time { background-color: #198754 !important; color: white; }
        .card-accepted { background-color: #0dcaf0 !important; color: white; }
        .card-rejected { background-color: #6c757d !important; color: white; }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-0">Panel Administrador</h1>
                    <p class="text-muted small">Bienvenido, <?php echo $_SESSION['nombre']; ?> (Gestión de Auditorios)</p>
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
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="URGENTE" id="chkUrgente" checked>
                            <label class="form-check-label small fw-bold text-danger" for="chkUrgente">Urgentes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="PENDIENTE" id="chkPendiente" checked>
                            <label class="form-check-label small fw-bold text-warning" for="chkPendiente">Pendientes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="CON TIEMPO" id="chkTiempo" checked>
                            <label class="form-check-label small fw-bold text-success" for="chkTiempo">A Tiempo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="ACEPTADA" id="chkAceptada">
                            <label class="form-check-label small fw-bold text-info" for="chkAceptada">Aceptadas</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-check" type="checkbox" value="RECHAZADA" id="chkRechazada">
                            <label class="form-check-label small fw-bold text-secondary" for="chkRechazada">Rechazadas</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Rango de Fechas</label>
                    <div class="input-group input-group-sm">
                        <input type="date" id="fecha_inicio" class="form-control border-end-0">
                        <span class="input-group-text bg-white border-start-0 border-end-0"><i class="bi bi-arrow-right"></i></span>
                        <input type="date" id="fecha_fin" class="form-control border-start-0">
                    </div>
                </div>

                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-dark btn-sm w-100 rounded-pill" onclick="resetFiltros()">
                        <i class="bi bi-trash-fill"></i> Limpiar
                    </button>
                    <button class="btn btn-danger btn-sm w-100 rounded-pill" onclick="descargarReporte()">
                        <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
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
                                // Consulta con JOIN para obtener nombres de usuario y auditorio
                                $query = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
                                          FROM solicitudes s
                                          JOIN usuarios u ON s.id_usuario = u.id_usuario
                                          JOIN auditorio a ON s.id_auditorio = a.id_auditorio
                                          ORDER BY s.id_solicitud DESC";
                                $resultado = mysqli_query($conexion, $query);
                                while ($row = mysqli_fetch_assoc($resultado)): 
                                    // Lógica de color de badge según prioridad si el estado es Pendiente
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
                                    <td>
                                        <span class="badge-status <?php echo $bg_status; ?>">
                                            <?php echo strtoupper($texto_mostrar); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm" onclick="gestionar(<?php echo $row['id_solicitud']; ?>)">
                                            Gestionar
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> 
    </div>

    <div class="modal fade" id="bsModalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">Revisión de Solicitud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h4 class="fw-bold mb-3" id="detFolio"></h4>
                            <p class="mb-1 text-muted small">Fecha del Evento</p>
                            <p class="fw-bold text-primary" id="detFechaEvento"></p>
                            <p class="mb-1 text-muted small">Estado Actual</p>
                            <div id="detEstado"></div>
                            <hr>
                            <div class="d-flex gap-2 mb-3">
                                <button class="btn btn-danger flex-fill fw-bold" onclick="actualizarEstado('Rechazada')">Rechazar</button>
                                <button class="btn btn-success flex-fill fw-bold" onclick="actualizarEstado('Aceptada')">Aprobar</button>
                            </div>
                            <textarea id="motivoRechazo" class="form-control" rows="3" placeholder="Comentarios adicionales..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold text-uppercase small">Solicitante</h6>
                            <p class="h5 mb-3 fw-bold" id="detUsuarioNombre"></p>
                            <p class="mb-1 text-muted small">Título del Evento</p>
                            <p class="fw-bold" id="detTituloEv"></p>
                            <p class="mb-1 text-muted small">Descripción</p>
                            <div class="bg-light p-3 rounded-3" id="detDescripcion" style="min-height: 120px;"></div>
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