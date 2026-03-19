<?php
/**
 * ACCIONES ADMINISTRATIVAS DE USUARIOS - SIRA
 * Cambio de DELETE a UPDATE (Baja Lógica) para mantener integridad referencial.
 */
include '../config/db_local.php';

// Aseguramos que la respuesta sea interpretada como JSON por el JS
header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception("ID de usuario no proporcionado.");
    }

    $id = mysqli_real_escape_string($conexion, $_POST['id']);

    // BAJA LÓGICA: Cambiamos el estatus a 0 en lugar de borrar el registro
    $query = "UPDATE usuarios SET estatus = 0 WHERE id_usuario = '$id'";
    
    $res = mysqli_query($conexion, $query);

    if ($res) {
        // Si el UPDATE fue exitoso, mandamos la confirmación al JS
        echo json_encode([
            'success' => true,
            'message' => 'El usuario ha sido desactivado con éxito.'
        ]);
    } else {
        throw new Exception(mysqli_error($conexion));
    }

} catch (Exception $e) {
    // Capturamos cualquier error y lo mandamos como JSON limpio
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
exit;
?>