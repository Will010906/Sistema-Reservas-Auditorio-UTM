<?php
include '../config/db_local.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);

    $sql = "DELETE FROM usuarios WHERE id_usuario = '$id'";

    if (mysqli_query($conexion, $sql)) {
        header("Location: ../admin_usuarios.php?status=deleted");
    } else {
        header("Location: ../admin_usuarios.php?status=error_db");
    }
    exit();
}
?>