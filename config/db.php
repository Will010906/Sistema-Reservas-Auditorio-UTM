<?php
$host = "192.168.99.3";               // IP de la dirección de conexión BD
$port = 9091;                       // Puerto específico de la base de datos
$user = "user_equipo11";              // Usuario de la BD
$password = "user_secret_password11"; // Contraseña de la BD
$database = "proyecto_equipo11_db";   // Nombre de la base de datos

// Conexión incluyendo el puerto
$conexion = mysqli_connect($host, $user, $password, $database, $port);

if (!$conexion) {
    die("Error de conexión al servidor remoto: " . mysqli_connect_error());
}
?>