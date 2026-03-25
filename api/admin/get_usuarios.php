<?php
header('Content-Type: application/json');
include '../../config/db_local.php';

$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || strpos($auth, 'Bearer ') === false) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    // Obtenemos todos los usuarios excepto el admin principal para mayor seguridad
    $sql = "SELECT id_usuario, nombre, matricula, correo, telefono, carrera, rol FROM usuarios WHERE rol != 'admin' ORDER BY nombre ASC";
    $resultado = mysqli_query($conexion, $sql);

    $usuarios = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $usuarios[] = $row;
    }

    echo json_encode($usuarios);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}