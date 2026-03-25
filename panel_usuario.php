<?php

/**
 * PANEL DE USUARIO - SIRA UTM
 * Actualizado: Seguridad JWT, Contadores Asíncronos y Tabla Dinámica.
 */
include("config/db_local.php");
// Nota: La seguridad y la identidad del usuario se manejan vía Token JWT en el cliente.
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Mis Reservaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <script src="assets/js/auth_check.js"></script>

    <style>
        :root {
            --sira-purple-dark: #2D1B33;
            --sira-purple-primary: #5B3D66;
            --sira-bg: #EBEFF2;
            --grad-pending: linear-gradient(135deg, #FFD93D 0%, #F9A825 100%);
            --grad-accepted: linear-gradient(135deg, #42A5F5 0%, #1E88E5 100%);
            --grad-rejected: linear-gradient(135deg, #FF6B6B 0%, #EE5253 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--sira-bg);
            margin: 0;
            color: #2D2D2D;
        }

        .main-content {
            margin-left: 310px;
            padding: 30px 40px;
            width: calc(100% - 310px);
        }

        .table-container {
            background: white;
            border-radius: 28px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        }

        .btn-nueva-solicitud {
            background-color: var(--sira-purple-primary);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 12px 28px;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 20px rgba(91, 61, 102, 0.2);
            display: flex;
            align-items: center;
        }

        .btn-nueva-solicitud:hover {
            background-color: var(--sira-purple-dark);
            transform: translateY(-4px);
            color: white;
        }

        .card-user {
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

        .card-user .count {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            z-index: 2;
            color: white;
        }

        .card-user h6 {
            font-size: 0.6rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            z-index: 2;
            color: white;
        }

        .bg-pend {
            background: var(--grad-pending);
        }

        .bg-pend h6,
        .bg-pend .count {
            color: #2D1B33;
        }

        .bg-acep {
            background: var(--grad-accepted);
        }

        .bg-rech {
            background: var(--grad-rejected);
        }

        .watermark {
            position: absolute;
            bottom: -5px;
            right: -5px;
            font-size: 3.2rem;
            opacity: 0.12;
            transform: rotate(-10deg);
            color: white;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            min-width: 105px;
            text-align: center;
            display: inline-block;
        }

        .st-pendiente {
            background: var(--grad-pending);
            color: #2D1B33 !important;
        }

        .st-aceptada {
            background: var(--grad-accepted);
            color: white !important;
        }

        .st-rechazada {
            background: var(--grad-rejected);
            color: white !important;
        }

        /* Estilos Calendario */
        .fc .fc-toolbar-title {
            text-transform: capitalize !important;
            font-weight: 800 !important;
            color: var(--sira-purple-dark);
            font-size: 1.5rem !important;
        }


        /* --- ESTILOS DE DISPONIBILIDAD (PASO 2) --- */

        .grid-horarios {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-start;
        }

        .btn-horario {
            border: 2px solid #E5E7EB;
            border-radius: 14px;
            /* Redondeado suave como en tu referencia */
            padding: 10px 14px;
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: calc(33.33% - 7px);
            /* Tres columnas exactas */
            background: white;
            color: #059669;
            /* Verde esmeralda para disponibles */
        }

        .btn-horario:hover:not(.disabled):not(.ocupado) {
            border-color: #059669;
            background-color: #F0FDF4;
            transform: scale(1.02);
        }

        /* Estado: OCUPADO (Rojo/Rosa) */
        .btn-horario.ocupado {
            background-color: #FCA5A5 !important;
            border-color: #FCA5A5 !important;
            color: white !important;
            cursor: not-allowed;
            opacity: 0.8;
        }

        /* Estado: SELECCIONADO (Verde Sólido) */
        .btn-horario.activo {
            background-color: #059669 !important;
            border-color: #059669 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }

        /* ALERTA AMARILLA (Límite 2 horas) */
        .alerta-limite {
            background-color: #FEF3C7;
            border: 1px solid #FDE68A;
            color: #92400E;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
      <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1 fw-800" style="color: var(--sira-purple-dark);">Mis Reservaciones</h1>
        <p class="text-muted small mb-0">Gestiona tus solicitudes de auditorio en la UTM.</p>
    </div>

    <div class="user-header-profile shadow-sm bg-white p-2 px-3 rounded-pill d-flex align-items-center gap-3">
        <div class="text-end d-none d-md-block">
            <div class="fw-bold small" id="nombreUsuarioHeader" style="font-size: 0.85rem; color: #2D1B33;">Cargando...</div>
            <small class="text-muted fw-bold text-uppercase" id="rolUsuarioHeader" style="font-size: 0.6rem;">Cargando...</small>
        </div>
        <div id="inicialAvatarUsuario" class="d-flex align-items-center justify-content-center text-white fw-bold" 
             style="width: 40px; height: 40px; background: var(--sira-purple-primary); border-radius: 50%;">U</div>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-nueva-solicitud shadow-sm" onclick="abrirModalNuevaReservacion()">
        <i class="bi bi-plus-lg me-2"></i> Nueva Solicitud
    </button>
</div>

<div class="row g-3 mb-4">
    </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card-user bg-pend">
                    <h6>En Revisión</h6>
                    <div class="count" id="countPendientes">0</div><i class="bi bi-clock-history watermark"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-user bg-acep">
                    <h6>Aprobadas</h6>
                    <div class="count" id="countAprobadas">0</div><i class="bi bi-check-circle-fill watermark"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-user bg-rech">
                    <h6>Rechazadas</h6>
                    <div class="count" id="countRechazadas">0</div><i class="bi bi-x-circle-fill watermark"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 mb-4 rounded-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center">
               <div id="filtros_estatus">
    <span class="small fw-bold text-muted me-3 text-uppercase">Filtrar Estatus:</span>
    <div class="form-check form-check-inline">
        <input class="form-check-input check-filtro" type="checkbox" value="Pendiente" id="f_pen">
        <label class="form-check-label text-warning fw-bold small" for="f_pen">Pendientes</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input check-filtro" type="checkbox" value="Aceptada" id="f_apr">
        <label class="form-check-label text-info fw-bold small" for="f_apr">Aprobadas</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input check-filtro" type="checkbox" value="Rechazada" id="f_rec">
        <label class="form-check-label text-danger fw-bold small" for="f_rec">Rechazadas</label>
    </div>
</div>
                <button class="btn btn-dark btn-sm rounded-pill px-4 fw-bold" onclick="window.limpiarFiltros()" class="btn">Limpiar Filtros</button>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaMisReservas" style="font-size: 0.85rem;">
                    <thead>
                        <tr class="text-muted x-small fw-bold text-uppercase border-bottom">
                            <th class="ps-4">Folio</th>
                            <th>Título del Evento</th>
                            <th>Auditorio</th>
                            <th>Fecha</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="contenedorMisReservas">
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No tienes solicitudes registradas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/modal_reservacion.php'; ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="assets/js/usuario_reservas.js"></script>
</body>

</html>