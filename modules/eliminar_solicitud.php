<?php
/**
 * MÓDULO DE ELIMINACIÓN DE SOLICITUDES (SEGURIDAD REFORZADA)
 */
session_start();
include '../config/db_local.php';

// Verificamos sesión iniciada y parámetro ID
if (isset($_SESSION['id_usuario']) && isset($_GET['id'])) {
    
    $id_solicitud = intval($_GET['id']);
    $id_user_sesion = $_SESSION['id_usuario'];
    $rol_user = $_SESSION['rol'] ?? 'Usuario';

    // Si es Administrador, borra cualquier cosa.
    // Si es Usuario, solo borra si le pertenece Y está PENDIENTE.
    if ($rol_user === 'Admin') {
        $sql = "DELETE FROM solicitudes WHERE id_solicitud = $id_solicitud";
    } else {
        $sql = "DELETE FROM solicitudes 
                WHERE id_solicitud = $id_solicitud 
                AND id_usuario = '$id_user_sesion' 
                AND estado = 'PENDIENTE'";
    }

    if (mysqli_query($conexion, $sql)) {
        // mysqli_affected_rows nos dice si realmente se borró algo
        if (mysqli_affected_rows($conexion) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar esta solicitud o ya no está pendiente.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado o ID faltante.']);
}
?>