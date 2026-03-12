<?php 
session_start();
// Validación de seguridad
if (!isset($_SESSION['nombre'])) {
    header("Location: index.php");
    exit();
}
include 'config/db_local.php';

// Consultas para las tarjetas dinámicas
$res_urgentes = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Urgente'");
$urgentes = mysqli_fetch_assoc($res_urgentes)['total'];

$res_demorados = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Pendiente'");
$demorados = mysqli_fetch_assoc($res_demorados)['total'];

$res_tiempo = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Aceptada'");
$con_tiempo = mysqli_fetch_assoc($res_tiempo)['total'];
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
        <div class="col-md-4">
            <div class="card card-custom bg-danger text-white shadow">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75">Urgentes por autorizar</h6>
                    <div class="display-5 fw-bold"><?php echo $urgentes; ?></div>
                    <p class="mb-0 small">< 3 días</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom bg-warning text-dark shadow">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75">Demorados por autorizar</h6>
                    <div class="display-5 fw-bold"><?php echo $demorados; ?></div>
                    <p class="mb-0 small">5 días</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom bg-success text-white shadow">
                <div class="card-body">
                    <h6 class="text-uppercase small opacity-75">Con tiempo</h6>
                    <div class="display-5 fw-bold"><?php echo $con_tiempo; ?></div>
                    <p class="mb-0 small">+ días</p>
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
                            $bg_status = ($row['estado'] == 'Urgente') ? 'bg-danger' : (($row['estado'] == 'Pendiente') ? 'bg-warning text-dark' : 'bg-success');
                            echo "<tr>
                                <td class='fw-bold'>{$row['folio']}</td>
                                <td>{$row['titulo_event']}</td>
                                <td>ID: {$row['id_auditorio']}</td>
                                <td>{$row['fecha_evento']}</td>
                                <td><span class='badge-status {$bg_status}'>{$row['estado']}</span></td>
                                <td>
                                    <button class='btn btn-sm btn-outline-primary' onclick='gestionar({$row['id_solicitud']})'>Gestionar</button>
                                </td>
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
                <p class="mb-1 text-muted small">Fecha Registro</p>
                <p class="fw-bold" id="detFechaSol"></p>
                <p class="mb-1 text-muted small">Estado Actual</p>
                <p id="detEstado"></p>
                <hr>
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-danger flex-fill" onclick="actualizarEstado('Rechazada')">Rechazar</button>
                    <button class="btn btn-success flex-fill" onclick="actualizarEstado('Aceptada')">Aprobar</button>
                </div>
                <textarea id="motivoRechazo" class="form-control" rows="2" placeholder="Motivo de rechazo..."></textarea>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary fw-bold text-uppercase small">Usuario Solicitante</h6>
                <p class="h5 mb-3" id="detUsuarioNombre"></p>
                <p class="mb-1 text-muted small">Evento</p>
                <p class="fw-bold" id="detTituloEv"></p>
                <p class="mb-1 text-muted small">Descripción</p>
                <div class="bg-light p-3 rounded" id="detDescripcion"></div>
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