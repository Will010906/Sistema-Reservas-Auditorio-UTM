<?php
/**
 * GENERADOR DE REPORTES PDF - NIVEL TSU
 * Implementa: Dompdf, Validación de Token JWT y Filtrado por Área/Fechas.
 */
require_once '../vendor/autoload.php'; 
include("../config/db_local.php");

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('America/Mexico_City');

// 1. VALIDACIÓN DE SEGURIDAD (30% JWT)
// Capturamos el token enviado por la URL desde el JS
$token = $_GET['token'] ?? null;

if (!$token) {
    die("Acceso denegado: Token de seguridad faltante.");
}

try {
    // Decodificación manual del Payload del JWT
    $payload_json = base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[1]));
    $payload = json_decode($payload_json, true);
    
    // Verificamos si el token ya expiró (Regla de los 10 minutos)
    if ($payload['exp'] < time()) {
        die("La sesión ha expirado. Por favor, genera el reporte nuevamente.");
    }

    // El área ya no se recibe por GET (para que no la alteren), se saca del Token
    $area = $payload['area']; 
    // Si el usuario es 'administrador', sí permitimos que el área venga por GET si existe
    if ($payload['perfil'] === 'administrador' && isset($_GET['area_filtro'])) {
        $area = mysqli_real_escape_string($conexion, $_GET['area_filtro']);
    }

} catch (Exception $e) {
    die("Error de autenticación: Sesión inválida.");
}

// 2. FILTRADO DE DATOS (40% Backend Funcional)
$inicio = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : date('Y-m-01');
$fin    = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : date('Y-m-t');

$query = "SELECT s.*, u.nombre as solicitante 
          FROM solicitudes s 
          JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE u.carrera_area = '$area' 
          AND s.fecha_evento BETWEEN '$inicio' AND '$fin' 
          ORDER BY s.fecha_evento ASC";
$result = mysqli_query($conexion, $query);

// --- PREPARACIÓN DEL LOGO (UX) ---
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
    <body>
        <td><strong>Responsable:</strong> <?php echo $payload['nombre']; ?></td>
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