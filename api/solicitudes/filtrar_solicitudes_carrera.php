<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API: CONSULTA DE SOLICITUDES POR ÁREA ACADÉMICA
 * * @package     Controladores_API
 * @subpackage  Gestion_Solicitudes
 * @version     1.0.2
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint recupera el historial de solicitudes vinculadas a una carrera 
 * o área específica de la universidad. Utiliza una arquitectura de consulta 
 * relacional para extraer datos de la tabla 'solicitudes' cruzándolos con 
 * la identidad del usuario solicitante.
 * * ESPECIFICACIONES:
 * 1. Filtro Flexible: Implementa el operador LIKE para mayor coincidencia de cadenas.
 * 2. Formato: Retorno estrictamente en JSON.
 * 3. Seguridad: Sanitización de parámetros vía mysqli_real_escape_string.
 */

// Desactiva el reporte de errores directo para evitar corrupciones en la salida JSON
error_reporting(0); 

/**
 * IMPORTACIÓN DE CONEXIÓN
 */
include("../../config/db_local.php");

/**
 * CONFIGURACIÓN DE CABECERAS
 */
header('Content-Type: application/json');

/**
 * 1. CAPTURA Y SANEAMIENTO DE PARÁMETROS
 * Se recupera la variable 'area' mediante el método GET y se procesa para evitar Inyección SQL.
 */
$area = isset($_GET['area']) ? mysqli_real_escape_string($conexion, $_GET['area']) : '';

// Validación de entrada: Si el área es nula, se retorna un arreglo vacío para evitar errores en el Frontend
if (empty($area)) {
    echo json_encode([]);
    exit;
}

/**
 * 2. CONSULTA RELACIONAL (JOIN)
 * Selecciona campos clave de la solicitud y el nombre del usuario.
 * Se utiliza INNER JOIN para garantizar que solo se muestren solicitudes con un usuario válido.
 * El uso de LIKE '%$area%' permite encontrar coincidencias parciales en los nombres de carrera.
 */
$query = "SELECT s.id_solicitud, s.folio, s.titulo_event, s.fecha_evento, s.estado, u.nombre 
          FROM solicitudes s 
          INNER JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE u.carrera_area LIKE '%$area%' 
          ORDER BY s.id_solicitud DESC";

$resultado = mysqli_query($conexion, $query);
$solicitudes = [];

/**
 * 3. PROCESAMIENTO DE RESULTADOS
 * Itera sobre el set de resultados y construye el arreglo de objetos para el cliente.
 */
if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $solicitudes[] = $fila;
    }
}

/**
 * 4. RESPUESTA AL CLIENTE
 * Envía la colección de solicitudes en formato JSON.
 */
echo json_encode($solicitudes);

/**
 * FINALIZACIÓN DEL SCRIPT
 */
exit;