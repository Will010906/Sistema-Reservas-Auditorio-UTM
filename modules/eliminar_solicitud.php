<?php
/**
 * MÓDULO DE ELIMINACIÓN DE SOLICITUDES
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Elimina un registro de reservación de la base de datos mediante su ID. [cite: 1]
 * Formato de respuesta: JSON para integración con Fetch API.
 */
include '../config/db_local.php';

// Validación de la existencia del parámetro ID enviado por método GET
if (isset($_GET['id'])) {
    // Saneamiento del ID para seguridad de la consulta [cite: 1]
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    
    // Sentencia SQL de eliminación [cite: 2]
    $sql = "DELETE FROM solicitudes WHERE id_solicitud = '$id'";

    if (mysqli_query($conexion, $sql)) {
        // Respuesta exitosa capturada por admin_interactivo.js
        echo json_encode(['success' => true]);
    } else {
        // Error técnico en caso de fallo en la base de datos [cite: 3]
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
}
?>