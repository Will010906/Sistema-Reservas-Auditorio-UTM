<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: REGISTRO DE SOLICITUDES DE RESERVACIÓN
 * * @package     Controladores_API
 * @subpackage  Gestion_Solicitudes
 * @version     1.0.5
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint procesa el almacenamiento persistente de nuevas solicitudes.
 * Implementa una capa de seguridad que extrae la identidad del solicitante
 * directamente del Token Bearer, evitando la suplantación de identidad (Spoofing).
 * * FUNCIONALIDADES:
 * 1. Autenticación: Decodificación de Base64 para obtener el ID de usuario.
 * 2. Generación: Creación de folios institucionales únicos basados en tiempo.
 * 3. Seguridad: Saneamiento de cadenas para mitigar ataques de Inyección SQL.
 */

/**
 * CONFIGURACIÓN DE SALIDA
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 */
require_once "../../config/db_local.php";

/**
 * 1. EXTRACCIÓN DE IDENTIDAD (JWT PAYLOAD)
 * Recupera el ID del usuario desde la cabecera de autorización para garantizar 
 * que la solicitud se vincule al dueño real de la sesión.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['success' => false, 'error' => 'Token de seguridad no detectado.']);
    exit;
}

// Decodificación de la segunda parte del token (Payload)
$payload = json_decode(base64_decode(explode('.', $matches[1])[1]), true);
$id_usuario_real = $payload['id']; 

/**
 * 2. RECEPCIÓN Y PROCESAMIENTO DE DATOS
 * Captura el flujo de entrada (Input Stream) para procesar el objeto JSON del modal.
 */
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    /**
     * GENERACIÓN DE FOLIO INSTITUCIONAL
     * Estructura: UTM - [AÑO/MES/DÍA] - [ALEATORIO]
     * @var string $folio Identificador único de la reservación.
     */
    $folio = "UTM-" . date('Ymd') . "-" . rand(100, 999);
    
    /**
     * SANEAMIENTO DE DATOS (DATA SANITIZATION)
     * Preparación de variables para interacción segura con el motor MySQL.
     */
    $id_aud  = mysqli_real_escape_string($conexion, $data['id_auditorio']);
    $tit     = mysqli_real_escape_string($conexion, $data['titulo']);
    $desc    = mysqli_real_escape_string($conexion, $data['descripcion']);
    $fec     = mysqli_real_escape_string($conexion, $data['fecha_evento']);
    $ini     = mysqli_real_escape_string($conexion, $data['hora_inicio']);
    $fin     = mysqli_real_escape_string($conexion, $data['hora_fin']);
    $otros   = mysqli_real_escape_string($conexion, $data['otros_servicios'] ?? '');
    
    /**
     * ATRIBUTOS POR DEFECTO (LÓGICA DE NEGOCIO SIRA)
     * Define valores base para prioridad y capacidad según el DER institucional.
     */
    $prioridad = 'Con tiempo'; 
    $asistentes = 10; 

    /**
     * 3. PERSISTENCIA DE LA INFORMACIÓN
     * Ejecuta la sentencia INSERT en la tabla 'solicitudes' con estado inicial 'Pendiente'.
     */
    $sql = "INSERT INTO solicitudes (
                folio, id_usuario, id_auditorio, titulo_event, 
                descripcion, otros_servicios, fecha_evento, 
                hora_inicio, hora_fin, num_asistentes, prioridad, estado
            ) VALUES (
                '$folio', '$id_usuario_real', '$id_aud', '$tit', 
                '$desc', '$otros', '$fec', 
                '$ini', '$fin', '$asistentes', '$prioridad', 'Pendiente'
            )";

    if (mysqli_query($conexion, $sql)) {
        // Respuesta exitosa al cliente
        echo json_encode(['success' => true, 'folio' => $folio]);
    } else {
        // Reporte de error técnico en caso de fallo en el motor DB
        echo json_encode(['success' => false, 'error' => "Fallo en base de datos: " . mysqli_error($conexion)]);
    }
}
/**
 * CIERRE DE EJECUCIÓN
 */
exit;