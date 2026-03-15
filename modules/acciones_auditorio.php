<?php
/**
 * MÓDULO DE ACCIONES PARA AUDITORIOS
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Procesa peticiones AJAX para modificar el estado de disponibilidad 
 * o eliminar registros de la tabla 'auditorio'.
 */
include '../config/db_local.php';

// Aseguramos que la respuesta sea interpretada como JSON por el frontend
header('Content-Type: application/json');

// Verificamos la existencia de los parámetros obligatorios enviados por el método POST
if (isset($_POST['id']) && isset($_POST['accion'])) {
    
    // Saneamiento del ID para prevenir inyecciones SQL
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $accion = $_POST['accion'];

    /**
     * CASO 1: CAMBIO DE ESTADO (Disponibilidad)
     * Actualiza el valor binario (1 o 0) para habilitar o deshabilitar un espacio.
     */
    if ($accion === 'estado') {
        $nuevoEstado = mysqli_real_escape_string($conexion, $_POST['estado']);
        $sql = "UPDATE auditorio SET disponibilidad = $nuevoEstado WHERE id_auditorio = $id";
    } 
    
    /**
     * CASO 2: ELIMINACIÓN DE REGISTRO
     * Borra el registro de la base de datos y gestiona la eliminación de la imagen asociada.
     */
    elseif ($accion === 'eliminar') {
        $sql = "DELETE FROM auditorio WHERE id_auditorio = $id";
        
        /**
         * Gestión de archivos:
         * Se busca la fotografía física en el servidor para evitar almacenamiento de archivos huérfanos.
         */
        $ruta_foto = "../assets/img/auditorios/" . $id . ".jpg";
        if (file_exists($ruta_foto)) {
            unlink($ruta_foto); // Borra el archivo del sistema
        }
    }

    // Ejecución de la consulta construida y envío de respuesta al JavaScript
    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
} else {
    // Respuesta en caso de peticiones incompletas
    echo json_encode(['success' => false, 'error' => 'Parámetros insuficientes']);
}

exit;
?>