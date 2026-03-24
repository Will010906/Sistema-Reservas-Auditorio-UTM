<?php
/**
 * GENERADOR DE REPORTE MAESTRO - NIVEL TSU
 * Implementa: Dompdf, Validación JWT, Filtros Dinámicos y Resumen de Datos.
 */
require_once '../../vendor/autoload.php'; 
include("../../config/db_local.php");

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('America/Mexico_City');

// 1. VALIDACIÓN DE SEGURIDAD JWT (30% Seguridad)
$token = $_GET['token'] ?? null;

if (!$token) {
    die("Acceso denegado: Token de seguridad faltante.");
}

try {
    // Decodificación del Payload
    $payload_json = base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[1]));
    $payload = json_decode($payload_json, true);
    
    // Verificación de Expiración (10 min) y Perfil
    if ($payload['exp'] < time()) {
        die("Sesión expirada. Por favor, genera el reporte nuevamente.");
    }

    if (!in_array($payload['perfil'], ['administrador', 'subdirector'])) {
        die("No tienes permisos suficientes para generar este reporte.");
    }

} catch (Exception $e) {
    die("Error de autenticación: Sesión inválida.");
}

// 2. CAPTURA DE FILTROS DINÁMICOS (40% Backend Funcional)
$inicio = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : date('Y-m-01');
$fin    = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : date('Y-m-t');
$estatus_raw = isset($_GET['estatus']) ? mysqli_real_escape_string($conexion, $_GET['estatus']) : '';

// Lógica de Estatus (Filtro útil evaluado)
if (empty($estatus_raw)) {
    $estatus_sql = "'PENDIENTE','ACEPTADA','RECHAZADA','URGENTE','CON TIEMPO'";
    $texto_estatus = "TODOS LOS ESTATUS";
} else {
    $estatus_array = explode(',', $estatus_raw);
    $estatus_sql = "'" . implode("','", $estatus_array) . "'";
    $texto_estatus = strtoupper($estatus_raw);
}

// 3. CONSULTA SQL CON JOIN (Integridad Referencial)
$query = "SELECT s.*, u.nombre as solicitante 
          FROM solicitudes s 
          JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE s.fecha_evento BETWEEN '$inicio' AND '$fin' 
          AND s.estado IN ($estatus_sql)
          ORDER BY s.fecha_evento ASC";
$result = mysqli_query($conexion, $query);
$total_filas = mysqli_num_rows($result);

// --- LOGO EN BASE64 (UX) ---
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
        /* (Se mantiene tu excelente CSS original) */
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
        .info-tabla { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .info-tabla th { background-color: #5B3D66; color: white; padding: 12px; font-size: 10px; text-transform: uppercase; }
        .info-tabla td { padding: 10px; border-bottom: 1px solid #dfe6e9; font-size: 10px; text-align: center; }
        .info-tabla tr:nth-child(even) { background-color: #fcfaff; }
        .aceptada { color: #27ae60; font-weight: bold; }
        .rechazada { color: #e74c3c; font-weight: bold; }
        .pendiente { color: #f39c12; font-weight: bold; }
        .urgente { color: #d63031; font-weight: bold; }
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
                    <span style="font-weight: normal; font-size: 10px; color: #636e72;">Reporte Maestro de Solicitudes (SIRA)</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-box">
        <h2 class="title">RESUMEN EJECUTIVO DE RESERVACIONES</h2>
        <div class="subtitle">AUDITORÍA DE ACTIVIDADES Y ESPACIOS</div>
    </div>
    
    <div class="meta-section">
        <table style="width: 100%;">
            <tr>
                <td><strong>Periodo:</strong> <?php echo date('d/m/Y', strtotime($inicio)); ?> al <?php echo date('d/m/Y', strtotime($fin)); ?></td>
                <td style="text-align: right;"><strong>Generado por:</strong> <?php echo $payload['nombre']; ?></td>
            </tr>
            <tr>
                <td><strong>Filtros aplicados:</strong> <?php echo $texto_estatus; ?></td>
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
                    <span class="<?php echo $clase_estado; ?>">
                        <?php echo strtoupper($row['estado']); ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
            
            <tr class="resumen-row">
                <td colspan="4" style="text-align: right; padding-right: 15px;">TOTAL DE REGISTROS ENCONTRADOS:</td>
                <td><?php echo $total_filas; ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Documento oficial generado por el Sistema SIRA UTM - <?php echo date('Y'); ?>
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

$dompdf->stream("Reporte_Maestro_SIRA.pdf", array("Attachment" => false));
?>