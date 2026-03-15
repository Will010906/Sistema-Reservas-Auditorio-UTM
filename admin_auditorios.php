<?php
/**
 * MÓDULO DE GESTIÓN DE AUDITORIOS - UTM
 * Descripción: Panel de control de inventario de espacios físicos (Auditorios/Aulas).
 * Funcionalidades:
 * - Visualización de disponibilidad mediante badges de colores.
 * - Desglose de equipamiento fijo por cada espacio.
 * - Registro de nuevos espacios con carga de imágenes (enctype="multipart/form-data").
 * - Función de desactivación/activación rápida (Mantenimiento).
 */
session_start();
include("config/db_local.php");

// Seguridad estricta: Solo permite acceso a usuarios con perfil de 'administrador'
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

// Obtención de todos los auditorios registrados
$query = "SELECT * FROM auditorio ORDER BY nombre_espacio ASC";
$resultado = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Auditorios - UTM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <style>
        /* Efecto hover en las tarjetas de auditorio */
        .auditorio-card { transition: all 0.3s; border: none; border-radius: 15px; overflow: hidden; }
        .auditorio-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .img-container { height: 160px; position: relative; }
        .img-container img { width: 100%; height: 100%; object-fit: cover; }
        /* Badge flotante sobre la imagen para indicar disponibilidad */
        .status-badge { position: absolute; top: 10px; right: 10px; padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    </style>
</head>
<body class="bg-light">

<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0">Gestión de Auditorios</h1>
                <p class="text-muted small">Control de inventario y disponibilidad de espacios</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoAuditorio">
                <i class="bi bi-plus-lg me-2"></i> Nuevo Auditorio
            </button>
        </div>

        <div class="row g-4">
    <?php while ($aud = mysqli_fetch_assoc($resultado)): ?>
        <div class="col-md-4">
            <div class="card auditorio-card shadow-sm h-100">
                <div class="img-container">
                    <img src="assets/img/auditorios/<?php echo $aud['id_auditorio']; ?>.jpg"
                         onerror="this.src='assets/img/placeholder.jpg'">

                    <span class="status-badge <?php echo $aud['disponibilidad'] ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                        <?php echo $aud['disponibilidad'] ? 'Disponible' : 'Mantenimiento'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1 h6"><?php echo $aud['nombre_espacio']; ?></h5>
                    <p class="text-muted x-small mb-2"><i class="bi bi-geo-alt me-1"></i> <?php echo $aud['ubicacion']; ?></p>

                    <div class="mb-3">
                        <div class="d-flex flex-wrap gap-1">
                            <?php
                            $equipos = explode(',', $aud['equipamiento_fijo']);
                            foreach ($equipos as $e): if (trim($e) != ""):
                            ?>
                                <span class="badge bg-light text-dark border x-small fw-normal"><?php echo trim($e); ?></span>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded-3 mb-3">
                        <span class="x-small fw-bold text-muted"><i class="bi bi-people me-1"></i> <?php echo $aud['capacidad_maxima']; ?></span>
                        <span class="x-small fw-bold text-muted"><i class="bi bi-shield-check me-1"></i> <?php echo $aud['disponibilidad'] ? 'Activo' : 'Inactivo'; ?></span>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary flex-fill rounded-pill" onclick="editarAuditorio(<?php echo htmlspecialchars(json_encode($aud)); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm <?php echo $aud['disponibilidad'] ? 'btn-outline-danger' : 'btn-outline-success'; ?> flex-fill rounded-pill"
                            onclick="cambiarEstado(<?php echo $aud['id_auditorio']; ?>, <?php echo $aud['disponibilidad']; ?>)">
                            <i class="bi bi-power"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-dark rounded-pill" onclick="eliminarAuditorio(<?php echo $aud['id_auditorio']; ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div class="modal fade" id="modalNuevoAuditorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="tituloModal"><i class="bi bi-plus-circle me-2"></i>Registrar Espacio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="modules/registrar_auditorio.php" method="POST" enctype="multipart/form-data" id="formAuditorio">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_auditorio" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nombre del Auditorio</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Ubicación</label>
                        <input type="text" name="ubicacion" id="edit_ubicacion" class="form-control rounded-3" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Capacidad</label>
                            <input type="number" name="capacidad" id="edit_capacidad" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Imagen (JPG)</label>
                            <input type="file" name="foto" class="form-control rounded-3" accept="image/jpeg">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Equipamiento Fijo (separado por comas)</label>
                        <textarea name="equipamiento" id="edit_equipamiento" class="form-control rounded-3" rows="2" placeholder="Proyector, Aire Acondicionado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin_auditorios.js"></script>
</body>
</html>