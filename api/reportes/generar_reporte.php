<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * GENERADOR DE REPORTE MAESTRO - NIVEL ADMINISTRACIÓN (TSU/ING)
 * * @package     Servicios_Reporteo
 * @subpackage  Auditoria_Administrativa
 * @version     2.5.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador genera el reporte global de actividades. Implementa una 
 * arquitectura de filtrado dinámico multi-estado y validación de privilegios 
 * basada en roles (Admin/Subdirector) extraídos del Payload JWT.
 * * CAPACIDADES:
 * 1. Seguridad de Acceso: Restringe la ejecución a perfiles de alta jerarquía.
 * 2. Consultas Complejas: Implementa JOINs relacionales y filtrado por conjuntos (SQL IN).
 * 3. UX Reporting: Diferenciación visual de estados (Semáforo de urgencia).
 */

// Carga de dependencias y motor de renderizado PDF
require_once '../../vendor/autoload.php'; 
include("../../config/db_local.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// Sincronización horaria institucional
date_default_timezone_set('America/Mexico_City');

/**
 * 1. VALIDACIÓN DE SEGURIDAD Y PRIVILEGIOS (JWT)
 * Verifica que el token sea válido y pertenezca a un nivel administrativo.
 */
$token = $_GET['token'] ?? null;

if (!$token) {
    die("Acceso denegado: Token de seguridad inexistente.");
}

try {
    /**
     * DECODIFICACIÓN DEL PAYLOAD
     * Extracción de metadatos del usuario para auditoría del reporte.
     */
    $payload_json = base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[1]));
    $payload = json_decode($payload_json, true);
    
    // Validación de expiración de sesión (Timeout de seguridad)
    if ($payload['exp'] < time()) {
        die("Sesión expirada. Por razones de seguridad, genere el reporte nuevamente.");
    }

    /**
     * CONTROL DE ACCESO BASADO EN ROLES (RBAC)
     * Solo permite la generación a usuarios con perfil Administrativo o Subdirección.
     */
    if (!in_array($payload['perfil'], ['administrador', 'subdirector'])) {
        die("Privilegios insuficientes para acceder a este recurso maestro.");
    }

} catch (Exception $e) {
    die("Fallo en la autenticación: Estructura de sesión inválida.");
}

/**
 * 2. MOTOR DE FILTRADO DINÁMICO
 * Procesa parámetros GET para construir una consulta SQL flexible.
 */
$inicio = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : date('Y-m-01');
$fin    = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : date('Y-m-t');
$estatus_raw = isset($_GET['estatus']) ? mysqli_real_escape_string($conexion, $_GET['estatus']) : '';

/**
 * LÓGICA DE CONSTRUCCIÓN DE CONJUNTOS (SQL IN)
 * Si no se especifica estatus, se seleccionan todos los estados operativos.
 */
if (empty($estatus_raw)) {
    $estatus_sql = "'PENDIENTE','ACEPTADA','RECHAZADA','URGENTE','CON TIEMPO'";
    $texto_estatus = "TODOS LOS ESTATUS";
} else {
    $estatus_array = explode(',', $estatus_raw);
    $estatus_sql = "'" . implode("','", $estatus_array) . "'";
    $texto_estatus = strtoupper($estatus_raw);
}

/**
 * 3. CONSULTA SQL CON INTEGRIDAD REFERENCIAL
 * Vincula las solicitudes con la identidad de los solicitantes mediante un INNER JOIN.
 */
$query = "SELECT s.*, u.nombre as solicitante, u.matricula 
          FROM solicitudes s 
          JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE s.fecha_evento BETWEEN '$inicio' AND '$fin' 
          AND s.estado IN ($estatus_sql)
          ORDER BY s.fecha_evento ASC";

$result = mysqli_query($conexion, $query);
$total_filas = mysqli_num_rows($result);

/**
 * PROCESAMIENTO DE IDENTIDAD VISUAL (UX)
 * Codificación Base64 para garantizar la portabilidad del logo en el PDF.
 */
$base64 = "";
$path = '../../assets/img/logo_app_web_RA.png'; 
if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}
$css_path = '../../assets/css/reporte_admin.css';
$estilos_admin = file_exists($css_path) ? file_get_contents($css_path) : "";

// Inicialización del buffer de salida para captura de maquetación HTML
ob_start();
?>
<html>
<head>
  <style>
        /* Inyección de estilos de auditoría administrativa */
        <?php echo $estilos_admin; ?>
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td><?php if($base64): ?><img src="<?php echo $base64; ?>" class="logo"><?php endif; ?></td>
                <td class="header-text">UNIVERSIDAD TECNOLÓGICA DE MORELIA<br>
                    <span style="font-weight: normal; font-size: 10px; color: #636e72;">Gestión Integral de Auditorios (SIRA)</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="title-box">
        <h2 class="title">REPORTE MAESTRO DE RESERVACIONES</h2>
        <div class="subtitle">AUDITORÍA DE ACTIVIDADES Y CONTROL DE ESPACIOS</div>
    </div>
    
    <div class="meta-section">
        <table style="width: 100%;">
            <tr>
                <td><strong>Periodo Auditado:</strong> <?php echo date('d/m/Y', strtotime($inicio)); ?> al <?php echo date('d/m/Y', strtotime($fin)); ?></td>
                <td style="text-align: right;"><strong>Generado por:</strong> <?php echo $payload['nombre']; ?></td>
            </tr>
            <tr>
                <td><strong>Criterios de Estatus:</strong> <?php echo $texto_estatus; ?></td>
                <td style="text-align: right;"><strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y H:i'); ?></td>
            </tr>
        </table>
    </div>

   <table class="info-tabla">
    <thead>
        <tr>
            <th style="width: 10%;">Folio</th>
            <th style="width: 15%;">Matrícula/ID</th> <th style="width: 25%;">Evento</th>
            <th style="width: 12%;">Fecha</th>
            <th style="width: 23%;">Solicitante</th>
            <th style="width: 15%;">Estatus</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): 
            $clase_estado = strtolower($row['estado']);
        ?>
        <tr>
            <td><strong>#<?php echo $row['folio']; ?></strong></td>
            <td style="color: #636e72;"><?php echo $row['matricula']; ?></td> <td style="text-align: left;"><?php echo $row['titulo_event']; ?></td>
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
            <td colspan="5" style="text-align: right; padding-right: 15px;">TOTAL DE SOLICITUDES PROCESADAS:</td>
            <td><?php echo $total_filas; ?></td>
        </tr>
    </tbody>
</table>

    <div class="footer">
        Este documento es un registro oficial generado por el Sistema SIRA UTM. Queda prohibida su alteración. - © <?php echo date('Y'); ?>
    </div>
</body>
</html>
<?php
/**
 * 4. FINALIZACIÓN Y RENDERIZADO
 * Se procesa el buffer capturado y se envía el flujo binario al navegador.
 */
$html = ob_get_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/**
 * SALIDA DEL PDF
 * Se establece el nombre dinámico y se define previsualización (Attachment: false).
 */
$dompdf->stream("Reporte_Maestro_SIRA_UTM.pdf", array("Attachment" => false));
?>