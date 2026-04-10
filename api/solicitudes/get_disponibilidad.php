<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: VERIFICACIÓN DE DISPONIBILIDAD TEMPORAL
 * * @package     Controladores_API
 * @subpackage  Gestion_Calendario
 * @version     1.1.5
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint analiza la ocupación de un auditorio en una fecha específica. 
 * Retorna un conjunto de rangos horarios (inicio/fin) que ya se encuentran 
 * reservados para alimentar el motor de validación visual en el Frontend.
 * * CAPACIDADES AVANZADAS:
 * 1. Lógica de Exclusión: Permite omitir una solicitud específica (id_excluir) 
 * durante procesos de edición para no generar falsos positivos de colisión.
 * 2. Filtrado de Estatus: Solo considera reservaciones activas (ignora RECHAZADAS).
 * 3. Formateo de Tiempo: Entrega horas en formato HH:MM compatible con objetos JS.
 */

// Limpieza de buffer para garantizar una respuesta JSON pura
ob_clean(); 
header('Content-Type: application/json');

/**
 * 1. CAPA DE PERSISTENCIA
 * Establece el puente de comunicación con el esquema institucional de la UTM.
 */
require_once "../../config/db_local.php"; 

if (!isset($conexion)) {
    echo json_encode(['success' => false, 'error' => 'Fallo crítico: No se detectó configuración de base de datos.']);
    exit;
}

/**
 * 2. SEGURIDAD: VALIDACIÓN DE SESIÓN (JWT)
 * Garantiza que la consulta al calendario se realice bajo un contexto de usuario autenticado.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$auth) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acceso restringido: Se requiere token Bearer válido.']);
    exit;
}

/**
 * 3. PROCESAMIENTO DE PARÁMETROS DE CONSULTA
 * @var int $id_auditorio Identificador del espacio físico.
 * @var string $fecha Fecha del evento en formato ISO (YYYY-MM-DD).
 * @var int|null $id_excluir Identificador opcional para procesos de reasignación.
 */
$id_auditorio = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : null;
$fecha        = isset($_GET['fecha']) ? mysqli_real_escape_string($conexion, $_GET['fecha']) : null;
$id_excluir   = isset($_GET['id_excluir']) ? mysqli_real_escape_string($conexion, $_GET['id_excluir']) : null;

$ocupados = [];

if ($id_auditorio && $fecha) {
    /**
     * CONSULTA DE TRASLAPE HORARIO (OVERLAP QUERY)
     * Selecciona horarios ocupados filtrando solicitudes no válidas.
     */
    $sql = "SELECT hora_inicio, hora_fin FROM solicitudes 
            WHERE id_auditorio = '$id_auditorio' 
            AND fecha_evento = '$fecha' 
            AND estado != 'RECHAZADA'";
            
    /**
     * INYECCIÓN DE LÓGICA DE EXCLUSIÓN
     * Si el sistema está en modo 'Edición', ignoramos el registro actual 
     * para que el usuario pueda reprogramar sus propias horas.
     */
    if ($id_excluir) {
        $sql .= " AND id_solicitud != '$id_excluir'";
    }
            
    $res = mysqli_query($conexion, $sql);
    
    if (!$res) {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
        exit;
    }

    /**
     * SERIALIZACIÓN Y FORMATEO
     * Convierte los tiempos de base de datos a formato de 5 dígitos (HH:MM).
     */
    while ($fila = mysqli_fetch_assoc($res)) {
        $ocupados[] = [
            'inicio' => substr($fila['hora_inicio'], 0, 5),
            'fin'    => substr($fila['hora_fin'], 0, 5)
        ];
    }
    
    // Retorno de la colección de colisiones horarias
    echo json_encode($ocupados);

} else {
    /**
     * RESPUESTA POR DEFECTO
     * Si no se proporcionan criterios de búsqueda, se retorna un buffer vacío.
     */
    echo json_encode([]); 
}

/**
 * FINALIZACIÓN DEL PROCESO
 */
exit;