<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * CONTROLADOR DE API: GESTIÓN INTEGRAL DE USUARIOS
 * * @package     Controladores_API
 * @subpackage  Gestion_Usuarios
 * @version     1.0.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este componente implementa un endpoint RESTful para la administración de la
 * entidad 'usuarios'. Centraliza las operaciones de consulta, registro,
 * actualización y baja definitiva.
 * * SEGURIDAD:
 * Implementa una capa de validación mediante Bearer Token (JWT) para cumplir
 * con los estándares de seguridad requeridos por la institución.
 */

/**
 * CONFIGURACIÓN DE RESPUESTA
 * Establece el encabezado para transferencia de datos en formato JSON.
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE DEPENDENCIAS
 * Archivo de conexión a la base de datos institucional.
 */
include '../../config/db_local.php';

/**
 * 1. CAPA DE SEGURIDAD (Bearer Token JWT)
 * Recupera las cabeceras de la petición y valida la existencia de un token de sesión.
 * @throws 401 Unauthorized si el token no cumple con el formato Bearer.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'Acceso denegado. Se requiere un token de autenticación válido.'
    ]);
    exit;
}

/**
 * CAPTURA DE PARÁMETROS DE ENTRADA
 * Recupera el verbo HTTP y el cuerpo de la petición (JSON Payload).
 */
$metodo = $_SERVER['REQUEST_METHOD'];
$json = file_get_contents('php://input');
$data = json_decode($json, true);

/**
 * 2. RUTEADOR DE OPERACIONES (Protocolo REST)
 * Determina la lógica a ejecutar basándose en el método HTTP solicitado.
 */
switch ($metodo) {

    /**
     * OPERACIÓN: LISTAR USUARIOS (GET)
     * Recupera la colección completa de usuarios registrados, omitiendo datos sensibles.
     * @return json Lista de objetos usuario ordenados alfabéticamente.
     */
    case 'GET':
        $sql = "SELECT id_usuario, nombre, matricula, correo_electronico, telefono, carrera_area, perfil, estatus 
                FROM usuarios ORDER BY nombre ASC";
        $res = mysqli_query($conexion, $sql);
        $usuarios = [];
        while($row = mysqli_fetch_assoc($res)) { $usuarios[] = $row; }
        echo json_encode($usuarios);
        exit; 

    /**
     * OPERACIÓN: REGISTRO (POST)
     * Inserta un nuevo colaborador en la base de datos con contraseña cifrada.
     * @param string nombre              Obligatorio.
     * @param string correo_electronico  Obligatorio (Único).
     * @param string password            Opcional (Default: 12345678).
     */
    case 'POST':
        if (empty($data['nombre']) || empty($data['correo_electronico'])) {
            echo json_encode(['success' => false, 'error' => 'Nombre y correo son campos obligatorios.']);
            exit;
        }

        // Sanitización de datos para prevención de Inyección SQL
        $mat = mysqli_real_escape_string($conexion, $data['matricula'] ?? '');
        $nom = mysqli_real_escape_string($conexion, $data['nombre']);
        $cor = mysqli_real_escape_string($conexion, $data['correo_electronico']);
        $tel = mysqli_real_escape_string($conexion, $data['telefono'] ?? '');
        $car = mysqli_real_escape_string($conexion, $data['carrera_area'] ?? '');
        $per = mysqli_real_escape_string($conexion, $data['perfil'] ?? 'alumno');
        
        // Protocolo de Seguridad: Hashing de contraseña (algoritmo BCrypt)
        $pass_plano = !empty($data['password']) ? $data['password'] : '12345678';
        $pass_hash = password_hash($pass_plano, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (matricula, nombre, correo_electronico, telefono, password, perfil, carrera_area, estatus) 
                VALUES ('$mat', '$nom', '$cor', '$tel', '$pass_hash', '$per', '$car', 1)";
        
        ejecutarSimple($conexion, $sql, "El usuario '$nom' ha sido registrado satisfactoriamente.");
        break;

    /**
     * OPERACIÓN: ACTUALIZACIÓN (PUT)
     * Modifica los atributos de un perfil de usuario existente mediante su ID único.
     * @param int id_usuario ID de referencia en la base de datos.
     */
    case 'PUT':
        $id = intval($data['id_usuario'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Identificador de usuario no válido.']);
            exit;
        }

        $nom = mysqli_real_escape_string($conexion, $data['nombre']);
        $mat = mysqli_real_escape_string($conexion, $data['matricula']);
        $tel = mysqli_real_escape_string($conexion, $data['telefono']);
        $cor = mysqli_real_escape_string($conexion, $data['correo_electronico']);
        $car = mysqli_real_escape_string($conexion, $data['carrera_area']);
        $per = mysqli_real_escape_string($conexion, $data['perfil']);

        $sql = "UPDATE usuarios SET nombre='$nom', matricula='$mat', telefono='$tel', 
                correo_electronico='$cor', carrera_area='$car', perfil='$per' WHERE id_usuario=$id";
        ejecutarSimple($conexion, $sql, "La información del usuario ha sido actualizada.");
        break;

    /**
     * OPERACIÓN: ELIMINACIÓN (DELETE)
     * Remueve de forma permanente el registro del usuario.
     * @param int id_usuario ID del registro a suprimir.
     */
    case 'DELETE':
        $id_usuario = isset($data['id_usuario']) ? intval($data['id_usuario']) : 0;

        if ($id_usuario > 0) {
            $sql = "DELETE FROM usuarios WHERE id_usuario = $id_usuario";
            ejecutarSimple($conexion, $sql, "Usuario eliminado permanentemente del sistema.");
        } else {
            echo json_encode(['success' => false, 'error' => "No se especificó el ID del usuario a eliminar."]);
        }
        break;

    /**
     * RESPUESTA POR DEFECTO
     * Maneja métodos HTTP no implementados en este controlador.
     */
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método HTTP no permitido.']);
        break;
}

/**
 * FUNCIÓN AUXILIAR: ejecutarSimple
 * Automatiza la ejecución de queries y el manejo de excepciones de base de datos.
 * * @param mysqli $conexion Recurso de conexión activa.
 * @param string $sql      Sentencia SQL a ejecutar.
 * @param string $msg      Mensaje de éxito personalizado.
 */
function ejecutarSimple($conexion, $sql, $msg) {
    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        $error = mysqli_error($conexion);
        
        /**
         * TRADUCCIÓN DE ERRORES TÉCNICOS A MENSAJES DE USUARIO
         * Maneja restricciones de integridad y duplicidad.
         */
        if (strpos($error, 'foreign key') !== false) {
            $error = "Restricción de Integridad: El usuario posee reservaciones vinculadas y no puede ser eliminado.";
        } elseif (strpos($error, 'Duplicate entry') !== false) {
            $error = "Conflicto de Datos: La matrícula o el correo electrónico ya se encuentran registrados.";
        }
        
        echo json_encode(['success' => false, 'error' => $error]);
    }
    exit;
}