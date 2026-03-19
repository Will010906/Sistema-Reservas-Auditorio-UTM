<?php
include("../config/db_local.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $matricula = strtoupper(trim($_POST['matricula']));
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $carrera = $_POST['carrera'];
    $pass_plana = $_POST['password'];
    
    // NUEVA LÍNEA: Capturamos el teléfono del formulario
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);

    // 1. Verificar si la matrícula ya existe
    $checkQuery = "SELECT id_usuario FROM usuarios WHERE matricula = '$matricula'";
    $resCheck = mysqli_query($conexion, $checkQuery);

    if (mysqli_num_rows($resCheck) > 0) {
        header("Location: ../registro.php?error=existe");
        exit();
    }

    $pass_hash = password_hash($pass_plana, PASSWORD_DEFAULT);

    // 2. INSERT actualizado con la columna 'telefono'
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, telefono, password, perfil, carrera_area, estatus) 
              VALUES ('$matricula', '$nombre', '$correo', '$telefono', '$pass_hash', 'alumno', '$carrera', 1)";

    if (mysqli_query($conexion, $query)) {
        header("Location: ../login.php?status=reg_success");
    } else {
        header("Location: ../registro.php?error=db");
    }
}
?>