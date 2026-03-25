<?php
/**
 * ENDPOINT API: CIERRE DE SESIÓN - NIVEL TSU
 * Finaliza sesiones tradicionales y prepara la respuesta para limpiar el JWT.
 */
header('Content-Type: application/json');

// 1. Iniciamos sesión para poder destruirla (Compatibilidad)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Limpieza total de variables de servidor
$_SESSION = array();

// 3. Destrucción de Cookies de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();

// 5. RESPUESTA JSON (Crucial para el Frontend)
// No usamos header("Location"), enviamos éxito para que el JS limpie el LocalStorage
echo json_encode([
    "success" => true,
    "message" => "Sesión finalizada correctamente. Limpiando tokens...",
    "redirect" => "login.php?status=logout"
]);
exit;