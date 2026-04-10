<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * ENDPOINT API: PROCESO DE ALTA DE USUARIOS (REGISTRO)
 * * @package     Controladores_API
 * @subpackage  Gestion_Usuarios
 * @version     1.0.3
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador gestiona la inserción de nuevos registros en la entidad 'usuarios'.
 * Procesa datos enviados mediante el método POST, realiza el saneamiento de cadenas
 * para prevenir vulnerabilidades de Inyección SQL y aplica un algoritmo de 
 * dispersión (hashing) a las credenciales de acceso.
 * * ESPECIFICACIONES DE DISEÑO:
 * 1. Formato de intercambio: JSON (Application/json).
 * 2. Seguridad de persistencia: Algoritmo BCrypt vía PASSWORD_DEFAULT.
 * 3. Integridad de respuesta: Desactiva el reporte de errores nativo para 
 * evitar corrupciones en el parseo del cliente.
 */

/**
 * CONFIGURACIÓN DE INTEGRIDAD
 * Desactiva la visualización de errores directos para garantizar que la 
 * salida sea exclusivamente un objeto JSON válido.
 */
error_reporting(0); 

/**
 * CONFIGURACIÓN DE CABECERAS
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 * Conexión centralizada a la base de datos institucional.
 */
include '../../config/db_local.php';

/**
 * 1. RECEPCIÓN DE FLUJO DE DATOS (JS FETCH)
 * Captura el cuerpo de la petición (Payload) y lo transforma en un arreglo asociativo.
 */
$json = file_get_contents('php://input');
$data = json_decode($json, true);

/**
 * 2. VALIDACIÓN DE PROTOCOLO Y CONTENIDO
 * Asegura que la petición se realice por el método correcto y contenga datos.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST" && $data) {
    
    /**
     * SANEAMIENTO Y CAPTURA DE ATRIBUTOS (PREVENCIÓN SQLi)
     * Se aplica filtrado a cada variable antes de interactuar con el motor DB.
     */
    $matricula = mysqli_real_escape_string($conexion, $data['matricula']);
    $nombre    = mysqli_real_escape_string($conexion, $data['nombre']);
    $correo    = mysqli_real_escape_string($conexion, $data['correo']);
    $telefono  = mysqli_real_escape_string($conexion, $data['telefono']);
    $carrera   = mysqli_real_escape_string($conexion, $data['carrera']);
    
    /**
     * PROTOCOLO DE SEGURIDAD: CIFRADO
     * @var string $password Hash resultante de la credencial de acceso.
     */
    $password  = password_hash($data['password'], PASSWORD_DEFAULT);
    
    /**
     * DEFINICIÓN DE PERFIL OPERATIVO
     * Por defecto, los registros desde este endpoint se asignan con el rol 'alumno'.
     */
    $perfil    = 'alumno'; 

    /**
     * 3. PERSISTENCIA DE DATOS
     * Ejecuta la inserción en la tabla 'usuarios' respetando el modelo relacional.
     * El estatus se inicializa en '1' (Activo).
     */
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, password, telefono, perfil, carrera_area, estatus) 
              VALUES ('$matricula', '$nombre', '$correo', '$password', '$telefono', '$perfil', '$carrera', 1)";

    if (mysqli_query($conexion, $query)) {
        /**
         * RETORNO EXITOSO
         */
        echo json_encode(["success" => true]);
    } else {
        /**
         * GESTIÓN DE EXCEPCIONES EN DB
         * Captura el error técnico de MySQL y lo encapsula en el JSON de respuesta.
         */
        echo json_encode([
            "success" => false, 
            "error"   => mysqli_error($conexion)
        ]);
    }
}

/**
 * FINALIZACIÓN DEL SCRIPT
 */
exit;