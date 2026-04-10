<?php
/**
 * SIRA - SUPERVISIÓN ACADÉMICA (SUBDIRECTORES)
 * * MÓDULO: NÚCLEO DE MÉTRICAS Y AUDITORÍA DE CARRERA
 * * @package     Frontend_Admin
 * @subpackage  Subdirector_Dashboard
 * @version     1.5.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 */
include("config/db_local.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Supervisión Académica</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <link rel="stylesheet" href="assets/css/panel_subdirector.css?v=1.2">
    <script>
        const tokenSira = localStorage.getItem('sira_session_token');
        if (!tokenSira) { window.location.href = 'login.php?error=expired'; }
        const payload = JSON.parse(atob(tokenSira.split('.')[1]));
    </script>

    
</head>
<body>

<nav class="navbar navbar-sira sticky-top shadow-sm">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand fw-800 d-flex align-items-center" href="#">
            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3"><i class="bi bi-shield-check text-sira fs-4"></i></div>
            SIRA <span class="text-muted fw-normal ms-2 small">| Supervisión</span>
        </a>
        <div class="d-flex align-items-center">
    <div class="text-end me-3 d-none d-md-block">
        <div class="fw-800 mb-0 small" id="nombreSubdirector" style="color: var(--sira-purple-dark);">--</div>
        <div class="badge-subdirector" style="text-align: center;">SUBDIRECTOR</div>
    </div>
    
    <div class="user-avatar" id="avatarLetra" style="width: 40px; height: 40px; background: var(--sira-purple); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; cursor: default;">
        S
    </div>

    <button onclick="confirmarSalida()" class="btn btn-link text-danger ms-3 p-0" title="Cerrar Sesión">
        <i class="bi bi-power fs-4"></i>
    </button>
</div>
    </div>
</nav>

<div class="container-fluid px-lg-5 py-4">
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h2 class="fw-800 mb-1">Métricas de Carrera</h2>
            <p class="text-muted small fw-bold">ÁREA: <span class="text-sira text-uppercase" id="areaCarrera">--</span></p>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-3">
            <button class="btn btn-primary rounded-pill px-4 fw-800 shadow-sm border-0 py-2" style="background: var(--sira-purple);" onclick="abrirNuevaReservacion()">
                <i class="bi bi-plus-circle-fill"></i> Nueva Reservación
            </button>
            <button id="btnPDFCarrera" class="btn btn-danger rounded-pill px-4 fw-800 shadow-sm border-0 py-2">
                <i class="bi bi-file-earmark-pdf-fill"></i> Reporte
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4"><div class="card card-kpi border-sira-primary"><div class="icon-box"><i class="bi bi-layers"></i></div><div><div class="kpi-label">Gestión Total</div><div class="kpi-value" id="kpiTotal">0</div></div></div></div>
        <div class="col-md-4"><div class="card card-kpi border-sira-warning"><div class="icon-box" style="color:#ffc107"><i class="bi bi-hourglass-split"></i></div><div><div class="kpi-label">En Revisión</div><div class="kpi-value" id="kpiPendientes">0</div></div></div></div>
        <div class="col-md-4"><div class="card card-kpi border-sira-success"><div class="icon-box" style="color:#2e7d32"><i class="bi bi-check-all"></i></div><div><div class="kpi-label">Autorizadas</div><div class="kpi-value" id="kpiAceptadas">0</div></div></div></div>
    </div>

    <div class="d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill border shadow-sm mb-4" style="width: fit-content;">
        <i class="bi bi-calendar-range text-muted"></i>
        <input type="date" id="min_fecha" class="form-control form-control-sm border-0 bg-transparent p-0" style="font-size: 0.75rem; width: 110px;">
        <span class="text-muted">/</span>
        <input type="date" id="max_fecha" class="form-control form-control-sm border-0 bg-transparent p-0" style="font-size: 0.75rem; width: 110px;">
        <button class="btn btn-sm text-primary p-0 ms-2" onclick="limpiarFiltrosFecha()"><i class="bi bi-eraser-fill"></i></button>
    </div>

    <div class="card card-table shadow-sm mb-5 animate__animated animate__fadeInUp">
        <div class="card-header bg-white p-4 border-0">
            <h5 class="fw-800 mb-0">Historial de Solicitudes</h5>
        </div>
        <div class="p-0 table-responsive">
            <table id="tablaSubdirector" class="table table-hover align-middle mb-0 w-100">
                <thead>
                    <tr>
                        <th class="ps-4">Folio</th>
                        <th>Solicitante</th>
                        <th>Evento</th>
                        <th>Fecha</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody id="contenedorSubdirector"></tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/modal_reservacion.php'; ?>
<?php include 'includes/modal_detalle.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="assets/js/subdirector_gestion.js"></script>
<script src="assets/js/usuario_reservas.js"></script>

<script>
   function confirmarSalida() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Tu acceso de supervisión terminará ahora.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#5B3D66',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Limpiamos el token de seguridad
            localStorage.removeItem('sira_session_token');
            // Redirigimos al login
            window.location.href = 'login.php?status=logout';
        }
    });
}
</script>

</body>
</html>