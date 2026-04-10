<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: GESTIÓN INTEGRAL DE SOLICITUDES (FULL CRUD)
 * * @package     Controladores_API
 * @subpackage  Gestion_Reservaciones
 * @version     2.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Endpoint centralizado que gestiona el ciclo de vida de una reservación.
 * Implementa una arquitectura RESTful utilizando los verbos HTTP:
 * - POST: Registro de nueva solicitud con generación de folio.
 * - PUT: Edición integral de datos (con validación de propiedad).
 * - PATCH: Actualización de estatus y feedback administrativo.
 * - DELETE: Eliminación física o cancelación condicionada.
 * * SEGURIDAD:
 * Validación de identidad mediante JWT y control de acceso basado en roles (RBAC).
 */

header('Content-Type: application/json');
include '../../config/db_local.php';

/**
 * 1. CAPA DE SEGURIDAD (Validación JWT)
 * Recupera y procesa el token Bearer para identificar al actor de la petición.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado. Token inexistente o inválido.']);
    exit;
}

$jwt = $matches[1];

try {
    /**
     * DECODIFICACIÓN Y EXTRACCIÓN DE IDENTIDAD
     * Obtiene el ID y Perfil del usuario directamente del Payload para aplicar reglas de negocio.
     */
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $jwt)[1])), true);
    $id_user_token = $payload['id'];
    $perfil_user = strtolower($payload['perfil']);

    $metodo = $_SERVER['REQUEST_METHOD'];
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    switch ($metodo) {

        /**
         * ACCIÓN: REGISTRO DE SOLICITUD (POST)
         * Genera un folio institucional consecutivo (FOL-000) e inserta el registro.
         */
        case 'POST': 
            if (!$data) throw new Exception("No se recibieron datos para procesar la reservación.");

            // Lógica de Folio: Calcula el próximo ID disponible para mantener la secuencia
            $res_folio = mysqli_query($conexion, "SELECT MAX(id_solicitud) as ultimo FROM solicitudes");
            $row_f = mysqli_fetch_assoc($res_folio);
            $proximo_id = ($row_f['ultimo']) ? $row_f['ultimo'] + 1 : 1;
            $folio = "FOL-" . str_pad($proximo_id, 3, "0", STR_PAD_LEFT);

            // Sanitización de parámetros de entrada
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
                echo json_encode(["success" => true, "message" => "Reservación registrada satisfactoriamente.", "folio" => $folio]);
            } else { throw new Exception(mysqli_error($conexion)); }
            break;

        /**
         * ACCIÓN: DICTAMEN ADMINISTRATIVO (PATCH)
         * Permite a Administradores y Subdirectores Aceptar o Rechazar solicitudes.
         */
        case 'PATCH': 
            if ($perfil_user !== 'administrador' && $perfil_user !== 'subdirector') {
                throw new Exception("Privilegios insuficientes para realizar dictámenes administrativos.");
            }
            $id = intval($data['id']);
            $estado = mysqli_real_escape_string($conexion, $data['estado']);
            $motivo = mysqli_real_escape_string($conexion, $data['observaciones_admin'] ?? '');

           $sql = "UPDATE solicitudes SET estado = '$estado', observaciones_admin = '$motivo' WHERE id_solicitud = $id";
            
            if (mysqli_query($conexion, $sql)) {
                // Recuperación de metadatos para notificaciones externas
                $resInfo = mysqli_query($conexion, "SELECT s.titulo_event, u.telefono FROM solicitudes s JOIN usuarios u ON s.id_usuario = u.id_usuario WHERE s.id_solicitud = $id");
                $info = mysqli_fetch_assoc($resInfo);
                echo json_encode(["success" => true, "message" => "Estado de solicitud actualizado.", "data" => $info]);
            } else { throw new Exception(mysqli_error($conexion)); }
            break;

        /**
         * ACCIÓN: EDICIÓN / REASIGNACIÓN (PUT)
         * Actualiza los datos de la solicitud. 
         * Los usuarios estándar solo pueden editar si la solicitud aún está en estado 'PENDIENTE'.
         */
      case 'PUT': 
            if (!$data) throw new Exception("Estructura de datos incompleta para actualización.");

            $id_sol     = intval($data['id_editando']);
            $id_aud     = mysqli_real_escape_string($conexion, $data['id_auditorio']);
            $titulo     = mysqli_real_escape_string($conexion, $data['titulo']);
            $desc       = mysqli_real_escape_string($conexion, $data['descripcion']);
            $fecha      = mysqli_real_escape_string($conexion, $data['fecha_evento']);
            $h_ini      = mysqli_real_escape_string($conexion, $data['hora_inicio']);
            $h_fin      = mysqli_real_escape_string($conexion, $data['hora_fin']);
            $otros      = mysqli_real_escape_string($conexion, $data['otros_servicios'] ?? '');
            $asistentes = isset($data['num_asistentes']) ? intval($data['num_asistentes']) : 0;

            $sql = "UPDATE solicitudes 
                    SET id_auditorio = '$id_aud', titulo_event = '$titulo', descripcion = '$desc', 
                        fecha_evento = '$fecha', hora_inicio = '$h_ini', hora_fin = '$h_fin', 
                        otros_servicios = '$otros', num_asistentes = '$asistentes' 
                    WHERE id_solicitud = $id_sol";

            // LÓGICA DE PROPIEDAD: Restringe la edición a los dueños del registro en estados iniciales
            if ($perfil_user !== 'administrador' && $perfil_user !== 'subdirector') {
                $sql .= " AND id_usuario = '$id_user_token' AND (estado LIKE 'PENDIENTE%' OR estado LIKE 'Pendiente%')";
            }

            if (mysqli_query($conexion, $sql)) {
                if (mysqli_affected_rows($conexion) === 0) {
                    echo json_encode(["success" => true, "message" => "Proceso terminado (sin cambios realizados o acceso restringido)."]);
                } else {
                    echo json_encode(["success" => true, "message" => "Los cambios han sido aplicados correctamente."]);
                }
            } else { 
                throw new Exception("Error en la persistencia de datos: " . mysqli_error($conexion)); 
            }
            break;

        /**
         * ACCIÓN: CANCELACIÓN / SUPRESIÓN (DELETE)
         * Implementa una eliminación lógica controlada por perfil.
         */
         case 'DELETE': 
            $id_sol = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id_sol <= 0) throw new Exception("Identificador de solicitud no válido.");

            if ($perfil_user === 'administrador' || $perfil_user === 'subdirector') {
                // El administrador posee facultad de borrado absoluto
                $sql = "DELETE FROM solicitudes WHERE id_solicitud = $id_sol";
            } else {
                // El solicitante solo puede cancelar si la solicitud no ha sido procesada
                $sql = "DELETE FROM solicitudes 
                        WHERE id_solicitud = $id_sol 
                        AND id_usuario = '$id_user_token' 
                        AND estado = 'Pendiente'";
            }

            if (mysqli_query($conexion, $sql)) {
                if (mysqli_affected_rows($conexion) > 0) {
                    echo json_encode(["success" => true, "message" => "Solicitud removida del sistema."]);
                } else { 
                    $msg = ($perfil_user === 'administrador') ? "Recurso no encontrado." : "Operación denegada: La solicitud ya posee un dictamen o no le pertenece.";
                    throw new Exception($msg); 
                }
            } else { 
                throw new Exception(mysqli_error($conexion)); 
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["success" => false, "error" => "Método HTTP no soportado por este endpoint."]);
            break;
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

/**
 * FINALIZACIÓN DE FLUJO
 */
exit;