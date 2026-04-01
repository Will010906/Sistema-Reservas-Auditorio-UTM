<?php
/**
 * MÓDULO DE GESTIÓN DE AUDITORIOS - UTM
 * Actualizado: Seguridad JWT y Arquitectura de API.
 */
include("config/db_local.php");

// Nota: La seguridad de sesión se maneja ahora vía JavaScript con el Token JWT 
// para cumplir con el estándar de desacoplamiento del frontend.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <title>Gestión de Auditorios - UTM</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    
    <script>
        // BLOQUEO DE SEGURIDAD JWT (Requisito 30% Seguridad)
        // Si no hay token, el usuario no puede ni ver la estructura de la página
       // Cambia 'token' por 'sira_session_token'
const token = localStorage.getItem('sira_session_token'); 
if (!token) {
    window.location.href = 'login.php?error=expired';
}
    </script>

<style>
    :root {
        /* Colores sincronizados con tu sidebar.php */
        --sira-purple-dark: #2D1B33;
        --sira-purple-primary: #5B3D66;
        --sira-purple-light: #F4EFFF;
        --utm-gris-bg: #f8f9fa;
    }
.badge-sira-status {
    background-color: #5B3D66 !important; /* El morado UTM */
    color: white !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.65rem;
    padding: 5px 10px;
    border-radius: 8px;
}

.capacidad-info {
    font-size: 0.85rem;
    color: #5B3D66; /* Morado UTM */
    font-weight: 700;
}   

    .capacidad-info i {
        margin-right: 5px;
    }

    /* Ajuste para que el nombre y estatus queden en la misma línea */
    .card-title-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    /* Tarjetas de Auditorio */
    .auditorio-card {
        border-radius: 20px;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .auditorio-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(45, 27, 51, 0.1) !important;
    }

    /* Botón Principal (Nuevo Auditorio) y Guardar */
    .btn-primary, .btn-guinda {
        background-color: var(--sira-purple-primary) !important;
        border-color: var(--sira-purple-primary) !important;
        color: white !important;
        font-weight: 600 !important;
        transition: all 0.3s ease;
    }

    .btn-primary:hover, .btn-guinda:hover {
        background-color: var(--sira-purple-dark) !important;
        transform: translateY(-2px);
    }

    /* Botón Mantenimiento (Outline) */
    .btn-outline-guinda {
        color: var(--sira-purple-primary) !important;
        border: 2px solid var(--sira-purple-primary) !important;
        background-color: transparent !important;
        font-weight: 700 !important;
        font-size: 0.75rem;
    }

    .btn-outline-guinda:hover {
        background-color: var(--sira-purple-primary) !important;
        color: white !important;
    }

    /* BLOQUE DE EQUIPAMIENTO MINIMALISTA */
    .badge-minimal {
        background-color: var(--utm-gris-bg);
        color: var(--sira-purple-primary);
        border: 1px solid #e9ecef;
        padding: 2px 8px; /* Tamaño reducido */
        border-radius: 4px;
        font-size: 0.65rem; /* Fuente más pequeña */
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }

    /* Ajustes de texto sutiles */
    .text-muted {
        font-size: 0.8rem !important;
    }
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
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" onclick="prepararNuevoAuditorio()">
                <i class="bi bi-plus-lg me-2"></i> Nuevo Auditorio
            </button>
        </div>

       <div class="container-fluid px-4">
    <div class="row" id="contenedorAuditorios">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Cargando inventario de espacios...</p>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="modalNuevoAuditorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="tituloModal"><i class="bi bi-plus-circle me-2"></i>Registrar Espacio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAuditorio" enctype="multipart/form-data">
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
                            <input type="file" name="foto" id="input_foto" class="form-control rounded-3" accept="image/jpeg">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Equipamiento Fijo (separado por comas)</label>
                        <textarea name="equipamiento" id="edit_equipamiento" class="form-control rounded-3" rows="2" placeholder="Proyector, Aire Acondicionado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnGuardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/admin_auditorios.js"></script>
<script src="assets/js/auth_check.js"></script>

</body>
</html>