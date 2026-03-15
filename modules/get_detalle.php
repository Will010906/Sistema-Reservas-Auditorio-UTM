<?php
/**
 * MÓDULO DE DETALLE DE SOLICITUD
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Obtiene la información exhaustiva de una sola solicitud para su revisión.
 */
include '../config/db_local.php';

// Validación de parámetro de entrada
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);

    /**
     * Consulta con Relación (JOIN):
     * Cruza la tabla 'solicitudes' con 'usuarios' para obtener el nombre del solicitante.
     */
    $query = "SELECT s.*, u.nombre 
              FROM solicitudes s 
              JOIN usuarios u ON s.id_usuario = u.id_usuario 
              WHERE s.id_solicitud = '$id'";

    $res = mysqli_query($conexion, $query);
    $data = mysqli_fetch_assoc($res);

    // Envío de datos estructurados al frontend
    header('Content-Type: application/json');
    echo json_encode($data);
}
?>