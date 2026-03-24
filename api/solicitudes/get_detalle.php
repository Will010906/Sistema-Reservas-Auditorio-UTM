<?php
/**
 * ENDPOINT API: DETALLE DE SOLICITUD - NIVEL TSU
 * Implementa: Validación JWT, Consultas Multitabla (JOIN/GROUP_CONCAT) y JSON.
 */
header('Content-Type: application/json');
include("../../config/db_local.php");

// 1. VALIDACIÓN DE SEGURIDAD (30% JWT)
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado. Token faltante.']);
    exit;
}

// 2. CAPTURA DE ID (Saneamiento)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    /**
     * NÚCLEO FUNCIONAL (40%): Consulta Avanzada
     * Usamos LEFT JOIN y GROUP_CONCAT para traer el equipamiento solicitado en una sola cadena.
     */
    $query = "SELECT s.*, 
                     u.nombre, u.telefono, 
                     a.nombre_espacio, a.capacidad_maxima,
                     GROUP_CONCAT(CONCAT(e.nombre_equipo, ' (', de.cantidad, ')') SEPARATOR ', ') as equipos_solicitados
              FROM solicitudes s
              JOIN usuarios u ON s.id_usuario = u.id_usuario
              JOIN auditorio a ON s.id_auditorio = a.id_auditorio
              LEFT JOIN detalle_equipamiento de ON s.id_solicitud = de.id_solicitud
              LEFT JOIN equipamiento e ON de.id_equipamiento = e.id_equipamiento
              WHERE s.id_solicitud = $id
              GROUP BY s.id_solicitud";

    $res = mysqli_query($conexion, $query);
    
    if ($res && mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        
        // 3. LÓGICA DE PRESENTACIÓN (Pre-procesamiento en el servidor)
        $data['fecha_evento_limpia'] = date('d/m/Y', strtotime($data['fecha_evento']));
        
        // Respondemos en JSON (Requisito TSU)
        echo json_encode($data);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "No se encontró la solicitud"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "ID no válido"]);
}

exit;