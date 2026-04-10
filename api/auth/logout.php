<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * ENDPOINT API: CIERRE DE SESIÓN (LOGOUT)
 * * @package     Controladores_API
 * @subpackage  Seguridad
 * @version     1.0.1
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador gestiona la terminación segura de la sesión del usuario. 
 * Realiza una limpieza profunda en tres niveles:
 * 1. Nivel Lógico: Limpia el arreglo superglobal $_SESSION.
 * 2. Nivel Cliente (Cookie): Invalida la cookie de sesión del navegador.
 * 3. Nivel Servidor: Destruye el archivo físico de sesión.
 * * IMPORTANTE:
 * Debido a que SIRA utiliza JWT, este endpoint no redirige mediante PHP.
 * Envía una señal de éxito para que el Frontend (JavaScript) proceda a
 * eliminar el Token del LocalStorage/SessionStorage.
 */

/**
 * CONFIGURACIÓN DE SALIDA
 * Garantiza que la respuesta sea interpretada como un objeto JSON.
 */
header('Content-Type: application/json');

/**
 * 1. INICIALIZACIÓN DE CONTEXTO
 * Se verifica el estado de la sesión y se inicia si es necesario para poder
 * acceder a los métodos de destrucción de la misma.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 2. VOLRADO DE DATOS (NIVEL LÓGICO)
 * Se sobreescribe el arreglo $_SESSION para asegurar que los datos del usuario
 * dejen de estar disponibles inmediatamente en la memoria del script.
 */
$_SESSION = array();

/**
 * 3. INVALIDACIÓN DE IDENTIFICADORES (COOKIES)
 * Si el sistema utiliza cookies para rastrear la sesión (PHPSESSID), se
 * solicita al navegador que expire la cookie restando tiempo al timestamp actual.
 */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

/**
 * 4. DESTRUCCIÓN DE RECURSOS EN SERVIDOR
 * Elimina de forma definitiva la información de sesión almacenada en el 
 * directorio temporal del servidor.
 */
session_destroy();

/**
 * 5. RESPUESTA AL FRONTEND
 * Envía las instrucciones de redirección y éxito.
 * * @return json Objeto indicando el éxito y la ruta de retorno al login.
 */
echo json_encode([
    "success"  => true,
    "message"  => "Sesión institucional finalizada. Limpiando credenciales locales...",
    "redirect" => "login.php?status=logout"
]);

/**
 * CIERRE DE EJECUCIÓN
 */
exit;