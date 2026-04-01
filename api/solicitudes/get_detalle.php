<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include("../../config/db_local.php");

$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$jwt = $matches[1];
$payload = json_decode(base64_decode(explode('.', $jwt)[1]), true);
$id_user_token = (int)$payload['id'];
$perfil_token = strtolower($payload['perfil'] ?? 'alumno'); 
$area_token = $payload['area'] ?? ''; // El área del subdirector

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_auditorio = isset($_GET['id_auditorio']) ? intval($_GET['id_auditorio']) : 0;

if ($id_auditorio > 0) {
    $query = "SELECT id_auditorio, nombre_espacio, capacidad_maxima, equipamiento_fijo 
              FROM auditorio WHERE id_auditorio = $id_auditorio";
    $res = mysqli_query($conexion, $query);
    echo json_encode(mysqli_fetch_assoc($res));
    exit;
}

if ($id > 0) {
    $query = "SELECT s.*, 
                    u.nombre as nombre_usuario, 
                    u.telefono, 
                    u.perfil, 
                    u.matricula, 
                    u.correo_electronico as correo, 
                    u.carrera_area as carrera, 
                    a.nombre_espacio, a.capacidad_maxima, a.equipamiento_fijo,
                    s.otros_servicios as extras_texto,
                    s.observaciones_admin AS notas_admin
              FROM solicitudes s
              JOIN usuarios u ON s.id_usuario = u.id_usuario
              JOIN auditorio a ON s.id_auditorio = a.id_auditorio
              WHERE s.id_solicitud = $id";

    // --- MEJORA DE SEGURIDAD PARA SUBDIRECTOR ---
    if ($perfil_token === 'subdirector') {
        // El subdirector solo puede ver si la solicitud es de su misma carrera
        $query .= " AND u.carrera_area = '$area_token'";
    } elseif ($perfil_token !== 'administrador' && $perfil_token !== 'admin') {
        // Alumnos y docentes solo ven las SUYAS
        $query .= " AND s.id_usuario = $id_user_token";
    }

    $res = mysqli_query($conexion, $query);

    if ($res && mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        $data['estado'] = trim($data['estado']);
        $data['fecha_evento_limpia'] = date('d/m/Y', strtotime($data['fecha_evento']));
        $data['id_editando'] = $data['id_solicitud'];

        echo json_encode($data);
    } else {
        // Si no encuentra nada o no pertenece a su área
        http_response_code(403);
        echo json_encode(["error" => "No tienes permiso para ver esta solicitud o no existe"]);
    }
} else {
    echo json_encode(["error" => "ID no válido"]);
}
exit;