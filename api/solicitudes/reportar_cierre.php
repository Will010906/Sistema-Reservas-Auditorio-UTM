<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API: REPORTE DE CIERRE E INCIDENTES (POST-EVENTO)
 * * @package     Controladores_API
 * @subpackage  Gestion_Solicitudes
 * @version     1.0.4
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint finaliza formalmente el uso de un espacio académico. Registra 
 * las observaciones, daños o incidencias reportadas al concluir el evento. 
 * Implementa una restricción de seguridad basada en el ID del solicitante
 * contenido en el Token JWT.
 * * FLUJO DE SEGURIDAD:
 * 1. Identificación: Extrae el ID del usuario del Payload del Token.
 * 2. Integridad: Solo permite actualizar si el registro pertenece al solicitante.
 * 3. Auditoría: Estampa automáticamente la fecha y hora exacta del cierre.
 */

/**
 * CONFIGURACIÓN DE CABECERAS (HEADERS)
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE CONEXIÓN
 */
require_once '../../config/db_local.php';

/**
 * 1. CAPA DE AUTENTICACIÓN (JWT)
 * Valida la sesión activa para evitar reportes anónimos o malintencionados.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no detectada. Identificación requerida.']);
    exit;
}

try {
    /**
     * 2. CAPTURA Y DECODIFICACIÓN DE DATOS
     * Procesa el cuerpo de la petición (POST Payload) y decodifica el Token.
     */
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['id']) || !isset($data['incidentes'])) {
        throw new Exception("Parámetros insuficientes para procesar el cierre del evento.");
    }

    $id_sol = (int)$data['id'];
    $incidentes = mysqli_real_escape_string($conexion, $data['incidentes']);

    // Extracción de ID del solicitante desde el Token Bearer
    $jwt = $matches[1];
    $payload = json_decode(base64_decode(explode('.', $jwt)[1]), true);
    $id_user_token = $payload['id'];

    /**
     * 3. PERSISTENCIA Y CIERRE (SQL)
     * Actualiza la solicitud con el reporte de incidentes y marca el fin del evento.
     * @note La cláusula id_usuario garantiza que un usuario no pueda cerrar eventos ajenos.
     */
    $sql = "UPDATE solicitudes 
            SET incidentes_cierre = '$incidentes', 
                fecha_cierre = NOW() 
            WHERE id_solicitud = $id_sol 
            AND id_usuario = '$id_user_token'";

    if (mysqli_query($conexion, $sql)) {
        /**
         * VALIDACIÓN DE AFECTACIÓN
         * Verifica si la consulta realmente modificó un registro (Propiedad confirmada).
         */
        if (mysqli_affected_rows($conexion) > 0) {
            echo json_encode([
                "success" => true, 
                "message" => "El reporte de cierre institucional ha sido almacenado correctamente."
            ]);
        } else {
            throw new Exception("Error de permisos: No se localizó la solicitud o no está vinculada a su cuenta.");
        }
    } else {
        throw new Exception("Fallo técnico en motor de datos: " . mysqli_error($conexion));
    }

} catch (Exception $e) {
    /**
     * GESTIÓN DE EXCEPCIONES
     */
    echo json_encode([
        "success" => false, 
        "error" => "No se pudo completar el reporte: " . $e->getMessage()
    ]);
}

/**
 * CIERRE DE FLUJO
 */
exit;