<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: CONSULTA DETALLADA DE RESERVACIONES Y ESPACIOS
 * * @package     Controladores_API
 * @subpackage  Gestion_Solicitudes
 * @author      Wilmer (Estudiante de Tecnologías de la Información, UTM)
 * @version     1.2.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint proporciona información atómica y detallada de una solicitud 
 * específica o de un auditorio. Implementa un modelo de seguridad robusto 
 * basado en Roles (RBAC) y pertenencia de área académica.
 * * SEGURIDAD Y PRIVACIDAD:
 * 1. Autenticación: Obligatoriedad de Token JWT.
 * 2. Restricción Jerárquica: 
 * - Administradores: Acceso total.
 * - Subdirectores: Acceso limitado por 'area_token' (Carrera/Área).
 * - Usuarios estándar: Acceso exclusivo a registros propios mediante 'id_user_token'.
 */

// Desactivación de reporte de errores nativos para evitar fugas de información de la estructura DB
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include("../../config/db_local.php");

/**
 * 1. CAPA DE SEGURIDAD (JWT)
 * Recupera e inspecciona el Token Bearer para identificar al actor de la petición.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Acceso denegado: Token de seguridad inexistente o inválido.']);
    exit;
}

// Extracción y decodificación del Payload JWT
$jwt = $matches[1];
$payload = json_decode(base64_decode(explode('.', $jwt)[1]), true);
$id_user_token = (int)$payload['id'];
$perfil_token = strtolower($payload['perfil'] ?? 'alumno'); 
$area_token = $payload['area'] ?? ''; // Atributo para validación de carrera (Subdirección)

/**
 * 2. PROCESAMIENTO DE PARÁMETROS (GET)
 */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_auditorio = isset($_GET['id_auditorio']) ? intval($_GET['id_auditorio']) : 0;

/**
 * ACCIÓN A: CONSULTA DE ESPECIFICACIONES DE AUDITORIO
 * Se utiliza principalmente para el llenado dinámico de modales de edición.
 */
if ($id_auditorio > 0) {
    $query = "SELECT id_auditorio, nombre_espacio, capacidad_maxima, equipamiento_fijo 
              FROM auditorio WHERE id_auditorio = $id_auditorio";
    $res = mysqli_query($conexion, $query);
    echo json_encode(mysqli_fetch_assoc($res));
    exit;
}

/**
 * ACCIÓN B: CONSULTA INTEGRAL DE SOLICITUD
 * Ejecuta una triple unión (JOIN) para consolidar datos del solicitante, el espacio y la reservación.
 */
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

    /**
     * MOTOR DE SEGURIDAD BASADO EN PERFIL
     * Implementa la lógica de filtrado de privacidad en el servidor.
     */
    if ($perfil_token === 'subdirector') {
        // Restricción horizontal: El subdirector solo accede a solicitudes de su área académica.
        $query .= " AND u.carrera_area = '$area_token'";
    } elseif ($perfil_token !== 'administrador' && $perfil_token !== 'admin') {
        // Restricción por propiedad: Alumnos y docentes solo acceden a sus propios registros.
        $query .= " AND s.id_usuario = $id_user_token";
    }

    $res = mysqli_query($conexion, $query);

    if ($res && mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        
        // Formateo y saneamiento de datos para el Frontend
        $data['estado'] = trim($data['estado']);
        $data['fecha_evento_limpia'] = date('d/m/Y', strtotime($data['fecha_evento']));
        $data['id_editando'] = $data['id_solicitud'];

        echo json_encode($data);
    } else {
        // Respuesta ante intentos de acceso no autorizado o IDs inexistentes
        http_response_code(403);
        echo json_encode(["error" => "Privilegios insuficientes para visualizar este recurso o no existe."]);
    }
} else {
    echo json_encode(["error" => "Identificador de recurso no válido."]);
}

/**
 * CIERRE DE FLUJO
 */
exit;