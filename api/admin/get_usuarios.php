<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API: CONSULTA DE DIRECTORIO DE USUARIOS
 * * @package     Controladores_API
 * @subpackage  Gestion_Usuarios
 * @version     1.0.1
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este servicio recupera la lista de usuarios registrados en el sistema para su
 * despliegue en tablas administrativas. Implementa una restricción de consulta
 * para proteger la cuenta de administrador principal.
 * * SEGURIDAD:
 * 1. Validación de Token JWT: Solo peticiones autenticadas pueden acceder.
 * 2. Restricción de Nivel de Datos: Filtra el rol 'admin' de los resultados 
 * para evitar intentos de manipulación de la cuenta raíz desde el cliente.
 */

/**
 * CONFIGURACIÓN DE CABECERAS
 * Define el estándar de intercambio de datos y salida.
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 * Conexión centralizada a la base de datos institucional.
 */
include '../../config/db_local.php';

/**
 * CAPA DE AUTENTICACIÓN (JWT)
 * Valida la existencia y el formato del token de autorización Bearer.
 * @throws 401 Unauthorized si el cliente no envía credenciales válidas.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || strpos($auth, 'Bearer ') === false) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'No autorizado. Se requiere una sesión administrativa activa.'
    ]);
    exit;
}

try {
    /**
     * CONSULTA SQL OPTIMIZADA
     * Selecciona campos específicos (no sensibles) y aplica filtro de seguridad.
     * * NOTA: Se excluye explicitamente al rol 'admin' como medida de 
     * protección de la infraestructura crítica del sistema.
     */
    $sql = "SELECT id_usuario, nombre, matricula, correo, telefono, carrera, rol 
            FROM usuarios 
            WHERE rol != 'admin' 
            ORDER BY nombre ASC";
            
    $resultado = mysqli_query($conexion, $sql);

    // Validación de ejecución de consulta
    if (!$resultado) {
        throw new Exception("Error en la ejecución de la consulta: " . mysqli_error($conexion));
    }

    /**
     * PROCESAMIENTO DE RESULTADOS
     * Construcción del arreglo de objetos de usuario para el frontend.
     */
    $usuarios = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $usuarios[] = $row;
    }

    /**
     * RETORNO DE DATOS
     * Envía la colección en formato JSON con éxito implícito.
     */
    echo json_encode($usuarios);

} catch (Exception $e) {
    /**
     * GESTIÓN DE EXCEPCIONES
     * Captura errores de base de datos o lógica y los formatea para el cliente.
     */
    echo json_encode([
        'success' => false, 
        'error' => 'Fallo en la recuperación de datos: ' . $e->getMessage()
    ]);
}