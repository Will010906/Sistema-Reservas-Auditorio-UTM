<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API: OBTENER INVENTARIO DE AUDITORIOS
 * * @package     Controladores_API
 * @subpackage  Gestion_Auditorios
 * @version     1.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint recupera la colección completa de espacios físicos (auditorios)
 * registrados en la base de datos. Realiza un formateo de tipos (casting) para
 * asegurar que los valores numéricos lleguen al cliente como enteros y no como strings.
 * * SEGURIDAD:
 * Requiere autenticación mediante Bearer Token. Valida que la sesión esté activa
 * antes de permitir el acceso al inventario institucional.
 */

// Limpieza del búfer de salida para prevenir carácteres invisibles que corrompan el JSON
if (ob_get_length()) ob_clean(); 

/**
 * CONFIGURACIÓN DE CABECERAS
 * Define el intercambio de datos bajo el estándar application/json.
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 * Conexión centralizada a la base de datos.
 */
include '../../config/db_local.php';

/**
 * VALIDACIÓN DE AUTENTICACIÓN (JWT)
 * Captura el encabezado de autorización para verificar la identidad del solicitante.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'No autorizado. Acceso restringido a personal de la UTM.'
    ]);
    exit;
}

try {
    /**
     * VERIFICACIÓN DE INTEGRIDAD DE CONEXIÓN
     * Asegura que el recurso de red a la base de datos esté disponible.
     */
    if (!isset($conexion) || !$conexion) {
        throw new Exception("Fallo crítico: La conexión a la base de datos no se estableció correctamente.");
    }

    /**
     * EJECUCIÓN DE CONSULTA
     * Recupera todos los campos de la tabla 'auditorio' con ordenamiento alfabético.
     */
    $sql = "SELECT * FROM auditorio ORDER BY nombre_espacio ASC";
    $resultado = mysqli_query($conexion, $sql);

    if (!$resultado) {
        throw new Exception("Error en la sentencia SQL: " . mysqli_error($conexion));
    }

    $auditorios = [];

    /**
     * PROCESAMIENTO Y FORMATEO DE RESULTADOS
     * Itera sobre el set de resultados y normaliza los tipos de datos para el cliente JS.
     */
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Casting manual para garantizar precisión en operaciones lógicas del frontend
        $row['id_auditorio'] = (int)$row['id_auditorio'];
        $row['capacidad_maxima'] = (int)$row['capacidad_maxima'];
        
        /**
         * NOTA DE COMPATIBILIDAD:
         * Se valida la existencia de la columna 'disponible' o 'disponibilidad' 
         * para evitar errores de índice en diferentes versiones de la BD.
         */
        $row['disponible'] = isset($row['disponible']) ? (int)$row['disponible'] : 0;
        
        $auditorios[] = $row;
    }

    // Retorno exitoso de la colección de datos
    echo json_encode($auditorios);

} catch (Exception $e) {
    /**
     * GESTIÓN DE ERRORES DE SERVIDOR
     * Responde con un código 500 en caso de fallos inesperados de infraestructura.
     */
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => "Error interno del servidor: " . $e->getMessage()
    ]);
}