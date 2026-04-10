<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API: PROCESAMIENTO DE RESTABLECIMIENTO DE CONTRASEÑA
 * * @package     Controladores_API
 * @subpackage  Seguridad
 * @version     1.0.2
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador finaliza el flujo de recuperación de cuenta. Valida la 
 * autenticidad y vigencia de un token temporal almacenado en la base de datos,
 * realiza el cambio de credenciales mediante hashing seguro y revoca el token
 * para prevenir ataques de reutilización (Replay Attacks).
 * * SEGURIDAD:
 * 1. Sentencias Preparadas: Mitigación total contra Inyección SQL (mysqli::prepare).
 * 2. Cifrado BCrypt: Implementación de password_hash para almacenamiento seguro.
 * 3. Invalidación Unilateral: Limpieza de tokens tras el primer uso exitoso.
 */

/**
 * CONFIGURACIÓN DE CABECERAS
 * Define el estándar de intercambio de datos como JSON.
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 * Conexión centralizada a la base de datos institucional.
 */
include("../../config/db_local.php");

/**
 * CAPTURA Y DECODIFICACIÓN DE DATOS
 * Procesa el flujo de entrada desde el cuerpo de la petición (JSON Payload).
 */
$data = json_decode(file_get_contents("php://input"), true);

/**
 * VALIDACIÓN DE INTEGRIDAD DE DATOS
 * Verifica la existencia de los campos necesarios para la operación.
 */
if (!isset($data['token']) || !isset($data['password'])) {
    echo json_encode([
        "success" => false, 
        "error" => "Parámetros incompletos. Se requiere token y nueva credencial."
    ]);
    exit();
}

$token = $data['token'];

/**
 * CIFRADO DE CREDENCIALES
 * Genera un hash seguro utilizando el algoritmo BCrypt.
 * @var string $nueva_pass Hash generado para almacenamiento persistente.
 */
$nueva_pass = password_hash($data['password'], PASSWORD_BCRYPT);

try {
    /**
     * 1. VALIDACIÓN DE TOKEN Y VIGENCIA
     * Consulta si el token existe y si la hora actual es menor a la de expiración.
     * La función NOW() garantiza sincronía con la hora del servidor DB.
     */
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE reset_token = ? AND token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        /**
         * 2. ACTUALIZACIÓN DE CREDENCIALES Y CIERRE DE SESIÓN TÉCNICA
         * Actualiza la contraseña y establece los campos de recuperación a NULL.
         * Esto garantiza que el enlace de recuperación sirva para una única ejecución.
         */
        $update = $conexion->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, token_expira = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $nueva_pass, $token);
        
        if ($update->execute()) {
            echo json_encode([
                "success" => true, 
                "message" => "¡Contraseña actualizada con éxito! Ya puedes acceder a SIRA con tus nuevas credenciales."
            ]);
        } else {
            throw new Exception("Error al intentar persistir los datos en la base de datos.");
        }
    } else {
        /**
         * NOTA DE SEGURIDAD:
         * No se especifica si el token no existe o si expiró para no dar pistas 
         * sobre la existencia de cuentas en intentos de fuerza bruta.
         */
        echo json_encode([
            "success" => false, 
            "error" => "El enlace de recuperación es inválido o ha expirado. Por favor, solicita uno nuevo."
        ]);
    }
} catch (Exception $e) {
    /**
     * GESTIÓN DE EXCEPCIONES
     * Captura errores técnicos y los devuelve en formato amigable para el frontend.
     */
    echo json_encode([
        "success" => false, 
        "error" => "Fallo en el servidor: " . $e->getMessage()
    ]);
}

/**
 * CIERRE DE RECURSOS
 * Libera la conexión al motor de base de datos.
 */
$conexion->close();