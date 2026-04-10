<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * @package     Controladores_API
 * @subpackage  Gestion_Auditorios
 * @version     1.0.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este controlador actúa como el punto final (endpoint) centralizado para la 
 * administración de la entidad 'auditorio'. Implementa una arquitectura 
 * RESTful simplificada procesando peticiones asíncronas para operaciones CRUD.
 * * SEGURIDAD:
 * Requiere una capa de autenticación basada en JWT (JSON Web Tokens) transmitida 
 * a través de las cabeceras HTTP para garantizar que solo usuarios autorizados 
 * puedan manipular los recursos del sistema.
 */

// Buffering de salida para asegurar que los headers JSON se envíen sin basura de salida
ob_start();

/**
 * CONFIGURACIÓN DE CABECERAS (HEADERS)
 * Define la respuesta como objeto JSON y permite comunicación asíncrona.
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE CONFIGURACIONES
 * Conexión a la base de datos local del servidor.
 */
include '../../config/db_local.php';

// Limpieza de cualquier salida accidental previa para evitar errores de parseo JSON en el cliente
ob_clean();

/**
 * CAPA DE AUTENTICACIÓN JWT
 * Captura y valida el Token Bearer enviado desde el cliente (LocalStorage).
 * @throws 401 Unauthorized si el token no es válido o inexistente.
 */
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || strpos($auth, 'Bearer ') === false) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'No autorizado. Token inexistente o inválido.'
    ]);
    exit;
}

/**
 * RUTEADOR DE MÉTODOS HTTP
 * Captura el verbo (POST, PATCH, DELETE) para determinar la acción a ejecutar.
 */
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($metodo) {
        
        /**
         * ACCIÓN: CREAR O ACTUALIZAR (UPSERT)
         * Se utiliza POST debido a las limitaciones nativas de PHP para procesar 
         * archivos adjuntos (multipart/form-data) mediante PUT o PATCH.
         * * @param int|null $id_auditorio ID del recurso (si existe es actualización).
         * @param string   $nombre       Etiqueta identificadora del auditorio.
         * @param string   $ubicacion    Referencia física (Edificio/Piso).
         * @param int      $capacidad    Aforo máximo permitido.
         * @param string   $equipamiento Descripción de recursos técnicos fijos.
         * @param file     $foto         Archivo de imagen opcional para el catálogo.
         */
        case 'POST':
            $id = !empty($_POST['id_auditorio']) ? intval($_POST['id_auditorio']) : null;
            $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
            $ubicacion = mysqli_real_escape_string($conexion, $_POST['ubicacion']);
            $capacidad = intval($_POST['capacidad']);
            $equipamiento = mysqli_real_escape_string($conexion, $_POST['equipamiento']);

            if ($id) {
                // Bloque de Actualización de registro existente
                $sql = "UPDATE auditorio SET 
                        nombre_espacio='$nombre', 
                        ubicacion='$ubicacion', 
                        capacidad_maxima=$capacidad, 
                        equipamiento_fijo='$equipamiento' 
                        WHERE id_auditorio=$id";
            } else {
                // Bloque de Inserción de nuevo recurso (Estado inicial: Disponible [1])
                $sql = "INSERT INTO auditorio (nombre_espacio, ubicacion, capacidad_maxima, equipamiento_fijo, disponibilidad) 
                        VALUES ('$nombre', '$ubicacion', $capacidad, '$equipamiento', 1)";
            }

            if (mysqli_query($conexion, $sql)) {
                // Determinación del ID objetivo para el nombre de la imagen
                $target_id = $id ? $id : mysqli_insert_id($conexion);
                
                /**
                 * PROCESAMIENTO DE ARCHIVOS MULTIMEDIA
                 * Valida y almacena la fotografía en el directorio de activos del servidor.
                 */
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                    $dir = "../../assets/img/auditorios/";
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    
                    // Almacenamiento físico vinculado al ID único del registro
                    move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $target_id . ".jpg");
                }
                echo json_encode(["success" => true, "message" => "Datos guardados correctamente"]);
            } else { 
                throw new Exception("Error en la base de datos: " . mysqli_error($conexion)); 
            }
            break;

        /**
         * ACCIÓN: ACTUALIZACIÓN PARCIAL (ESTADO)
         * Procesa el cambio de disponibilidad mediante la recepción de un objeto JSON.
         * * @param int $id     ID único del auditorio.
         * @param int $estado Valor binario (0: No disponible, 1: Disponible).
         */
        case 'PATCH':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id']);
            $st = intval($data['estado']);
            
            $sql = "UPDATE auditorio SET disponibilidad = $st WHERE id_auditorio = $id";
            
            if (mysqli_query($conexion, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Estado de disponibilidad actualizado']);
            } else { 
                throw new Exception(mysqli_error($conexion)); 
            }
            break;

        /**
         * ACCIÓN: ELIMINACIÓN DE RECURSO
         * Ejecuta una baja definitiva eliminando el registro y sus activos físicos.
         * * @param int $id ID del auditorio a suprimir.
         */
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id']);
            
            // Eliminación del recurso multimedia asociado para evitar orfandad de archivos
            $foto = "../../assets/img/auditorios/$id.jpg";
            if (file_exists($foto)) unlink($foto);
            
            // Remoción de la tupla en la base de datos
            $sql = "DELETE FROM auditorio WHERE id_auditorio = $id";
            if (mysqli_query($conexion, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Auditorio eliminado permanentemente']);
            } else { 
                throw new Exception(mysqli_error($conexion)); 
            }
            break;
            
        default:
            throw new Exception("Método HTTP $metodo no permitido en este endpoint");
    }
} catch (Exception $e) {
    /**
     * MANEJO DE EXCEPCIONES
     * Captura cualquier fallo en la ejecución y lo comunica al frontend en formato estandarizado.
     */
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * CIERRE DEL BUFFER
 * Envía la salida procesada y libera la memoria.
 */
ob_end_flush();