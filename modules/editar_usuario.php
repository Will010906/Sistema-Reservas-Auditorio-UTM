<?php
include("../config/db_local.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_usuario'];
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']); // Nueva matrícula
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo_electronico']);
    $carrera = $_POST['carrera_area'];
    $perfil = $_POST['perfil'];

    $query = "UPDATE usuarios SET 
                nombre='$nombre', 
                matricula='$matricula', 
                telefono='$telefono',
                correo_electronico='$correo', 
                carrera_area='$carrera', 
                perfil='$perfil' 
              WHERE id_usuario=$id";

    if (mysqli_query($conexion, $query)) {
        header("Location: ../admin_usuarios.php?res=success");
    } else {
        header("Location: ../admin_usuarios.php?res=error");
    }
}
?>