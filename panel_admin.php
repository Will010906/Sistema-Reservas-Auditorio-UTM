<?php
session_start();
// Validación de seguridad
if (!isset($_SESSION['nombre'])) {
    header("Location: index.php");
    exit();
}
include 'config/db_local.php';

// --- SECCIÓN: PENDIENTES POR GESTIONAR ---
$res_urg = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Urgente' AND estado = 'Pendiente'");
$urgentes = mysqli_fetch_assoc($res_urg)['t'];

$res_dem = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Pendiente' AND estado = 'Pendiente'");
$demorados = mysqli_fetch_assoc($res_dem)['t'];

// Cambio de lógica: Esta tarjeta es ahora "A Tiempo" y debe ser VERDE.
$res_tiem = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE prioridad = 'Con tiempo' AND estado = 'Pendiente'");
$a_tiempo = mysqli_fetch_assoc($res_tiem)['t'];

// --- SECCIÓN: HISTORIAL DE DECISIONES ---
// Cambio de lógica: Esta tarjeta es "Aceptadas" (Historial) y debe ser AZUL.
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
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .card-custom { border: none; border-radius: 15px; transition: transform 0.2s; }
        .card-custom:hover { transform: translateY(-5px); }
        .badge-status { padding: 0.5em 1em; border-radius: 50px; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; }
        .x-small { font-size: 0.7rem; }
        
        /* Definición de colores para el semáforo y el historial */
        .card-urgent { background-color: #dc3545; color: white; } /* Rojo */
        .card-pending { background-color: #ffc107; color: #212529; } /* Amarillo */
        .card-on-time { background-color: #198754; color: white; } /* Verde - Semáforo */
        .card-accepted { background-color: #0dcaf0; color: white; } /* Azul Info - Historial */
        .card-rejected { background-color: #6c757d; color: white; } /* Gris - Historial */
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Panel Administrador</h1>
            <p class="text-muted">Bienvenido, <?php echo $_SESSION['nombre']; ?></p>
        </div>
        <button class="btn btn-outline-danger btn-sm" onclick="location.href='modules/logout.php'">Cerrar Sesión</button>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-9">
            <div class="row g-3 h-100">
                <div class="col-md-4">
                    <div class="card card-custom card-urgent shadow h-100">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="text-uppercase small opacity-75">Urgentes</h6>
                            <div class="display-5 fw-bold"><?php echo $urgentes; ?></div>
                            <p class="mb-0 x-small mt-2 text-white-50">Atención inmediata</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom card-pending shadow h-100">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="text-uppercase small opacity-75">Pendientes</h6>
                            <div class="display-5 fw-bold"><?php echo $demorados; ?></div>
                            <p class="mb-0 x-small mt-2 text-muted">Prioridad normal</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom card-on-time shadow h-100">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="text-uppercase small opacity-75">A Tiempo</h6>
                            <div class="display-5 fw-bold"><?php echo $a_tiempo; ?></div>
                            <p class="mb-0 x-small mt-2 text-white-50">Eventos lejanos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="d-flex flex-column gap-3 h-100">
                <div class="card card-custom card-accepted shadow-sm flex-fill d-flex align-items-center justify-content-center">
                    <div class="card-body py-3 text-center w-100">
                        <h6 class="text-uppercase x-small mb-1 opacity-75">Aceptadas</h6>
                        <div class="h3 fw-bold mb-0"><?php echo $total_aceptadas; ?></div>
                    </div>
                </div>
                <div class="card card-custom card-rejected shadow-sm flex-fill d-flex align-items-center justify-content-center">
                    <div class="card-body py-3 text-center w-100">
                        <h6 class="text-uppercase x-small mb-1 opacity-75">Rechazadas</h6>
                        <div class="h3 fw-bold mb-0"><?php echo $total_rechazadas; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Desde:</label>
                    <div class="input-group input-group-sm">
                        <input type="date" id="fecha_inicio" class="form-control border-end-0" style="border-radius: 10px 0 0 10px;">
                        <span class="input-group-text bg-white border-start-0" style="border-radius: 0 10px 10px 0;">📅</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Hasta:</label>
                    <div class="input-group input-group-sm">
                        <input type="date" id="fecha_fin" class="form-control border-end-0" style="border-radius: 10px 0 0 10px;">
                        <span class="input-group-text bg-white border-start-0" style="border-radius: 0 10px 10px 0;">📅</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <button id="btnFiltrar" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm" style="border-radius: 10px; height: 38px;">
                        Filtrar Solicitudes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Tabla de Gestión</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaSolicitudes">
                    <thead class="table-light">
                        <tr>
                            <th>Folio</th>
                            <th>Solicitante</th>
                            <th>Auditorio</th>
                            <th>Fecha Evento</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM solicitudes";
                        $resultado = mysqli_query($conexion, $query);
                        while ($row = mysqli_fetch_assoc($resultado)) {
                            // Definimos el color del badge en la tabla
                            $bg_status = 'bg-warning text-dark';
                            if ($row['estado'] == 'Aceptada') { $bg_status = 'bg-success'; } 
                            elseif ($row['estado'] == 'Rechazada') { $bg_status = 'bg-danger'; }

                            // Lógica de texto: mostrar prioridad si está pendiente
                            $texto_mostrar = ($row['estado'] == 'Pendiente') ? $row['prioridad'] : $row['estado'];

                            echo "<tr>
                                <td class='fw-bold'>{$row['folio']}</td>
                                <td>{$row['titulo_event']}</td>
                                <td>ID: {$row['id_auditorio']}</td>
                                <td>{$row['fecha_evento']}</td>
                                <td><span class='badge-status {$bg_status}'>" . strtoupper($texto_mostrar) . "</span></td>
                                <td><button class='btn btn-sm btn-outline-primary' onclick='gestionar({$row['id_solicitud']})'>Gestionar</button></td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bsModalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Revisión de Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6 border-end">
                        <h4 class="fw-bold mb-3" id="detFolio"></h4>
                        <p class="mb-1 text-muted small">Fecha de Registro</p>
                        <p class="fw-bold" id="detFechaSol"></p>
                        <p class="mb-1 text-muted small text-primary">Fecha del Evento</p>
                        <p class="fw-bold" id="detFechaEvento" style="color: #0d6efd;"></p>
                        <p class="mb-1 text-muted small">Estado Actual</p>
                        <p id="detEstado"></p>
                        <hr>
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-danger flex-fill" onclick="actualizarEstado('Rechazada')">Rechazar</button>
                            <button class="btn btn-success flex-fill" onclick="actualizarEstado('Aceptada')">Aprobar</button>
                        </div>
                        <label class="form-label small text-muted">Comentarios / Motivo de rechazo:</label>
                        <textarea id="motivoRechazo" class="form-control" rows="3" placeholder="Escribe aquí..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary fw-bold text-uppercase small">Usuario Solicitante</h6>
                        <p class="h5 mb-3" id="detUsuarioNombre"></p>
                        <p class="mb-1 text-muted small">Evento</p>
                        <p class="fw-bold" id="detTituloEv"></p>
                        <p class="mb-1 text-muted small">Descripción</p>
                        <div class="bg-light p-3 rounded" id="detDescripcion" style="min-height: 100px;"></div>
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