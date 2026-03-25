<?php
/**
 * API: OBTENER RESERVACIONES PROPIAS - SIRA UTM
 */
header('Content-Type: application/json');
require_once '../../config/db_local.php'; 

$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
    exit;
}

try {
    $jwt = $matches[1];
    $tokenParts = explode('.', $jwt);
    if(count($tokenParts) < 2) throw new Exception("Formato de Token inválido");
    
    $payload = json_decode(base64_decode($tokenParts[1]), true);
    // Forzamos el ID a entero para evitar inyecciones o errores de tipo
    $id_usuario = isset($payload['id']) ? (int)$payload['id'] : 0; 

    if ($id_usuario <= 0) throw new Exception("Usuario no identificado");

    /**
     * CONSULTA BASADA EN EL DER
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

    // Estadísticas para las Cards dinámicas
    $stats = ["pendientes" => 0, "aprobadas" => 0, "rechazadas" => 0];
    
    foreach ($solicitudes as $s) {
        // Usamos strtolower para que no importe si en la DB dice 'Aceptada' o 'ACEPTADA'
        $estado = strtolower($s['estado']);
        if ($estado == 'pendiente') $stats['pendientes']++;
        elseif ($estado == 'aceptada') $stats['aprobadas']++; 
        elseif ($estado == 'rechazada') $stats['rechazadas']++;
    }

    echo json_encode([
        "success" => true,
        "solicitudes" => $solicitudes,
        "stats" => $stats
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;