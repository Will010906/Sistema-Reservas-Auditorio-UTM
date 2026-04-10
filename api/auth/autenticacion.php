<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * SERVICIO DE AUTENTICACIÓN Y GENERADOR DE TOKENS (JWT)
 * * @package     Controladores_API
 * @subpackage  Seguridad
 * @version     2.0.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador procesa el inicio de sesión (Login). Implementa una creación 
 * manual de JSON Web Tokens (JWT) conforme al estándar RFC 7519, utilizando 
 * el algoritmo de firma HS256 (HMAC con SHA-256).
 * * FUNCIONALIDADES CLAVE:
 * 1. Verificación de Hash: Compara credenciales usando password_verify.
 * 2. Criptografía: Genera firmas digitales base64url seguras.
 * 3. Ruteo Dinámico: Determina el destino del usuario según su rol institucional.
 */

/**
 * CONFIGURACIÓN DE SALIDA
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 * Conexión a la base de datos y configuración local.
 */
include '../../config/db_local.php';

/**
 * 1. RECEPCIÓN DE FLUJO DE DATOS (INPUT STREAM)
 * Se capturan los datos enviados vía Fetch API en formato JSON.
 */
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Petición malformada: No se recibieron datos']);
    exit;
}

// Sanitización de entrada para prevenir inyección SQL
$matricula = trim(mysqli_real_escape_string($conexion, $data['matricula']));
$password_ingresado = trim($data['password']);

/**
 * 2. CONSULTA DE IDENTIDAD
 * Busca al usuario por matrícula únicamente si su estatus es activo (1).
 */
$query = "SELECT id_usuario, nombre, perfil, carrera_area, password FROM usuarios WHERE matricula='$matricula' AND estatus = 1";
$resultado = mysqli_query($conexion, $query);

if ($usuario = mysqli_fetch_assoc($resultado)) {
    
    /**
     * VERIFICACIÓN CRIPTOGRÁFICA
     * Valida el password plano contra el hash almacenado en la base de datos.
     */
    if (password_verify($password_ingresado, $usuario['password'])) {
        
        /**
         * 3. CONSTRUCCIÓN MANUAL DEL JWT (JSON WEB TOKEN)
         * El token se compone de tres partes: Header, Payload y Signature.
         */

        // PARTE 1: HEADER (Define el tipo y algoritmo)
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // PARTE 2: PAYLOAD (Datos públicos del usuario y tiempo de vida)
        // Expiración configurada a 10 minutos (600 segundos)
        $expire = time() + (10 * 60); 
        $payload = json_encode([
            'id'     => $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'perfil' => strtolower($usuario['perfil']),
            'area'   => $usuario['carrera_area'],
            'exp'    => $expire 
        ]);

        // CODIFICACIÓN BASE64URL (Elimina carácteres no seguros para URLs: +, /, =)
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // PARTE 3: SIGNATURE (Firma digital de integridad)
        // Utiliza una "Secret Key" única del sistema para firmar el contenido.
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'UTM_SIRA_2026', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        /**
         * TOKEN FINALIZADO
         * @var string $jwt Token resultante listo para el LocalStorage del cliente.
         */
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        /**
         * 4. LÓGICA DE CONTROL DE ACCESO (RUTEO)
         * Define el punto de entrada (Dashboard) según el nivel jerárquico del usuario.
         */
        $perfil = strtolower($usuario['perfil']);
        $redirect = 'panel_usuario.php'; // Dashboard estándar para Alumnos/Docentes

        if ($perfil === 'administrador') {
            $redirect = 'panel_admin.php';
        } elseif ($perfil === 'subdirector') {
            $redirect = 'panel_subdirector.php';
        }

        /**
         * 5. RESPUESTA EXITOSA
         * Devuelve el objeto de identidad completo para ser procesado por el frontend.
         */
        echo json_encode([
            'success'  => true,
            'token'    => $jwt,
            'perfil'   => $perfil,
            'redirect' => $redirect,
            'message'  => 'Sesión iniciada. ¡Bienvenido, ' . $usuario['nombre'] . '!'
        ]);

    } else {
        // Error de autenticación: Password incorrecto
        echo json_encode(['success' => false, 'error' => 'La contraseña es incorrecta. Inténtalo de nuevo.']);
    }
} else {
    // Error de identidad: Usuario no registrado o suspendido
    echo json_encode(['success' => false, 'error' => 'Acceso denegado: El usuario no existe o está inactivo.']);
}

/**
 * CIERRE DE EJECUCIÓN
 */
exit;