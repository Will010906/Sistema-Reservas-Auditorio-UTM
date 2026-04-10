<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: FILTRADO DINÁMICO Y MOTOR DE BÚSQUEDA
 * * @package     Controladores_API
 * @subpackage  Gestion_Solicitudes
 * @version     1.0.8
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint procesa peticiones de búsqueda filtradas por rangos cronológicos.
 * Implementa una consulta de triple relación (JOIN) para consolidar datos de 
 * usuarios, espacios y solicitudes en una sola respuesta estructurada.
 * * LÓGICA DE VALOR AGREGADO:
 * Además de retornar datos, el controlador pre-procesa el estado de cada 
 * reservación para asignar "clases de semáforo", optimizando el renderizado 
 * visual en el Frontend.
 */

/**
 * IMPORTACIÓN DE RECURSOS
 */
include '../config/db_local.php';

/**
 * CONFIGURACIÓN DE CABECERAS (HEADERS)
 * Establece la comunicación bajo el estándar RESTful (JSON).
 */
header('Content-Type: application/json');

/**
 * 1. CAPA DE SEGURIDAD (AUTENTICACIÓN)
 * Verifica la presencia del Token Bearer en las cabeceras de la petición HTTP.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'Acceso denegado: Se requiere una sesión activa para realizar filtros.'
    ]);
    exit;
}

/**
 * 2. CAPTURA Y SANEAMIENTO DE PARÁMETROS (GET)
 * Procesa las fechas límites del reporte mediante sanitización de cadenas.
 */
$inicio = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : null;
$fin = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : null;

if (!$inicio || !$fin) {
    echo json_encode([
        'success' => false, 
        'error' => 'Rango de fechas incompleto o inválido.'
    ]);
    exit;
}

/**
 * 3. NÚCLEO FUNCIONAL: CONSULTA MULTI-RELACIONAL
 * 
 * Se realiza un JOIN con 'usuarios' y 'auditorio' para resolver las llaves 
 * foráneas y presentar nombres legibles en lugar de identificadores numéricos.
 */
$query = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
          FROM solicitudes s
          JOIN usuarios u ON s.id_usuario = u.id_usuario
          JOIN auditorio a ON s.id_auditorio = a.id_auditorio
          WHERE s.fecha_evento BETWEEN '$inicio' AND '$fin'
          ORDER BY s.fecha_evento ASC";

$resultado = mysqli_query($conexion, $query);
$solicitudes = [];

/**
 * 4. MOTOR DE LÓGICA DE ESTADOS (SEMÁFORO)
 * Itera los resultados para determinar el comportamiento visual del componente.
 */
while($row = mysqli_fetch_assoc($resultado)) {
    
    /**
     * EVALUACIÓN DINÁMICA DE PRIORIDAD
     * Si la solicitud está pendiente, se evalúa la urgencia.
     * Si ya fue procesada, se asigna el color según el veredicto (Aceptada/Rechazada).
     */
    if ($row['estado'] == 'Pendiente') {
        $row['clase_status'] = ($row['prioridad'] == 'Urgente') ? 'card-urgent' : (($row['prioridad'] == 'Pendiente') ? 'card-pending' : 'card-on-time');
        $row['texto_status'] = strtoupper($row['prioridad']);
    } else {
        $row['clase_status'] = ($row['estado'] == 'Aceptada') ? 'card-accepted' : 'card-rejected';
        $row['texto_status'] = strtoupper($row['estado']);
    }
    
    $solicitudes[] = $row;
}

/**
 * 5. RESPUESTA FINAL DE LA API
 * Devuelve la colección de objetos procesados al cliente solicitante.
 */
echo json_encode($solicitudes);

/**
 * FINALIZACIÓN DEL SCRIPT
 */
exit;