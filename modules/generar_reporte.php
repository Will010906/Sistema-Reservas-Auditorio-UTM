<?php
session_start();
require_once '../vendor/autoload.php'; 
include("../config/db_local.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Configuración de Zona Horaria (México)
date_default_timezone_set('America/Mexico_City');

// 2. Verificación de Seguridad
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['perfil'], ['administrador', 'subdirector'])) {
    die("Acceso no autorizado");
}

// 3. Captura de filtros desde el JS
$inicio  = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : date('Y-m-01');
$fin     = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : date('Y-m-t');
$estatus_raw = isset($_GET['estatus']) ? mysqli_real_escape_string($conexion, $_GET['estatus']) : '';

// --- LÓGICA DE ESTATUS CORREGIDA ---
if (empty($estatus_raw)) {
    // Si viene vacío (porque marcaron TODOS), definimos todos los estados posibles
    $estatus_sql = "'PENDIENTE','ACEPTADA','RECHAZADA','URGENTE','CON TIEMPO'";
    $texto_estatus = "TODOS LOS ESTATUS";
} else {
    // Si vienen estatus específicos, los procesamos normalmente
    $estatus_array = explode(',', $estatus_raw);
    $estatus_sql = "'" . implode("','", $estatus_array) . "'";
    $texto_estatus = strtoupper($estatus_raw);
}

// 4. Consulta Filtrada Dinámica (Ordenada por Fecha)
$query = "SELECT s.*, u.nombre as solicitante 
          FROM solicitudes s 
          JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE s.fecha_evento BETWEEN '$inicio' AND '$fin' 
          AND s.estado IN ($estatus_sql)
          ORDER BY s.fecha_evento ASC";
$result = mysqli_query($conexion, $query);
$total_filas = mysqli_num_rows($result);

// 5. Preparación del Logo
$base64 = "";
$path = '../assets/img/logo_app_web_RA.png'; 
if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

ob_start();
?>
<html>
<head>
    <style>
        @page { margin: 120px 50px 80px 50px; }
        body { font-family: 'Helvetica', sans-serif; color: #2d3436; line-height: 1.4; }
        .header { position: fixed; top: -95px; left: 0px; right: 0px; height: 90px; border-bottom: 3px solid #5B3D66; }
        .header table { width: 100%; border: none; }
        .logo { width: 130px; }
        .header-text { text-align: right; font-size: 14px; color: #5B3D66; font-weight: bold; }
        .title-box { text-align: center; margin-top: 20px; }
        .title { color: #5B3D66; text-transform: uppercase; font-size: 19px; margin-bottom: 5px; }
        .subtitle { font-size: 11px; color: #636e72; letter-spacing: 1px; }
        .meta-section { margin: 20px 0; font-size: 11px; padding: 15px; background: #f8f9fa; border-radius: 10px; }
        .meta-section strong { color: #5B3D66; }
        .info-tabla { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .info-tabla th { background-color: #5B3D66; color: white; padding: 12px; font-size: 10px; text-transform: uppercase; }
        .info-tabla td { padding: 10px; border-bottom: 1px solid #dfe6e9; font-size: 10px; text-align: center; }
        .info-tabla tr:nth-child(even) { background-color: #fcfaff; }
        .status { font-weight: bold; font-size: 9px; }
        
        /* Colores de estatus */
        .aceptada { color: #27ae60; }
        .rechazada { color: #e74c3c; }
        .pendiente { color: #f39c12; }
        .urgente { color: #d63031; }
        
        .resumen-row { background-color: #f1f2f6 !important; font-weight: bold; }
        .footer { position: fixed; bottom: -50px; width: 100%; font-size: 9px; text-align: center; color: #b2bec3; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td><?php if($base64): ?><img src="<?php echo $base64; ?>" class="logo"><?php endif; ?></td>
                <td class="header-text">UNIVERSIDAD TECNOLÓGICA DE MORELIA<br>
                    <span style="font-weight: normal; font-size: 10px; color: #636e72;">Sistema de Reservación de Auditorios (SIRA)</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-box">
        <h2 class="title">REPORTE DE SOLICITUDES</h2>
        <div class="subtitle">FILTRADO POR RANGO DE FECHAS Y ESTATUS</div>
    </div>
    
    <div class="meta-section">
        <table style="width: 100%;">
            <tr>
                <td><strong>Periodo:</strong> <?php echo date('d/m/Y', strtotime($inicio)); ?> al <?php echo date('d/m/Y', strtotime($fin)); ?></td>
                <td style="text-align: right;"><strong>Emitido por:</strong> <?php echo $_SESSION['nombre']; ?></td>
            </tr>
            <tr>
                <td><strong>Estatus incluidos:</strong> <?php echo $texto_estatus; ?></td>
                <td style="text-align: right;"><strong>Fecha impresión:</strong> <?php echo date('d/m/Y H:i'); ?></td>
            </tr>
        </table>
    </div>

    <table class="info-tabla">
        <thead>
            <tr>
                <th style="width: 12%;">Folio</th>
                <th style="width: 25%;">Evento</th>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 30%;">Solicitante</th>
                <th style="width: 18%;">Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): 
                $clase_estado = strtolower($row['estado']);
                if ($row['estado'] == 'Pendiente' && $row['prioridad'] == 'Urgente') $clase_estado = 'urgente';
            ?>
            <tr>
                <td><strong>#<?php echo $row['folio']; ?></strong></td>
                <td style="text-align: left; padding-left: 10px;"><?php echo $row['titulo_event']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['fecha_evento'])); ?></td>
                <td><?php echo $row['solicitante']; ?></td>
                <td>
                    <span class="status <?php echo $clase_estado; ?>">
                        <?php echo strtoupper($row['estado']); ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
            
            <tr class="resumen-row">
                <td colspan="4" style="text-align: right; padding-right: 15px;">TOTAL DE SOLICITUDES:</td>
                <td><?php echo $total_filas; ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Página 1 de 1 - SIRA UTM <?php echo date('Y'); ?>
    </div>
</body>
</html>
<?php
    $html = ob_get_clean();
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); 

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream("Reporte_SIRA_Admin.pdf", array("Attachment" => false));
?>