<?php
$servername = "192.168.99.3";
$username = "user_equipo11";
$password = "user_secret_password11";
$dbname = "proyecto_equipo11_db";
$port = 3311;

// Conexión incluyendo el puerto
$conexion = mysqli_connect( $servername, $username, $password, $dbname, $port );

if (!$conexion) {
    die("Error de conexión al servidor remoto: " . mysqli_connect_error());
}

