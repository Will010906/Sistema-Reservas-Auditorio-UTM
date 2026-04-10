<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: OBTENCIÓN DE RESERVACIONES PROPIAS (VISTA USUARIO)
 * * @package     Controladores_API
 * @subpackage  Gestion_Solicitudes
 * @version     1.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint recupera el historial de reservaciones vinculadas a un usuario 
 * específico. Implementa un modelo de seguridad donde la identidad no se recibe 
 * por parámetro, sino que se extrae directamente del Token Bearer para evitar 
 * la visualización de datos de terceros.
 * * FUNCIONALIDADES:
 * 1. Identificación Segura: Decodificación de JWT para obtener el 'id_usuario'.
 * 2. Consultas Relacionales: Uso de JOIN para obtener nombres de espacios físicos.
 * 3. Analítica Dinámica: Cálculo de acumuladores de estatus para la interfaz gráfica.
 */

/**
 * CONFIGURACIÓN DE SALIDA
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 */
require_once '../../config/db_local.php'; 

/**
 * 1. CAPA DE SEGURIDAD (JWT)
 * Valida la existencia del encabezado de autorización para denegar accesos anónimos.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no válida o inexistente.']);
    exit;
}

try {
    /**
     * EXTRACCIÓN DE IDENTIDAD
     * Decodifica la sección 'Payload' del token para recuperar el ID único del usuario.
     */
    $jwt = $matches[1];
    $tokenParts = explode('.', $jwt);
    if(count($tokenParts) < 2) throw new Exception("Formato de Token inválido");
    
    $payload = json_decode(base64_decode($tokenParts[1]), true);
    $id_usuario = isset($payload['id']) ? (int)$payload['id'] : 0;

    if ($id_usuario <= 0) throw new Exception("Usuario no identificado en el sistema.");

    /**
     * 2. NÚCLEO DE CONSULTA (DATA EXTRACTION)
     * Recupera atributos detallados de las solicitudes ordenadas por fecha de registro.
     */
    $query = "SELECT 
                s.id_solicitud, 
                s.folio, 
                s.titulo_event, 
                s.fecha_evento, 
                s.hora_inicio, 
                s.hora_fin,
                s.estado, 
                s.observaciones_admin, 
                s.prioridad,
                a.nombre_espacio 
              FROM solicitudes s 
              JOIN auditorio a ON s.id_auditorio = a.id_auditorio 
              WHERE s.id_usuario = $id_usuario 
              ORDER BY s.fecha_registro DESC";

    $res = mysqli_query($conexion, $query);
    if (!$res) throw new Exception(mysqli_error($conexion));

    $solicitudes = [];
    while($row = mysqli_fetch_assoc($res)) { 
        $solicitudes[] = $row; 
    }

    /**
     * 3. MOTOR DE ESTADÍSTICAS (ANALYTICS)
     * Procesa la colección de resultados para generar totales de estatus.
     */
    $stats = ["pendientes" => 0, "aprobadas" => 0, "rechazadas" => 0];
    
    foreach ($solicitudes as $s) {
        $estado = strtolower(trim($s['estado']));
        if ($estado == 'pendiente') $stats['pendientes']++;
        elseif ($estado == 'aceptada') $stats['aprobadas']++;
        elseif ($estado == 'rechazada') $stats['rechazadas']++;
    }

    /**
     * RESPUESTA ESTRUCTURADA
     */
    echo json_encode([
        "success" => true,
        "solicitudes" => $solicitudes,
        "stats" => $stats
    ]);

} catch (Exception $e) {
    /**
     * GESTIÓN DE EXCEPCIONES
     */
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * CIERRE DE FLUJO
 */
exit;