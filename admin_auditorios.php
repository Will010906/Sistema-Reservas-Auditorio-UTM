<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: INTERFAZ DE GESTIÓN DE INFRAESTRUCTURA (ADMIN)
 * * @package     Frontend_Admin
 * @subpackage  Auditorios_View
 * @version     2.5.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Vista administrativa para el control de inventario de espacios físicos. 
 * Implementa un esquema de renderizado desacoplado mediante Fetch API y 
 * un middleware de seguridad en el cliente basado en JWT.
 */
include("config/db_local.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Gestión de Auditorios</title>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="assets/css/admin_auditorios.css">
    
    <script>
        /**
         * GUARDIÁN DE SEGURIDAD PERIMETRAL (JWT GATEKEEPER)
         * Bloquea el renderizado de la estructura si la sesión no es válida.
         * Cumple con el requerimiento de seguridad institucional.
         */
        const token = localStorage.getItem('sira_session_token'); 
        if (!token) {
            window.location.href = 'login.php?error=expired';
        }
    </script>

</head>
<body class="bg-light">

<div class="wrapper d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0">Gestión de Auditorios</h1>
                <p class="text-muted small">Control de inventario, aforo y equipamiento de espacios</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" onclick="prepararNuevoAuditorio()">
                <i class="bi bi-plus-lg me-2"></i> Nuevo Auditorio
            </button>
        </div>

        <div class="container-fluid px-0">
            <div class="row g-4" id="contenedorAuditorios">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Sincronizando inventario con el núcleo...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="modalNuevoAuditorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="tituloModal">
                    <i class="bi bi-building-add me-2"></i>Registrar Espacio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formAuditorio" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_auditorio" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Nombre del Auditorio</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control rounded-3" placeholder="Ej: Auditorio A" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Ubicación Institucional</label>
                        <input type="text" name="ubicacion" id="edit_ubicacion" class="form-control rounded-3" placeholder="Ej: Edificio K - Planta Alta" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Capacidad (Aforo)</label>
                            <input type="number" name="capacidad" id="edit_capacidad" class="form-control rounded-3" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Imagen (JPG)</label>
                            <input type="file" name="foto" id="input_foto" class="form-control rounded-3" accept="image/jpeg">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Equipamiento Fijo (Tags)</label>
                        <textarea name="equipamiento" id="edit_equipamiento" class="form-control rounded-3" rows="2" placeholder="Proyector, Aire Acondicionado, Audio..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnGuardar">Guardar Cambios</button>
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