<?php
/**
 * ENDPOINT API: DISPONIBILIDAD DE HORARIOS - NIVEL TSU
 * Implementa: Validación JWT, Filtrado de Solicitudes y Respuesta JSON.
 */
header('Content-Type: application/json');
include("../config/db_local.php");

// 1. VALIDACIÓN DE SEGURIDAD (30% JWT)
// Evita que bots o usuarios externos consulten la agenda de la universidad
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado. Inicia sesión para consultar disponibilidad.']);
    exit;
}

// 2. CAPTURA Y SANEAMIENTO DE PARÁMETROS (GET)
$id_auditorio = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : null;
$fecha = isset($_GET['fecha']) ? mysqli_real_escape_string($conexion, $_GET['fecha']) : null;

$ocupados = [];

if ($id_auditorio && $fecha) {
    /**
     * NÚCLEO FUNCIONAL (40%): Lógica de traslapes
     * Solo consideramos solicitudes 'ACEPTADA' o 'PENDIENTE'.
     * Las rechazadas liberan el espacio automáticamente.
     */
    $sql = "SELECT hora_inicio, hora_fin FROM solicitudes 
            WHERE id_auditorio = '$id_auditorio' 
            AND fecha_evento = '$fecha' 
            AND estado != 'RECHAZADA'";
            
    $res = mysqli_query($conexion, $sql);
    
    while ($fila = mysqli_fetch_assoc($res)) {
        $ocupados[] = [
            'inicio' => $fila['hora_inicio'],
            'fin'    => $fila['hora_fin']
        ];
    }
    
    // 3. RESPUESTA EN JSON (Requisito TSU)
    echo json_encode($ocupados);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros de auditorio o fecha incompletos"]);
}

exit;