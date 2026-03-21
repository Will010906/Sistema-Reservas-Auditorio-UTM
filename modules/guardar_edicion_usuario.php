<?php
session_start();
include("../config/db_local.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_editando'])) {
    $id_sol = intval($_POST['id_editando']);
    
    // Captura y limpieza de campos
    $id_auditorio = intval($_POST['id_auditorio']);
    $titulo       = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion  = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $fecha        = mysqli_real_escape_string($conexion, $_POST['fecha_evento']);
    $h_inicio     = mysqli_real_escape_string($conexion, $_POST['hora_inicio']);
    $h_fin        = mysqli_real_escape_string($conexion, $_POST['hora_fin']);
    $otros        = mysqli_real_escape_string($conexion, $_POST['otros_servicios']);

    // 1. Iniciamos el UPDATE con los campos de texto
    $sql = "UPDATE solicitudes SET 
            titulo_event = '$titulo', 
            descripcion = '$descripcion',
            otros_servicios = '$otros'";

    // 2. Solo actualizamos el auditorio si el ID es válido (evita error de Foreign Key)
    if ($id_auditorio > 0) {
        $sql .= ", id_auditorio = '$id_auditorio'";
    }

    // 3. Solo actualizamos fecha y hora si Andrea las cambió en el calendario
    if (!empty($fecha))    $sql .= ", fecha_evento = '$fecha'";
    if (!empty($h_inicio))  $sql .= ", hora_inicio = '$h_inicio'";
    if (!empty($h_fin))     $sql .= ", hora_fin = '$h_fin'";

    // 4. Aplicamos el filtro de seguridad
    $sql .= " WHERE id_solicitud = $id_sol AND estado = 'PENDIENTE'";

    if (mysqli_query($conexion, $sql)) {
        header("Location: ../panel_usuario.php?status=success_edit");
    } else {
        // En caso de error, mostramos el detalle para depurar
        die("Error en SQL: " . mysqli_error($conexion));
    }
}
?>