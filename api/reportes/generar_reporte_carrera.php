<?php
/**
 * GENERADOR DE REPORTES PDF PROFESIONAL - SIRA UTM
 * Implementa: Dompdf, Estilos UTM, Sincronización con Base de Datos.
 */
require_once '../../vendor/autoload.php'; // Ajusta la ruta si es necesario
include("../../config/db_local.php");

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('America/Mexico_City');

// 1. VALIDACIÓN DE SEGURIDAD JWT
$token = $_GET['token'] ?? null;
if (!$token) { die("Acceso denegado."); }

try {
    $payload_json = base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[1]));
    $payload = json_decode($payload_json, true);
    
    if ($payload['exp'] < time()) { die("Sesión expirada."); }

    $area = $payload['area']; 
    if ($payload['perfil'] === 'administrador' && isset($_GET['area_filtro'])) {
        $area = mysqli_real_escape_string($conexion, $_GET['area_filtro']);
    }
} catch (Exception $e) { die("Error de autenticación."); }

// 2. FILTRADO DE DATOS (Ajustado a tu diagrama)
$inicio = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : date('Y-m-01');
$fin    = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : date('Y-m-t');

$query = "SELECT s.*, u.nombre as solicitante 
          FROM solicitudes s 
          JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE u.carrera_area = '$area' 
          AND s.fecha_evento BETWEEN '$inicio' AND '$fin' 
          ORDER BY s.fecha_evento ASC";
$result = mysqli_query($conexion, $query);

// --- LOGO EN BASE64 ---
$base64 = "";
$path = '../../assets/img/logo_app_web_RA.png'; // Verifica que la ruta sea correcta
if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 50px; }
        header { position: fixed; top: -60px; left: 0; right: 0; height: 80px; border-bottom: 2px solid #5B3D66; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 30px; font-size: 10px; text-align: center; color: #777; }
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; }
        
        .logo { height: 50px; float: left; }
        .info-header { float: right; text-align: right; margin-top: 5px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; color: #5B3D66; margin-top: 20px; clear: both; }
        
        .summary-box { background: #f4f0f7; padding: 15px; border-radius: 10px; margin-top: 20px; }
        .summary-box table { width: 100%; border: none; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        table.data-table th { background-color: #5B3D66; color: white; padding: 10px; text-transform: uppercase; font-size: 10px; }
        table.data-table td { padding: 8px; border-bottom: 1px solid #eee; text-align: center; }
        
        .badge-aceptada { color: #2e7d32; font-weight: bold; }
        .badge-rechazada { color: #d32f2f; font-weight: bold; }
        .badge-pendiente { color: #f9a825; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <?php if($base64): ?>
        <img src="<?php echo $base64; ?>" class="logo">
    <?php endif; ?>
    <div class="info-header">
        <strong>Sistema SIRA UTM</strong><br>
        Reporte Generado el: <?php echo date('d/m/Y H:i'); ?>
    </div>
</header>

<footer>
    Página <script type="text/javascript">document.write(page_number);</script> | Universidad Tecnológica de Morelia
</footer>

<div class="title">REPORTE MENSUAL DE RESERVACIONES</div>

<div class="summary-box">
    <table>
        <tr>
            <td><strong>Responsable:</strong> <?php echo $payload['nombre']; ?></td>
            <td><strong>Área Académica:</strong> <?php echo $area; ?></td>
        </tr>
        <tr>
            <td><strong>Periodo:</strong> <?php echo date('d/m/Y', strtotime($inicio)); ?> al <?php echo date('d/m/Y', strtotime($fin)); ?></td>
            <td><strong>Total Registros:</strong> <?php echo mysqli_num_rows($result); ?></td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Folio</th>
            <th>Solicitante</th>
            <th>Evento</th>
            <th>Fecha</th>
            <th>Estatus</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td style="font-weight: bold;">#<?php echo $row['folio']; ?></td>
            <td><?php echo $row['solicitante']; ?></td>
            <td style="text-align: left;"><?php echo $row['titulo_event']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($row['fecha_evento'])); ?></td>
            <td>
                <?php 
                    $estatus = strtoupper($row['estado']);
                    $clase = 'badge-' . strtolower($estatus);
                    echo "<span class='$clase'>$estatus</span>";
                ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

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

$nombreArchivo = "SIRA_Reporte_" . str_replace(" ", "_", $area) . "_" . date('dmY') . ".pdf";
$dompdf->stream($nombreArchivo, array("Attachment" => false));
?>