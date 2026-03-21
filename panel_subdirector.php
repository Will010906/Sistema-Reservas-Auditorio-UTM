<?php
session_start();
include("config/db_local.php");

if (!isset($_SESSION['id_usuario']) || $_SESSION['perfil'] !== 'subdirector') {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

$mi_area = $_SESSION['carrera_area'];
$nombre_usuario = $_SESSION['nombre'];
$rol_usuario = $_SESSION['perfil'];
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
    
    <style>
        :root {
            --sira-purple: #5B3D66;
            --sira-purple-light: #f4f0f7;
            --sira-dark: #2d1b33;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8f9fa; 
            color: var(--sira-dark); 
        }

        .fw-800 { font-weight: 800; }
        
        .navbar-sira { 
            background-color: white; 
            border-bottom: 2px solid var(--sira-purple-light); 
            padding: 0.8rem 2rem; 
        }

        .user-avatar { 
            width: 42px; height: 42px; 
            background: linear-gradient(135deg, var(--sira-purple) 0%, #3d2945 100%);
            color: white; border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; 
            font-weight: 700; box-shadow: 0 4px 10px rgba(91, 61, 102, 0.2);
        }

        .card-kpi { 
            border: none; border-radius: 24px; transition: all 0.3s ease;
            background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
        .card-kpi:hover { transform: translateY(-5px); }
        
        .border-sira { border-top: 5px solid var(--sira-purple) !important; }

        .icon-box { padding: 1rem; border-radius: 18px; display: inline-flex; }
        .bg-sira-light { background-color: var(--sira-purple-light); }
        .text-sira { color: var(--sira-purple); }

        .card-table { border-radius: 24px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.02); }
        .table thead th { 
            background-color: #fafbfc; border-bottom: none; 
            text-transform: uppercase; font-size: 0.72rem; letter-spacing: 1px; color: #64748b; padding: 1.2rem;
        }
        
        .badge-status { padding: 0.5rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.7rem; }
        .btn-view { 
            background-color: var(--sira-purple); color: white; border: none;
            width: 35px; height: 35px; transition: 0.2s;
        }
        .btn-view:hover { background-color: var(--sira-dark); color: white; transform: scale(1.1); }
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
                <div class="fw-bold mb-0 small"><?php echo $nombre_usuario; ?></div>
                <div class="badge bg-sira-light text-sira text-uppercase fw-700" style="font-size: 0.6rem;"><?php echo $rol_usuario; ?></div>
            </div>
            <div class="user-avatar"><?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?></div>
            <button onclick="confirmarSalida()" class="btn btn-link text-danger ms-3 p-0"><i class="bi bi-power fs-4"></i></button>
        </div>
    </div>
</nav>

<div class="container-fluid px-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-5">
            <h3 class="fw-800 mb-1">Métricas de Supervisión</h3>
            <p class="text-muted small">Carrera: <span class="fw-bold text-sira"><?php echo $mi_area; ?></span></p>
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
        <?php
        $sql_count = "SELECT COUNT(*) as total, SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes, SUM(CASE WHEN estado = 'Aceptada' THEN 1 ELSE 0 END) as aceptadas FROM solicitudes s JOIN usuarios u ON s.id_usuario = u.id_usuario WHERE u.carrera_area = '$mi_area'";
        $res_count = mysqli_fetch_assoc(mysqli_query($conexion, $sql_count));
        ?>
        <div class="col-md-4">
            <div class="card card-kpi p-4 border-sira">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-sira-light me-3"><i class="bi bi-layers-half text-sira fs-3"></i></div>
                    <div>
                        <div class="text-muted small fw-bold">TOTAL SOLICITUDES</div>
                        <h2 class="fw-800 mb-0"><?php echo $res_count['total']; ?></h2>
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
                        <h2 class="fw-800 mb-0"><?php echo $res_count['pendientes']; ?></h2>
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
                        <h2 class="fw-800 mb-0"><?php echo $res_count['aceptadas']; ?></h2>
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
                <tbody>
                    <?php
                    $query = "SELECT s.*, u.nombre as solicitante FROM solicitudes s JOIN usuarios u ON s.id_usuario = u.id_usuario WHERE u.carrera_area = '$mi_area' ORDER BY s.fecha_evento ASC";
                    $result = mysqli_query($conexion, $query);
                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr class="solicitud-fila">
                        <td><span class="badge bg-light text-sira fw-bold py-2 px-3 border">#<?php echo $row['folio']; ?></span></td>
                        <td class="small fw-bold text-secondary"><?php echo $row['solicitante']; ?></td>
                        <td class="small fw-bold"><?php echo $row['titulo_event']; ?></td>
                        <td class="small"><?php echo date('d/m/Y', strtotime($row['fecha_evento'])); ?></td>
                        <td class="text-center">
                            <span class="badge-status <?php echo ($row['estado']=='Aceptada') ? 'bg-success bg-opacity-10 text-success' : (($row['estado']=='Pendiente') ? 'bg-warning bg-opacity-10 text-warning' : 'bg-danger bg-opacity-10 text-danger'); ?>">
                                <?php echo strtoupper($row['estado']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-view btn-sm rounded-circle shadow-sm" onclick="verDetalleUsuario(<?php echo $row['id_solicitud']; ?>)">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
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
    // 1. Inicialización de DataTable
    var table = $('#tablaSubdirector').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pageLength: 8,
        dom: 'rtip'
    });

    // 2. Filtro de Fechas para DataTables
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var min = $('#min_fecha').val();
        var max = $('#max_fecha').val();
        var fechaTxt = data[3]; // Columna Fecha
        var partes = fechaTxt.split('/');
        var fechaFila = new Date(partes[2], partes[1] - 1, partes[0]);
        
        var dMin = min ? new Date(min + "T00:00:00") : null;
        var dMax = max ? new Date(max + "T23:59:59") : null;

        if ((!dMin && !dMax) || (!dMin && fechaFila <= dMax) || (fechaFila >= dMin && !dMax) || (fechaFila >= dMin && fechaFila <= dMax)) {
            return true;
        }
        return false;
    });

    $('#min_fecha, #max_fecha').on('change', function() { table.draw(); });

    // 3. Generar Reporte con Filtros
    // Localiza esta parte en tu código y reemplázala
$('#btnPDFCarrera').on('click', function() {
    const area = "<?php echo $mi_area; ?>";
    
    // CAPTURAMOS LAS FECHAS ACTUALES DE LOS INPUTS
    const inicio = $('#min_fecha').val(); 
    const fin = $('#max_fecha').val();    

    // Validamos: Si Saúl no ha puesto fechas, le avisamos (opcional)
    if (!inicio || !fin) {
        Swal.fire({
            icon: 'info',
            title: 'Rango incompleto',
            text: 'Por favor selecciona una fecha de inicio y fin para filtrar el reporte.',
            confirmButtonColor: '#5B3D66'
        });
        return;
    }

    // ENVIAMOS LAS FECHAS POR LA URL
    window.open(`modules/generar_reporte_carrera.php?area=${encodeURIComponent(area)}&inicio=${inicio}&fin=${fin}`, '_blank');
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
    }).then((result) => { if (result.isConfirmed) { window.location.href = 'modules/logout.php'; } });
}
</script>
</body>
</html>