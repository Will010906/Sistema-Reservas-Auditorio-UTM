<?php
// Limpiamos cualquier salida previa para asegurar que solo salga JSON
ob_clean(); 
header('Content-Type: application/json');

// 1. Ruta corregida hacia la conexión
require_once "../../config/db_local.php"; 

if (!isset($conexion)) {
    echo json_encode(['error' => 'Error: No se pudo conectar a la base de datos UTM.']);
    exit;
}

// 2. Validación de Token JWT
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida.']);
    exit;
}

// 3. Captura de parámetros (Añadimos id_excluir)
$id_auditorio = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : null;
$fecha        = isset($_GET['fecha']) ? mysqli_real_escape_string($conexion, $_GET['fecha']) : null;
$id_excluir   = isset($_GET['id_excluir']) ? mysqli_real_escape_string($conexion, $_GET['id_excluir']) : null;

$ocupados = [];

if ($id_auditorio && $fecha) {
    // --- CONSULTA OPTIMIZADA CON EXCLUSIÓN ---
    // Si estamos editando, ignoramos la solicitud actual para que sus horas aparezcan LIBRES
    $sql = "SELECT hora_inicio, hora_fin FROM solicitudes 
            WHERE id_auditorio = '$id_auditorio' 
            AND fecha_evento = '$fecha' 
            AND estado != 'RECHAZADA'";
            
    if ($id_excluir) {
        $sql .= " AND id_solicitud != '$id_excluir'";
    }
    // -----------------------------------------
            
    $res = mysqli_query($conexion, $sql);
    
    if (!$res) {
        echo json_encode(['error' => mysqli_error($conexion)]);
        exit;
    }

    while ($fila = mysqli_fetch_assoc($res)) {
        $ocupados[] = [
            'inicio' => substr($fila['hora_inicio'], 0, 5), // HH:MM
            'fin'    => substr($fila['hora_fin'], 0, 5)
        ];
    }
    echo json_encode($ocupados);
} else {
    echo json_encode([]); 
}
exit;