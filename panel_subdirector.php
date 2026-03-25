<?php
/**
 * PANEL DE SUPERVISIÓN (SUBDIRECTOR) - SIRA UTM
 * Actualizado: Seguridad JWT, Filtrado por Carrera y Reportes Dinámicos.
 */
include("config/db_local.php");
// La seguridad ahora se valida en el cliente mediante el Token JWT.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SIRA - Supervisión de Carrera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <script>
        // BLOQUEO DE SEGURIDAD JWT (30% Seguridad)
       const token = localStorage.getItem('sira_session_token'); // <-- Cambia 'token' por 'sira_session_token'
if (!token) { window.location.href = 'login.php?error=expired'; }
        
        // Extraemos datos del payload para personalizar la vista
        const payload = JSON.parse(atob(token.split('.')[1]));
        if (payload.perfil !== 'subdirector' && payload.perfil !== 'administrador') {
            window.location.href = 'login.php?error=perfil_no_autorizado';
        }
    </script>

    <style>
        :root { --sira-purple: #5B3D66; --sira-purple-light: #f4f0f7; --sira-dark: #2d1b33; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; color: var(--sira-dark); }
        .fw-800 { font-weight: 800; }
        .navbar-sira { background-color: white; border-bottom: 2px solid var(--sira-purple-light); padding: 0.8rem 2rem; }
        .user-avatar { 
            width: 42px; height: 42px; background: linear-gradient(135deg, var(--sira-purple) 0%, #3d2945 100%);
            color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; 
            font-weight: 700; box-shadow: 0 4px 10px rgba(91, 61, 102, 0.2);
        }
        .card-kpi { border: none; border-radius: 24px; transition: all 0.3s ease; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .card-kpi:hover { transform: translateY(-5px); }
        .border-sira { border-top: 5px solid var(--sira-purple) !important; }
        .icon-box { padding: 1rem; border-radius: 18px; display: inline-flex; }
        .bg-sira-light { background-color: var(--sira-purple-light); }
        .text-sira { color: var(--sira-purple); }
        .card-table { border-radius: 24px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.02); }
        .badge-status { padding: 0.5rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.7rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-sira sticky-top mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-800 text-dark d-flex align-items-center" href="#">
            <i class="bi bi-intersect me-2 text-sira"></i> SIRA <span class="text-muted fw-normal ms-2 small">| Supervisión</span>
        </a>
        <div class="d-flex align-items-center">
            <div class="text-end me-3 d-none d-md-block">
                <div class="fw-bold mb-0 small" id="nombreSubdirector">--</div>
                <div class="badge bg-sira-light text-sira text-uppercase fw-700" id="perfilTag" style="font-size: 0.6rem;">SUBDIRECTOR</div>
            </div>
            <div class="user-avatar" id="avatarLetra">U</div>
            <button onclick="confirmarSalida()" class="btn btn-link text-danger ms-3 p-0"><i class="bi bi-power fs-4"></i></button>
        </div>
    </div>
</nav>

<div class="container-fluid px-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-5">
            <h3 class="fw-800 mb-1">Métricas de Supervisión</h3>
            <p class="text-muted small">Carrera: <span class="fw-bold text-sira" id="areaCarrera">--</span></p>
        </div>
        <div class="col-md-7 d-flex justify-content-end align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 bg-white p-2 rounded-4 border shadow-sm">
                <input type="date" id="min_fecha" class="form-control form-control-sm border-0 bg-transparent" style="font-size: 0.8rem;">
                <i class="bi bi-arrow-right text-muted small"></i>
                <input type="date" id="max_fecha" class="form-control form-control-sm border-0 bg-transparent" style="font-size: 0.8rem;">
            </div>
            <button id="btnPDFCarrera" class="btn btn-danger rounded-pill px-4 shadow-sm border-0 py-2 fw-bold" style="background-color: #e63946;">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i>Generar Reporte Mensual
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-kpi p-4 border-sira">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-sira-light me-3"><i class="bi bi-layers-half text-sira fs-3"></i></div>
                    <div>
                        <div class="text-muted small fw-bold">TOTAL SOLICITUDES</div>
                        <h2 class="fw-800 mb-0" id="kpiTotal">0</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-kpi p-4 border-start border-5 border-warning">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-warning bg-opacity-10 me-3"><i class="bi bi-hourglass-split text-warning fs-3"></i></div>
                    <div>
                        <div class="text-muted small fw-bold">EN REVISIÓN ADMIN</div>
                        <h2 class="fw-800 mb-0" id="kpiPendientes">0</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-kpi p-4 border-start border-5 border-success">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success bg-opacity-10 me-3"><i class="bi bi-patch-check-fill text-success fs-3"></i></div>
                    <div>
                        <div class="text-muted small fw-bold">APROBADAS POR ÁREA</div>
                        <h2 class="fw-800 mb-0" id="kpiAceptadas">0</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-table shadow-sm mb-5">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between">
            <h5 class="fw-bold mb-0 text-sira"><i class="bi bi-calendar-event me-2"></i>Bitácora de Eventos</h5>
            <button class="btn btn-link text-muted p-0 text-decoration-none small" onclick="limpiarFiltrosFecha()"><i class="bi bi-eraser-fill me-1"></i>Limpiar fechas</button>
        </div>
        <div class="p-4 pt-0">
            <table id="tablaSubdirector" class="table align-middle w-100">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Solicitante</th>
                        <th>Evento</th>
                        <th>Fecha</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="contenedorSubdirector">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    // Cargar datos del usuario desde el Token
    const payload = JSON.parse(atob(localStorage.getItem('sira_session_token').split('.')[1]));
    $('#nombreSubdirector').text(payload.nombre);
    $('#areaCarrera').text(payload.area);
    $('#avatarLetra').text(payload.nombre.charAt(0).toUpperCase());

    // Inicializar DataTable
    var table = $('#tablaSubdirector').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pageLength: 8,
        dom: 'rtip'
    });

    // Función para cargar los datos reales desde la API
    function cargarDatos() {
        fetch(`api/get_filtrado_carrera.php?area=${encodeURIComponent(payload.area)}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        })
        .then(res => res.json())
        .then(data => {
            // Actualizar KPIs
            $('#kpiTotal').text(data.length);
            $('#kpiPendientes').text(data.filter(s => s.estado === 'Pendiente').length);
            $('#kpiAceptadas').text(data.filter(s => s.estado === 'Aceptada').length);
            
            // Limpiar y llenar tabla
            table.clear();
            data.forEach(row => {
                const badge = row.estado === 'Aceptada' ? 'bg-success bg-opacity-10 text-success' : 
                             (row.estado === 'Pendiente' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-danger bg-opacity-10 text-danger');
                
                table.row.add([
                    `<span class="badge bg-light text-sira fw-bold py-2 px-3 border">#${row.folio}</span>`,
                    `<span class="small fw-bold text-secondary">${row.nombre_usuario}</span>`,
                    `<span class="small fw-bold">${row.titulo_event}</span>`,
                    `<span class="small">${row.fecha_evento}</span>`,
                    `<div class="text-center"><span class="badge-status ${badge}">${row.estado.toUpperCase()}</span></div>`,
                    `<div class="text-center"><button class="btn btn-view btn-sm rounded-circle shadow-sm" onclick="verDetalle(${row.id_solicitud})"><i class="bi bi-eye-fill"></i></button></div>`
                ]);
            });
            table.draw();
        });
    }

    cargarDatos();

    // Filtro de Fechas para DataTables
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var min = $('#min_fecha').val();
        var max = $('#max_fecha').val();
        var fechaTxt = data[3]; 
        var partes = fechaTxt.split('/'); // Asumiendo formato dd/mm/yyyy
        var fechaFila = new Date(partes[2], partes[1] - 1, partes[0]);
        
        var dMin = min ? new Date(min + "T00:00:00") : null;
        var dMax = max ? new Date(max + "T23:59:59") : null;

        if ((!dMin && !dMax) || (!dMin && fechaFila <= dMax) || (fechaFila >= dMin && !dMax) || (fechaFila >= dMin && fechaFila <= dMax)) {
            return true;
        }
        return false;
    });

    $('#min_fecha, #max_fecha').on('change', function() { table.draw(); });

    // Generar Reporte PDF enviando el Token por URL
    $('#btnPDFCarrera').on('click', function() {
        const inicio = $('#min_fecha').val(); 
        const fin = $('#max_fecha').val();    

        if (!inicio || !fin) {
            Swal.fire({
                icon: 'info',
                title: 'Rango incompleto',
                text: 'Selecciona una fecha de inicio y fin para filtrar el reporte.',
                confirmButtonColor: '#5B3D66'
            });
            return;
        }

        window.open(`api/generar_reporte.php?token=${localStorage.getItem('token')}&inicio=${inicio}&fin=${fin}`, '_blank');
    });
});

function limpiarFiltrosFecha() {
    $('#min_fecha, #max_fecha').val('').trigger('change');
}

function confirmarSalida() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "La sesión actual terminará.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#5B3D66',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => { 
        if (result.isConfirmed) { 
            localStorage.removeItem('token');
            window.location.href = 'index.php?status=logout'; 
        } 
    });
}
</script>
</body>
</html>