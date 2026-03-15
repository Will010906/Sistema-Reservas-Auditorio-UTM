<?php
/**
 * ACCIONES ADMINISTRATIVAS DE USUARIOS
 * Descripción: Procesa peticiones de mantenimiento de cuentas (como eliminación).
 * Formato de respuesta: JSON (para integración con Fetch API).
 */
include '../config/db_local.php';

// Aseguramos que la respuesta sea interpretada como JSON por el navegador
header('Content-Type: application/json');

if (isset($_POST['id']) && isset($_POST['accion'])) {
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    
    // Switch de acciones: actualmente procesa 'eliminar_usuario'
    if ($_POST['accion'] === 'eliminar_usuario') {
        $sql = "DELETE FROM usuarios WHERE id_usuario = '$id'";
        
        if (mysqli_query($conexion, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']);
}
exit;
?>