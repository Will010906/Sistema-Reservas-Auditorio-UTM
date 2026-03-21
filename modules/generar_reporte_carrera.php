<?php
session_start();
require_once '../vendor/autoload.php'; 
include("../config/db_local.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Configuración de Zona Horaria para México
date_default_timezone_set('America/Mexico_City');

if (isset($_GET['area'])) {
    $area = mysqli_real_escape_string($conexion, $_GET['area']);
    
   $inicio  = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : date('Y-m-01');
$fin     = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : date('Y-m-t');

// Tu consulta SQL debe usar esas variables en el WHERE
$query = "SELECT s.*, u.nombre as solicitante 
          FROM solicitudes s 
          JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE u.carrera_area = '$area' 
          AND s.fecha_evento BETWEEN '$inicio' AND '$fin' 
          ORDER BY s.fecha_evento ASC";
    $result = mysqli_query($conexion, $query);

    // 3. Preparación del Logo en Base64
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
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; }
        
        .header { position: fixed; top: -90px; left: 0px; right: 0px; height: 80px; border-bottom: 2px solid #5B3D66; }
        .header table { width: 100%; border: none; }
        .logo { width: 120px; }
        .header-text { text-align: right; font-size: 14px; color: #5B3D66; font-weight: bold; }
        
        .title { text-align: center; color: #5B3D66; margin-top: 20px; text-transform: uppercase; font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #666; text-align: center; margin-bottom: 25px; font-style: italic; }

        .meta-data { margin-bottom: 20px; font-size: 11px; }
        .meta-data strong { color: #5B3D66; }

        .info-tabla { width: 100%; border-collapse: collapse; }
        .info-tabla th { background-color: #5B3D66; color: white; padding: 10px; font-size: 10px; text-transform: uppercase; }
        .info-tabla td { padding: 10px; border-bottom: 1px solid #eee; font-size: 9px; text-align: center; }
        .info-tabla tr:nth-child(even) { background-color: #fcfaff; }

        /* Colores de Estatus según Bitácora */
        .status-aceptada { color: #28a745; font-weight: bold; }
        .status-rechazada { color: #dc3545; font-weight: bold; }
        .status-pendiente { color: #f39c12; font-weight: bold; }
        .status-urgente { color: #d63031; font-weight: bold; }

        .footer { position: fixed; bottom: -50px; left: 0px; right: 0px; height: 30px; font-size: 9px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td><?php if($base64): ?><img src="<?php echo $base64; ?>" class="logo"><?php endif; ?></td>
                <td class="header-text">SIRA | SISTEMA DE RESERVACIÓN<br><span style="font-weight: normal; font-size: 10px; color: #777;">Universidad Tecnológica de Morelia</span></td>
            </tr>
        </table>
    </div>

    <h2 class="title">Reporte Mensual de Reservaciones</h2>
    <div class="subtitle">Bitácora oficial de uso de espacios audiovisuales</div>
    
    <div class="meta-data">
        <table style="width: 100%;">
            <tr>
                <td><strong>Área:</strong> <?php echo $area; ?></td>
                <td style="text-align: right;"><strong>Fecha de emisión:</strong> <?php echo date('d/m/Y'); ?></td>
            </tr>
            <tr>
                <td><strong>Responsable:</strong> Saúl</td>
                <td style="text-align: right;"><strong>Hora:</strong> <?php echo date('H:i'); ?></td>
            </tr>
        </table>
    </div>

    <table class="info-tabla">
        <thead>
            <tr>
                <th style="width: 12%;">Folio</th>
                <th style="width: 23%;">Solicitante</th>
                <th style="width: 30%;">Evento</th>
                <th style="width: 18%;">Fecha</th>
                <th style="width: 17%;">Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): 
                $claseStatus = 'status-' . strtolower($row['estado']);
                if ($row['estado'] == 'Pendiente' && $row['prioridad'] == 'Urgente') $claseStatus = 'status-urgente';
            ?>
            <tr>
                <td><strong>#<?php echo $row['folio']; ?></strong></td>
                <td style="text-align: left;"><?php echo $row['solicitante']; ?></td>
                <td style="text-align: left;"><?php echo $row['titulo_event']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['fecha_evento'])); ?></td>
                <td class="<?php echo $claseStatus; ?>"><?php echo strtoupper($row['estado']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="footer">
        Página 1 de 1 - Documento generado por SIRA UTM <?php echo date('Y'); ?>
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

    $nombreArchivo = "SIRA_Bitacora_" . str_replace(" ", "_", $area) . "_" . date('dmY') . ".pdf";
    $dompdf->stream($nombreArchivo, array("Attachment" => false));
}
?>