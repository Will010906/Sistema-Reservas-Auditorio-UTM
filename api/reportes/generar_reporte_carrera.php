<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * SERVICIO DE GENERACIÓN DE REPORTES PDF PROFESIONAL
 * * @package     Servicios_Reporteo
 * @subpackage  Exportacion_PDF
 * @version     1.2.5
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador transforma datos relacionales de la base de datos en un
 * documento PDF estructurado. Utiliza el motor Dompdf para el renderizado
 * de HTML5/CSS3 y garantiza la integridad mediante validación de tokens JWT.
 * * CAPACIDADES CLAVE:
 * 1. Validación de Sesión: Decodificación y verificación de expiración de Payload.
 * 2. Procesamiento de Activos: Conversión de logotipos a Base64 para evitar errores de ruta.
 * 3. Gestión de Buffer: Uso de ob_start() para capturar la maquetación dinámica.
 */

// Carga de dependencias y configuración de base de datos
require_once '../../vendor/autoload.php'; 
include("../../config/db_local.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// Sincronización horaria para metadatos del reporte
date_default_timezone_set('America/Mexico_City');

/**
 * 1. SEGURIDAD Y CONTROL DE ACCESO (JWT)
 * Recupera el token por URL y valida la identidad del solicitante.
 */
$token = $_GET['token'] ?? null;
if (!$token) { die("Acceso denegado: Token de seguridad no detectado."); }

try {
    /**
     * DECODIFICACIÓN DEL PAYLOAD
     * Se procesa la segunda sección del token para extraer permisos y datos del usuario.
     */
    $payload_json = base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[1]));
    $payload = json_decode($payload_json, true);
    
    // Verificación de vigencia temporal del token
    if ($payload['exp'] < time()) { die("Error: La sesión ha expirado."); }

    /**
     * FILTRADO JERÁRQUICO
     * Define el alcance de los datos según el perfil del usuario autenticado.
     */
    $area = $payload['area']; 
    if ($payload['perfil'] === 'administrador' && isset($_GET['area_filtro'])) {
        $area = mysqli_real_escape_string($conexion, $_GET['area_filtro']);
    }
} catch (Exception $e) { 
    die("Fallo en la autenticación: El token proporcionado es inválido."); 
}

/**
 * 2. PROCESAMIENTO DE DATOS (SQL)
 * Determina el rango de fechas y extrae las reservaciones vinculadas al área.
 */
$inicio = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : date('Y-m-01');
$fin    = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : date('Y-m-t');

// Cambiamos el SELECT para incluir u.matricula
$query = "SELECT s.*, u.nombre as solicitante, u.matricula 
          FROM solicitudes s 
          JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE u.carrera_area = '$area' 
          AND s.fecha_evento BETWEEN '$inicio' AND '$fin' 
          ORDER BY s.fecha_evento ASC";
$result = mysqli_query($conexion, $query);

/**
 * 3. GESTIÓN DE ACTIVOS MULTIMEDIA (LOGO)
 * Codificación Base64 para asegurar el renderizado de la imagen en entornos PDF.
 */
$base64 = "";
$path = '../../assets/img/logo_app_web_RA.png'; 
if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

$css_path = '../../assets/css/reporte_carrera.css';
$estilos_carrera = file_exists($css_path) ? file_get_contents($css_path) : "";
/**
 * 4. MAQUETACIÓN DEL DOCUMENTO (BUFFER)
 * Inicia la captura de salida para procesar el HTML dinámico.
 */
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
  <style>
        /* Inyección de estilos profesionales desde archivo externo */
        <?php echo $estilos_carrera; ?>
    </style>
</head>
<body>

<header>
    <?php if($base64): ?>
        <img src="<?php echo $base64; ?>" class="logo">
    <?php endif; ?>
    <div class="info-header">
        <strong>Sistema SIRA | UTM</strong><br>
        Generado el: <?php echo date('d/m/Y H:i'); ?>
    </div>
</header>

<footer>
    Universidad Tecnológica de Morelia - Gestión de Auditorios SIRA
</footer>

<div class="title">REPORTE DE RESERVACIONES POR CARRERA</div>

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
            <th class="col-matricula">Matrícula/ID</th> <th>Solicitante</th>
            <th>Evento</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td style="font-weight: bold;">#<?php echo $row['folio']; ?></td>
            <td style="color: #555;"><?php echo $row['matricula']; ?></td> <td style="text-align: left;"><?php echo $row['solicitante']; ?></td>
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
/**
 * 5. RENDERIZADO FINAL
 * Captura el HTML generado y ejecuta el motor Dompdf.
 */
$html = ob_get_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Definición dinámica del nombre del archivo PDF
$nombreArchivo = "SIRA_Reporte_" . str_replace(" ", "_", $area) . "_" . date('dmY') . ".pdf";

/**
 * SALIDA DEL FLUJO BINARIO
 * @param bool Attachment Define si se descarga directamente o se previsualiza.
 */
$dompdf->stream($nombreArchivo, array("Attachment" => false));
?>