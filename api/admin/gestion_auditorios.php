<?php
ob_start();
header('Content-Type: application/json');
include '../../config/db_local.php';
ob_clean();

$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || strpos($auth, 'Bearer ') === false) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($metodo) {
        case 'POST':
            $id = !empty($_POST['id_auditorio']) ? intval($_POST['id_auditorio']) : null;
            $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
            $ubicacion = mysqli_real_escape_string($conexion, $_POST['ubicacion']);
            $capacidad = intval($_POST['capacidad']);
            $equipamiento = mysqli_real_escape_string($conexion, $_POST['equipamiento']);

            if ($id) {
                $sql = "UPDATE auditorio SET nombre_espacio='$nombre', ubicacion='$ubicacion', 
                        capacidad_maxima=$capacidad, equipamiento_fijo='$equipamiento' WHERE id_auditorio=$id";
            } else {
                // USANDO: disponibilidad (según tu phpMyAdmin)
                $sql = "INSERT INTO auditorio (nombre_espacio, ubicacion, capacidad_maxima, equipamiento_fijo, disponibilidad) 
                        VALUES ('$nombre', '$ubicacion', $capacidad, '$equipamiento', 1)";
            }

            if (mysqli_query($conexion, $sql)) {
                $target_id = $id ? $id : mysqli_insert_id($conexion);
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                    $dir = "../../assets/img/auditorios/";
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $target_id . ".jpg");
                }
                echo json_encode(["success" => true, "message" => "Operación exitosa"]);
            } else { throw new Exception(mysqli_error($conexion)); }
            break;

        case 'PATCH':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id']);
            $st = intval($data['estado']);
            
            // CORREGIDO: Usando 'disponibilidad' en lugar de 'disponible'
            $sql = "UPDATE auditorio SET disponibilidad = $st WHERE id_auditorio = $id";
            
            if (mysqli_query($conexion, $sql)) echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
            else throw new Exception(mysqli_error($conexion));
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id']);
            $foto = "../../assets/img/auditorios/$id.jpg";
            if (file_exists($foto)) unlink($foto);
            
            $sql = "DELETE FROM auditorio WHERE id_auditorio = $id";
            if (mysqli_query($conexion, $sql)) echo json_encode(['success' => true, 'message' => 'Eliminado']);
            else throw new Exception(mysqli_error($conexion));
            break;
            
        default:
            throw new Exception("Método $metodo no soportado");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
ob_end_flush();