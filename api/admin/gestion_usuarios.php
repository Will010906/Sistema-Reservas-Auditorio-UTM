<?php
/**
 * API: GESTIÓN INTEGRAL DE USUARIOS - SIRA UTM
 * Centraliza: Registro (POST), Edición (PUT) y Eliminación (DELETE).
 */
header('Content-Type: application/json');
include '../../config/db_local.php';

// 1. SEGURIDAD: Validación de Bearer Token (JWT)
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado. Se requiere Token.']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 2. PROCESAR SEGÚN EL VERBO HTTP
switch ($metodo) {

    case 'GET': // --- LISTAR USUARIOS ---
        $sql = "SELECT id_usuario, nombre, matricula, correo_electronico, telefono, carrera_area, perfil, estatus 
                FROM usuarios ORDER BY nombre ASC";
        $res = mysqli_query($conexion, $sql);
        $usuarios = [];
        while($row = mysqli_fetch_assoc($res)) { $usuarios[] = $row; }
        echo json_encode($usuarios);
        exit; 

    case 'POST': // --- REGISTRO DE NUEVO USUARIO ---
        // Validamos que los campos esenciales no estén vacíos
        if (empty($data['nombre']) || empty($data['correo_electronico'])) {
            echo json_encode(['success' => false, 'error' => 'Nombre y correo son obligatorios.']);
            exit;
        }

        $mat = mysqli_real_escape_string($conexion, $data['matricula'] ?? '');
        $nom = mysqli_real_escape_string($conexion, $data['nombre']);
        $cor = mysqli_real_escape_string($conexion, $data['correo_electronico']);
        $tel = mysqli_real_escape_string($conexion, $data['telefono'] ?? '');
        $car = mysqli_real_escape_string($conexion, $data['carrera_area'] ?? '');
        $per = mysqli_real_escape_string($conexion, $data['perfil'] ?? 'alumno');
        
        // Encriptación segura (Mínimo 60 caracteres en la columna 'password' de tu DB)
        $pass_plano = !empty($data['password']) ? $data['password'] : '12345678';
        $pass_hash = password_hash($pass_plano, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (matricula, nombre, correo_electronico, telefono, password, perfil, carrera_area, estatus) 
                VALUES ('$mat', '$nom', '$cor', '$tel', '$pass_hash', '$per', '$car', 1)";
        
        if (mysqli_query($conexion, $sql)) {
            echo json_encode(['success' => true, 'message' => "Usuario '$nom' registrado con éxito."]);
        } else {
            $error_mysql = mysqli_error($conexion);
            // Manejo de error por si la matrícula o correo ya existen
            if (strpos($error_mysql, 'Duplicate entry') !== false) {
                $error_mysql = "La matrícula o el correo ya están registrados en la UTM.";
            }
            echo json_encode(['success' => false, 'error' => $error_mysql]);
        }
        exit;

    case 'PUT': // --- EDICIÓN ---
        $id  = intval($data['id_usuario']);
        $nom = mysqli_real_escape_string($conexion, $data['nombre']);
        $mat = mysqli_real_escape_string($conexion, $data['matricula']);
        $tel = mysqli_real_escape_string($conexion, $data['telefono']);
        $cor = mysqli_real_escape_string($conexion, $data['correo_electronico']);
        $car = mysqli_real_escape_string($conexion, $data['carrera_area']);
        $per = mysqli_real_escape_string($conexion, $data['perfil']);

        $sql = "UPDATE usuarios SET nombre='$nom', matricula='$mat', telefono='$tel', 
                correo_electronico='$cor', carrera_area='$car', perfil='$per' WHERE id_usuario=$id";
        ejecutarSimple($conexion, $sql, "Usuario actualizado correctamente.");
        break;

   case 'DELETE': // --- ELIMINACIÓN ---
        // 1. Validar que el ID llegó y convertirlo a número
        $id_usuario = isset($data['id_usuario']) ? intval($data['id_usuario']) : 0;

        if ($id_usuario > 0) {
            $sql = "DELETE FROM usuarios WHERE id_usuario = $id_usuario";
            
            // 2. Ejecutar y capturar cualquier error de MySQL
            if (mysqli_query($conexion, $sql)) {
                echo json_encode(['success' => true, 'message' => "Usuario eliminado."]);
            } else {
                $mysql_error = mysqli_error($conexion);
                // Manejo de llaves foráneas por si tiene reservaciones
                $mensaje_error = (strpos($mysql_error, 'foreign key') !== false) 
                    ? "No se puede eliminar: El usuario tiene reservaciones en el SIRA." 
                    : "Error de base de datos: " . $mysql_error;
                
                echo json_encode(['success' => false, 'error' => $mensaje_error]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => "ID de usuario inválido o no recibido."]);
        }
        exit;

function ejecutarSimple($conexion, $sql, $msg) {
    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        $error = mysqli_error($conexion);
        if (strpos($error, 'foreign key constraint') !== false) {
            $error = "No se puede eliminar: El usuario tiene reservaciones activas.";
        }
        echo json_encode(['success' => false, 'error' => $error]);
    }
    exit;
}
}