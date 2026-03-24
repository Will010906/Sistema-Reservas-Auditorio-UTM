<?php
/**
 * GENERADOR DE IDENTIDAD - NIVEL TSU
 * Genera un JWT manual con expiración de 10 minutos y gestiona rutas.
 */
header('Content-Type: application/json');
include '../../config/db_local.php';

// 1. LEER DATOS JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Datos no recibidos']);
    exit;
}

$matricula = trim(mysqli_real_escape_string($conexion, $data['matricula']));
$password_ingresado = trim($data['password']);

// 2. BUSCAR USUARIO
$query = "SELECT id_usuario, nombre, perfil, carrera_area, password FROM usuarios WHERE matricula='$matricula' AND estatus = 1";
$resultado = mysqli_query($conexion, $query);

if ($usuario = mysqli_fetch_assoc($resultado)) {
    
    if (password_verify($password_ingresado, $usuario['password'])) {
        
        // --- 3. CREACIÓN MANUAL DEL JWT ---
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $expire = time() + (10 * 60); 
        $payload = json_encode([
            'id' => $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'perfil' => strtolower($usuario['perfil']),
            'area' => $usuario['carrera_area'],
            'exp' => $expire 
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'UTM_SIRA_2026', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        // --- 4. LÓGICA DE REDIRECCIÓN (Lo que arregla el error 404) ---
        $perfil = strtolower($usuario['perfil']);
        $redirect = 'panel_usuario.php'; // Por defecto

        if ($perfil === 'administrador') {
            $redirect = 'panel_admin.php';
        } elseif ($perfil === 'subdirector') {
            $redirect = 'panel_subdirector.php';
        }

        // 5. RESPUESTA EN JSON COMPLETA
        echo json_encode([
            'success' => true,
            'token' => $jwt,
            'perfil' => $perfil,
            'redirect' => $redirect, // 👈 ¡ESTO ES LO QUE EL JS NECESITA!
            'message' => '¡Bienvenido, ' . $usuario['nombre'] . '!'
        ]);

    } else {
        echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Usuario no encontrado o inactivo']);
}
exit;