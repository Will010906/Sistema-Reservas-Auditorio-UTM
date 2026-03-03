<?php
$host = "localhost";
$user = "root";
$password = ""; // XAMPP por defecto no tiene contraseña
$database = "reservacionauditorios"; 

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    die("Error de conexión local (XAMPP): " . mysqli_connect_error());
}
?>