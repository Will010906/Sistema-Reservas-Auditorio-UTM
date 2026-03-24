<?php
/**
 * API: GESTIÓN INTEGRAL DE SOLICITUDES - SIRA UTM
 * Centraliza: Creación (POST), Edición (PUT), Cambio de Estatus (PATCH) y Cancelación (DELETE).
 */
header('Content-Type: application/json');
include '../../config/db_local.php';

// 1. VALIDACIÓN DE SEGURIDAD (30% JWT)
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado. Token faltante.']);
    exit;
}

$jwt = $matches[1];

try {
    // Decodificamos el Token para identificar al usuario y su perfil
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $jwt)[1])), true);
    $id_user_token = $payload['id'];
    $perfil_user = strtolower($payload['perfil']);

    $metodo = $_SERVER['REQUEST_METHOD'];
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    switch ($metodo) {

        case 'POST': // --- CREAR NUEVA RESERVACIÓN ---
            if (!$data) throw new Exception("No se recibieron datos.");

            // Generación de Folio Consecutivo
            $res_folio = mysqli_query($conexion, "SELECT MAX(id_solicitud) as ultimo FROM solicitudes");
            $row_f = mysqli_fetch_assoc($res_folio);
            $proximo_id = ($row_f['ultimo']) ? $row_f['ultimo'] + 1 : 1;
            $folio = "FOL-" . str_pad($proximo_id, 3, "0", STR_PAD_LEFT);

            $id_aud = mysqli_real_escape_string($conexion, $data['id_auditorio']);
            $titulo = mysqli_real_escape_string($conexion, $data['titulo']);
            $desc   = mysqli_real_escape_string($conexion, $data['descripcion']);
            $fecha  = mysqli_real_escape_string($conexion, $data['fecha_evento']);
            $h_ini  = mysqli_real_escape_string($conexion, $data['hora_inicio']);
            $h_fin  = mysqli_real_escape_string($conexion, $data['hora_fin']);
            $otros  = mysqli_real_escape_string($conexion, $data['otros_servicios'] ?? '');

            $sql = "INSERT INTO solicitudes (id_usuario, id_auditorio, folio, titulo_event, descripcion, fecha_evento, hora_inicio, hora_fin, otros_servicios, estado, fecha_registro) 
                    VALUES ('$id_user_token', '$id_aud', '$folio', '$titulo', '$desc', '$fecha', '$h_ini', '$h_fin', '$otros', 'PENDIENTE', NOW())";
            
            if (mysqli_query($conexion, $sql)) {
                echo json_encode(["success" => true, "message" => "Reservación creada.", "folio" => $folio]);
            } else { throw new Exception(mysqli_error($conexion)); }
            break;

        case 'PATCH': // --- ACTUALIZAR ESTADO (ADMIN) ---
            if ($perfil_user !== 'administrador' && $perfil_user !== 'subdirector') {
                throw new Exception("No tienes permisos para cambiar el estado.");
            }
            $id = intval($data['id']);
            $estado = mysqli_real_escape_string($conexion, $data['estado']);
            $motivo = mysqli_real_escape_string($conexion, $data['observaciones_admin'] ?? '');

            $sql = "UPDATE solicitudes SET estado = '$estado', observaciones_admin = '$motivo' WHERE id_solicitud = $id";
            
            if (mysqli_query($conexion, $sql)) {
                // Buscamos datos para WhatsApp
                $resInfo = mysqli_query($conexion, "SELECT s.titulo_event, u.telefono FROM solicitudes s JOIN usuarios u ON s.id_usuario = u.id_usuario WHERE s.id_solicitud = $id");
                $info = mysqli_fetch_assoc($resInfo);
                echo json_encode(["success" => true, "message" => "Estado actualizado", "data" => $info]);
            } else { throw new Exception(mysqli_error($conexion)); }
            break;

        case 'DELETE': // --- CANCELAR/ELIMINAR SOLICITUD ---
            $id_sol = isset($data['id']) ? intval($data['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
            
            if ($perfil_user === 'administrador') {
                $sql = "DELETE FROM solicitudes WHERE id_solicitud = $id_sol";
            } else {
                // Usuario solo borra la suya y si está PENDIENTE
                $sql = "DELETE FROM solicitudes WHERE id_solicitud = $id_sol AND id_usuario = '$id_user_token' AND estado = 'PENDIENTE'";
            }

            if (mysqli_query($conexion, $sql)) {
                if (mysqli_affected_rows($conexion) > 0) {
                    echo json_encode(["success" => true, "message" => "Solicitud eliminada."]);
                } else { throw new Exception("No tienes permiso o la solicitud ya no es editable."); }
            } else { throw new Exception(mysqli_error($conexion)); }
            break;

        default:
            http_response_code(405);
            echo json_encode(["success" => false, "error" => "Método no permitido"]);
            break;
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
exit;