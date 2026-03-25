<?php
$servername = "mysql";
$username   = "user_equipo11";
$password   = "user_secret_password11"; // Asegúrate que sea la que usaste en Adminer
$dbname     = "proyecto_equipo11_db";

// Intentar la conexión sin especificar puerto (usará 3306 por defecto)
$conexion = mysqli_connect($servername, $username, $password, $dbname);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>