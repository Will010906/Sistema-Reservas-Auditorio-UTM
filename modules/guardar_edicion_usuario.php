<?php
session_start();
include("../config/db_local.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_editando'])) {
    $id_sol = intval($_POST['id_editando']);
    
    // Captura total de campos
    $id_auditorio = intval($_POST['id_auditorio']);
    $titulo       = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion  = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $fecha        = mysqli_real_escape_string($conexion, $_POST['fecha_evento']);
    $h_inicio     = mysqli_real_escape_string($conexion, $_POST['hora_inicio']);
    $h_fin        = mysqli_real_escape_string($conexion, $_POST['hora_fin']);
    $otros        = mysqli_real_escape_string($conexion, $_POST['otros_servicios']);

    // UPDATE total: Permite cambiar incluso la logística si el usuario regresó al calendario
    $sql = "UPDATE solicitudes SET 
            id_auditorio = '$id_auditorio',
            titulo_event = '$titulo', 
            descripcion = '$descripcion',
            fecha_evento = '$fecha',
            hora_inicio = '$h_inicio',
            hora_fin = '$h_fin',
            otros_servicios = '$otros'
            WHERE id_solicitud = $id_sol AND estado = 'PENDIENTE'";

    if (mysqli_query($conexion, $sql)) {
        header("Location: ../panel_usuario.php?status=success_edit");
    } else {
        header("Location: ../panel_usuario.php?status=error");
    }
}
?>