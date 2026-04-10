<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * ENDPOINT API: REGISTRO DE NUEVOS ALUMNOS
 * * @package     Controladores_API
 * @subpackage  Gestion_Usuarios
 * @version     1.0.5
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador procesa las solicitudes de alta para nuevos perfiles de tipo 
 * 'alumno'. Implementa una capa de saneamiento de datos mediante expresiones 
 * regulares y funciones de escape, garantizando la integridad de la base de datos.
 * * FLUJO DE SEGURIDAD:
 * 1. Sanitización: Limpieza de caracteres especiales e inyecciones básicas.
 * 2. Unicidad: Validación previa de matrícula para evitar colisión de registros.
 * 3. Hashing: Cifrado de credenciales mediante el algoritmo estándar de PHP.
 */

/**
 * CONFIGURACIÓN DE RESPUESTA
 * Establece el tipo de contenido como JSON para la comunicación con Fetch API.
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 * Enlace a la configuración y conexión de la base de datos local.
 */
include("../config/db_local.php");

/**
 * 1. RECEPCIÓN Y DECODIFICACIÓN DE DATOS
 * Captura el flujo de entrada 'php://input' para procesar el cuerpo JSON de la petición.
 */
$json = file_get_contents('php://input');
$data = json_decode($json, true);

/**
 * VALIDACIÓN DE MÉTODO Y CONTENIDO
 * Asegura que la petición sea vía POST y contenga un cuerpo válido.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST" && $data) {
    
    /**
     * SANEAMIENTO Y FORMATEO DE ATRIBUTOS
     * Aplicación de mysqli_real_escape_string para mitigar riesgos de Inyección SQL.
     */
    $nombre    = mysqli_real_escape_string($conexion, $data['nombre']);
    $matricula = strtoupper(trim(mysqli_real_escape_string($conexion, $data['matricula'])));
    $correo    = mysqli_real_escape_string($conexion, $data['correo']);
    $carrera   = mysqli_real_escape_string($conexion, $data['carrera']);
    $pass_plana = $data['password'];
    
    /**
     * NORMALIZACIÓN DE TELÉFONO
     * Utiliza expresiones regulares para extraer únicamente los dígitos numéricos,
     * garantizando un formato uniforme en la base de datos.
     */
    $telefono = preg_replace('/\D/', '', $data['telefono']); 
    $telefono = mysqli_real_escape_string($conexion, $telefono);

    /**
     * 2. VERIFICACIÓN DE INTEGRIDAD (UNICIDAD)
     * Consulta la existencia previa de la matrícula para prevenir registros duplicados.
     */
    $checkQuery = "SELECT id_usuario FROM usuarios WHERE matricula = '$matricula'";
    $resCheck = mysqli_query($conexion, $checkQuery);

    if (mysqli_num_rows($resCheck) > 0) {
        echo json_encode([
            "success" => false, 
            "error"   => "Conflicto de registro: La matrícula '$matricula' ya se encuentra vinculada a una cuenta existente."
        ]);
        exit();
    }

    /**
     * 3. CIFRADO DE CREDENCIALES (REGLA DE ORO)
     * Implementa password_hash con el algoritmo por defecto (BCrypt actualmente)
     * para asegurar que las contraseñas no sean legibles en la base de datos.
     */
    $pass_hash = password_hash($pass_plana, PASSWORD_DEFAULT);

    /**
     * 4. PERSISTENCIA DE DATOS
     * Inserción del nuevo registro con perfil predefinido como 'alumno' y estatus activo.
     */
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, telefono, password, perfil, carrera_area, estatus) 
              VALUES ('$matricula', '$nombre', '$correo', '$telefono', '$pass_hash', 'alumno', '$carrera', 1)";

    if (mysqli_query($conexion, $query)) {
        /**
         * RESPUESTA DE ÉXITO
         * Devuelve instrucciones para que el frontend notifique al usuario y redirija.
         */
        echo json_encode([
            "success"  => true,
            "message"  => "¡Registro institucional completado! Ya puedes acceder con tus credenciales.",
            "redirect" => "index.php?status=reg_success"
        ]);
    } else {
        /**
         * GESTIÓN DE ERRORES DE BASE DE DATOS
         */
        http_response_code(500);
        echo json_encode([
            "success" => false, 
            "error"   => "Error crítico en el motor de base de datos: " . mysqli_error($conexion)
        ]);
    }
} else {
    /**
     * RESPUESTA PARA PETICIONES INVÁLIDAS
     */
    http_response_code(400);
    echo json_encode([
        "success" => false, 
        "error"   => "Protocolo de petición inválido o estructura de datos incompleta."
    ]);
}

/**
 * CIERRE DE FLUJO
 */
exit;