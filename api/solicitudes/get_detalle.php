<?php
header('Content-Type: application/json');
include("../../config/db_local.php");

$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// 1. Decodificación del Payload para identificar el PERFIL
$jwt = $matches[1];
$payload = json_decode(base64_decode(explode('.', $jwt)[1]), true);
$id_user_token = (int)$payload['id'];
$perfil_token = strtolower($payload['perfil'] ?? 'alumno'); // Extraemos el perfil

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_auditorio = isset($_GET['id_auditorio']) ? intval($_GET['id_auditorio']) : 0;

if ($id_auditorio > 0) {
    $query = "SELECT * FROM auditorio WHERE id_auditorio = $id_auditorio";
    $res = mysqli_query($conexion, $query);
    echo json_encode(mysqli_fetch_assoc($res));
    exit;
}

if ($id > 0) {
    // 2. Consulta Base con todos los JOINs necesarios
  $query = "SELECT s.*, 
                     u.nombre as nombre_usuario, u.telefono, u.perfil, 
                     a.nombre_espacio, a.capacidad_maxima, a.equipamiento_fijo,
                     s.otros_servicios as extras_texto,
                     s.observaciones_admin AS notas_admin -- <--- AGREGAMOS ESTA COLUMNA
              FROM solicitudes s
              JOIN usuarios u ON s.id_usuario = u.id_usuario
              JOIN auditorio a ON s.id_auditorio = a.id_auditorio
              LEFT JOIN detalle_equipamiento de ON s.id_solicitud = de.id_solicitud
              LEFT JOIN equipamiento e ON de.id_equipamiento = e.id_equipamiento
              WHERE s.id_solicitud = $id";

    // 3. SEGURIDAD DINÁMICA: Si NO es administrador, aplicar filtro de dueño
    if ($perfil_token !== 'administrador' && $perfil_token !== 'admin') {
        $query .= " AND s.id_usuario = $id_user_token";
    }

    $query .= " GROUP BY s.id_solicitud";

    $res = mysqli_query($conexion, $query);
    
    if ($res && mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        
        // NORMALIZACIÓN PARA EL JS (Evita el 'undefined')
        $data['estado'] = trim($data['estado']); 
        $data['fecha_evento_limpia'] = date('d/m/Y', strtotime($data['fecha_evento']));
        $data['incidentes_cierre'] = (!empty($data['incidentes_cierre'])) ? $data['incidentes_cierre'] : null;
        $data['folio'] = $data['folio'] ?? 'N/A'; // Asegura que el folio no sea N/A si existe en DB
$data['id_editando'] = $data['id_solicitud']; // Respaldo explícito del ID
        echo json_encode($data);
    } else {
        http_response_code(403);
        echo json_encode(["error" => "No se encontró la solicitud o no tienes permisos de acceso"]);
    }
} else {
    echo json_encode(["error" => "ID no válido"]);
}
exit;