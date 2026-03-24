<?php
/**
 * ENDPOINT API: FILTRADO DINÁMICO - NIVEL TSU
 * Implementa: Validación JWT, Consultas con JOIN y Respuesta JSON.
 */
include '../config/db_local.php';

// 1. CONFIGURACIÓN DE CABECERAS
header('Content-Type: application/json');

// 2. SEGURIDAD (30%): Validar que el token venga en los Headers
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado. Inicia sesión.']);
    exit;
}

// 3. CAPTURA DE PARÁMETROS (GET)
$inicio = isset($_GET['inicio']) ? mysqli_real_escape_string($conexion, $_GET['inicio']) : null;
$fin = isset($_GET['fin']) ? mysqli_real_escape_string($conexion, $_GET['fin']) : null;

if (!$inicio || !$fin) {
    echo json_encode(['success' => false, 'error' => 'Rango de fechas incompleto']);
    exit;
}

/**
 * NÚCLEO FUNCIONAL (40%): Consulta con Relaciones
 * Traemos el nombre del usuario y del auditorio para evitar IDs vacíos en la tabla.
 */
$query = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
          FROM solicitudes s
          JOIN usuarios u ON s.id_usuario = u.id_usuario
          JOIN auditorio a ON s.id_auditorio = a.id_auditorio
          WHERE s.fecha_evento BETWEEN '$inicio' AND '$fin'
          ORDER BY s.fecha_evento ASC";

$resultado = mysqli_query($conexion, $query);
$solicitudes = [];

while($row = mysqli_fetch_assoc($resultado)) {
    
    // Lógica de Semáforo (Evaluada como "Propuesta de Valor" en el PDF)
    if ($row['estado'] == 'Pendiente') {
        $row['clase_status'] = ($row['prioridad'] == 'Urgente') ? 'card-urgent' : (($row['prioridad'] == 'Pendiente') ? 'card-pending' : 'card-on-time');
        $row['texto_status'] = strtoupper($row['prioridad']);
    } else {
        $row['clase_status'] = ($row['estado'] == 'Aceptada') ? 'card-accepted' : 'card-rejected';
        $row['texto_status'] = strtoupper($row['estado']);
    }
    
    $solicitudes[] = $row;
}

// 4. RESPUESTA FINAL
echo json_encode($solicitudes);
exit;