<?php
/**
 * LÓGICA DE CIERRE DE SESIÓN - SIRA
 */
session_start(); // Unirse a la sesión actual

// 1. Limpiar todas las variables de sesión
$_SESSION = array();

// 2. Si se desea destruir la cookie de sesión también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al login con un parámetro de estatus
header("Location: ../login.php?status=logout"); 
exit();
?>