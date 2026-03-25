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

        case 'POST': 
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
            $asistentes = isset($data['num_asistentes']) ? intval($data['num_asistentes']) : 0;

            $sql = "INSERT INTO solicitudes (id_usuario, id_auditorio, folio, titulo_event, descripcion, fecha_evento, hora_inicio, hora_fin, otros_servicios, num_asistentes, estado, fecha_registro) 
                    VALUES ('$id_user_token', '$id_aud', '$folio', '$titulo', '$desc', '$fecha', '$h_ini', '$h_fin', '$otros', '$asistentes', 'PENDIENTE', NOW())";
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

        case 'PUT': 
    if (!$data) throw new Exception("No hay datos para actualizar.");

    // Aseguramos que el ID se reciba del campo 'id_editando' que manda el JS
    $id_sol = intval($data['id_editando']);
    $titulo = mysqli_real_escape_string($conexion, $data['titulo']);
    $desc   = mysqli_real_escape_string($conexion, $data['descripcion']);
    $otros  = mysqli_real_escape_string($conexion, $data['otros_servicios'] ?? '');
    $asistentes = isset($data['num_asistentes']) ? intval($data['num_asistentes']) : 0;

    // Corregimos el WHERE: Quitamos la restricción de fecha para que te deje editar abril
  // En api/solicitudes/gestion_solicitudes.php
$sql = "UPDATE solicitudes 
                    SET titulo_event = '$titulo', descripcion = '$desc', otros_servicios = '$otros', num_asistentes = '$asistentes' 
                    WHERE id_solicitud = $id_sol 
                    AND id_usuario = '$id_user_token' 
                    AND (estado LIKE 'PENDIENTE%' OR estado LIKE 'Pendiente%')";

    if (mysqli_query($conexion, $sql)) {
        if (mysqli_affected_rows($conexion) > 0) {
            echo json_encode(["success" => true, "message" => "Cambios guardados."]);
        } else {
            // Si llega aquí, es porque el ID no existe o el estado cambió en la DB
            throw new Exception("No se realizaron cambios. Verifica que la solicitud siga PENDIENTE.");
        }
    } else { 
        throw new Exception(mysqli_error($conexion)); 
    }
    break;

     case 'DELETE': // --- CANCELAR/ELIMINAR SOLICITUD ---
            $id_sol = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id_sol <= 0) throw new Exception("ID de solicitud no válido.");

            // LÓGICA HÍBRIDA
            if ($perfil_user === 'administrador' || $perfil_user === 'subdirector') {
                // EL ADMIN BORRA LO QUE SEA
                $sql = "DELETE FROM solicitudes WHERE id_solicitud = $id_sol";
            } else {
                // EL USUARIO (DOCENTE/ALUMNO) SOLO BORRA LA SUYA Y SI ESTÁ PENDIENTE
                $sql = "DELETE FROM solicitudes 
                        WHERE id_solicitud = $id_sol 
                        AND id_usuario = '$id_user_token' 
                        AND estado = 'Pendiente'";
            }

            if (mysqli_query($conexion, $sql)) {
                if (mysqli_affected_rows($conexion) > 0) {
                    echo json_encode(["success" => true, "message" => "Solicitud eliminada."]);
                } else { 
                    // Mensaje personalizado según el perfil
                    $msg = ($perfil_user === 'administrador') ? "La solicitud no existe." : "No tienes permiso o la solicitud ya fue procesada.";
                    throw new Exception($msg); 
                }
            } else { 
                throw new Exception(mysqli_error($conexion)); 
            }
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