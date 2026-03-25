<?php
header('Content-Type: application/json');
require_once "../../config/db_local.php";

// 1. Extraer ID del usuario desde el Token JWT
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['success' => false, 'error' => 'Token no detectado']);
    exit;
}

$payload = json_decode(base64_decode(explode('.', $matches[1])[1]), true);
$id_usuario_real = $payload['id']; 

// 2. Recibir datos del modal vía JSON
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // Generación de Folio dinámico
    $folio = "UTM-" . date('Ymd') . "-" . rand(100, 999);
    
    // Saneamiento de datos (Evitar Inyección SQL)
    $id_aud  = mysqli_real_escape_string($conexion, $data['id_auditorio']);
    $tit     = mysqli_real_escape_string($conexion, $data['titulo']);
    $desc    = mysqli_real_escape_string($conexion, $data['descripcion']);
    $fec     = mysqli_real_escape_string($conexion, $data['fecha_evento']);
    $ini     = mysqli_real_escape_string($conexion, $data['hora_inicio']);
    $fin     = mysqli_real_escape_string($conexion, $data['hora_fin']);
    $otros   = mysqli_real_escape_string($conexion, $data['otros_servicios'] ?? '');
    
    /**
     * AJUSTE SEGÚN TU DER
     * Agregamos prioridad por defecto y num_asistentes (puedes capturarlos luego en el modal)
     */
    $prioridad = 'Con tiempo'; 
    $asistentes = 10; // Valor temporal, idealmente viene del modal

    // 3. Inserción Completa
    $sql = "INSERT INTO solicitudes (
                folio, id_usuario, id_auditorio, titulo_event, 
                descripcion, otros_servicios, fecha_evento, 
                hora_inicio, hora_fin, num_asistentes, prioridad, estado
            ) VALUES (
                '$folio', '$id_usuario_real', '$id_aud', '$tit', 
                '$desc', '$otros', '$fec', 
                '$ini', '$fin', '$asistentes', '$prioridad', 'Pendiente'
            )";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true, 'folio' => $folio]);
    } else {
        // Si falla, te dirá exactamente qué columna falta o está mal
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
}
exit;