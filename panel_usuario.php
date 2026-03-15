<?php
session_start();
include("config/db_local.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}
$id_user = $_SESSION['id_usuario'];

// Consultas para los contadores
$res_acep = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE id_usuario = '$id_user' AND estado = 'Aceptada'");
$aceptadas = mysqli_fetch_assoc($res_acep)['t'];

$res_pend = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE id_usuario = '$id_user' AND estado = 'Pendiente'");
$pendientes = mysqli_fetch_assoc($res_pend)['t'];

$res_rech = mysqli_query($conexion, "SELECT COUNT(*) as t FROM solicitudes WHERE id_usuario = '$id_user' AND estado = 'Rechazada'");
$rechazadas = mysqli_fetch_assoc($res_rech)['t'];

// Consulta para la tabla con JOIN
$sql = "SELECT s.*, a.nombre_espacio FROM solicitudes s 
        JOIN auditorio a ON s.id_auditorio = a.id_auditorio 
        WHERE s.id_usuario = '$id_user' ORDER BY s.id_solicitud DESC";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Usuario - UTM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        /* Tarjetas con diseño premium */
        .card-stats {
            border: none;
            border-radius: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card-stats:hover {
            transform: translateY(-7px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        /* Colores de Semáforo para Usuario */
        .bg-pendientes {
            background-color: #ffc107 !important;
            color: #444 !important;
        }

        /* Amarillo */
        .bg-aceptadas {
            background-color: #198754 !important;
            color: white !important;
        }

        /* Verde */
        .bg-rechazadas {
            background-color: #dc3545 !important;
            color: white !important;
        }

        /* Rojo */

        .table-container {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        }

        .btn-nueva {
            border-radius: 15px;
            background-color: #008a45;
            border: none;
            font-weight: 700;
            padding: 12px 25px;
        }

        .btn-nueva:hover {
            background-color: #006b35;
            transform: scale(1.05);
        }

        .badge-status {
            padding: 10px 18px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0">Panel Usuario</h1>
                <p class="text-muted">Bienvenido, <?php echo $_SESSION['nombre']; ?></p>
            </div>
            <button class="btn btn-primary px-4 py-2 fw-bold shadow-sm"
                style="border-radius: 12px;"
                data-bs-toggle="modal"
                data-bs-target="#modalNuevaSolicitud">
                + Nueva Solicitud
            </button>
        </div>

        <div class="row g-4 mb-5 text-white">
            <div class="col-md-4">
                <div class="card card-stats bg-pendientes shadow h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase small opacity-75 fw-bold">Mis Pendientes</h6>
                        <div class="display-5 fw-bold"><?php echo $pendientes; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats bg-aceptadas shadow h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase small opacity-75 fw-bold">Aceptadas</h6>
                        <div class="display-5 fw-bold"><?php echo $aceptadas; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats bg-rechazadas shadow h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase small opacity-75 fw-bold">Rechazadas</h6>
                        <div class="display-5 fw-bold"><?php echo $rechazadas; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container shadow-sm border-0">
            <h5 class="fw-bold mb-4">Mis Reservaciones</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-muted small">
                            <th>FOLIO</th>
                            <th>EVENTO</th>
                            <th>AUDITORIO</th>
                            <th>FECHA</th>
                            <th>ESTATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo $fila['folio']; ?></td>
                                <td><span class="fw-semibold"><?php echo $fila['titulo_event']; ?></span></td>
                                <td><span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?php echo $fila['nombre_espacio']; ?></span></td>
                                <td class="text-muted small"><?php echo $fila['fecha_event']; ?></td>
                                <td>
                                    <?php
                                    $status_class = "bg-pendientes"; // Por defecto
                                    if ($fila['estado'] == 'Aceptada') $status_class = 'bg-aceptadas';
                                    if ($fila['estado'] == 'Rechazada') $status_class = 'bg-rechazadas';
                                    ?>
                                    <span class="badge badge-status <?php echo $status_class; ?> text-white">
                                        <?php echo strtoupper($fila['estado']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-lg" style="border-radius: 25px; border: none;">
            
            <div id="paso_catalogo" class="p-4">
                <h4 class="fw-bold mb-4 text-center">Selecciona un Auditorio</h4>
                <div class="row g-4">
                    <?php 
                    $res = mysqli_query($conexion, "SELECT * FROM auditorio");
                    while($aud = mysqli_fetch_assoc($res)): 
                    ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <img src="assets/img/auditorios/<?php echo $aud['id_auditorio']; ?>.jpg" 
                                 class="card-img-top" style="height: 160px; object-fit: cover;"
                                 onerror="this.src='assets/img/placeholder_auditorio.jpg'">
                            <div class="card-body text-center">
                                <h6 class="fw-bold mb-1"><?php echo $aud['nombre_espacio']; ?></h6>
                                <p class="small text-muted mb-3">Capacidad: <?php echo $aud['capacidad_maxima']; ?> personas</p>
                                <button class="btn btn-success w-100 rounded-pill fw-bold" 
                                        onclick="irAlCalendario(<?php echo $aud['id_auditorio']; ?>, '<?php echo $aud['nombre_espacio']; ?>')">
                                    Seleccionar
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div id="paso_calendario" class="p-4" style="display: none;">
                <button class="btn btn-sm btn-outline-secondary mb-3 rounded-pill" onclick="regresarAlCatalogo()">← Volver al catálogo</button>
                <h4 class="fw-bold text-center mb-4">Selección de Horario y Día</h4>
                
                <div class="row g-4">
                    <div class="col-md-5 border-end">
                        <label class="form-label fw-bold">1. Elige la fecha:</label>
                        <input type="date" id="fecha_seleccionada" class="form-control rounded-3" 
                               min="<?php echo date('Y-m-d'); ?>" 
                               onchange="actualizarDisponibilidad()">
                    </div>
                    <div class="col-md-7 text-center">
                        <label class="form-label fw-bold d-block">2. Horarios disponibles:</label>
                        <div id="contenedor_horarios" class="d-flex flex-wrap justify-content-center gap-2 bg-light p-3 rounded-4 border">
                            <p class="text-muted small">Por favor, selecciona una fecha primero.</p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <button class="btn btn-primary px-5 py-2 fw-bold shadow rounded-pill" 
                            id="btnIrAPasoFinal" disabled onclick="irAlFormularioFinal()">
                        Confirmar Horario y Continuar
                    </button>
                </div>
            </div>

            <div id="paso_formulario" class="p-4" style="display: none;">
                <button class="btn btn-sm btn-outline-secondary mb-3 rounded-pill" onclick="regresarAlCalendario()">← Ajustar horario</button>
                
                <form action="modules/guardar_solicitud.php" method="POST">
                    <input type="hidden" name="id_auditorio" id="input_id_auditorio">
                    <input type="hidden" name="fecha_evento" id="input_fecha_evento">
                    <input type="hidden" name="hora_inicio" id="input_hora_inicio">
                    <input type="hidden" name="hora_fin" id="input_hora_fin">

                    <div class="bg-primary bg-opacity-10 p-3 mb-4 rounded-4 border border-primary border-opacity-25">
                        <h6 class="mb-0 fw-bold">Resumen de selección:</h6>
                        <span id="display_nombre_auditorio" class="badge bg-primary mt-2"></span>
                        <span id="display_resumen_fecha" class="badge bg-dark mt-2"></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Nombre del Evento</label>
                            <input type="text" name="titulo_event" class="form-control rounded-3" placeholder="Ej. Taller de Redes" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Razón de la Solicitud</label>
                            <textarea name="descripcion" class="form-control rounded-3" rows="4" placeholder="Describe brevemente el objetivo del evento..."></textarea>
                        </div>
                        <div class="col-md-12 text-end mt-4">
                            <button type="submit" class="btn btn-success btn-lg px-5 fw-bold rounded-pill shadow">
                                Enviar Solicitud
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/gestion_reservas.js"></script>

</html>