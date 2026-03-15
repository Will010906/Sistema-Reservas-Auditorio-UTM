<?php
/**
 * ARCHIVO DE CONEXIÓN REMOTA
 * Proyecto: Sistema de Reservación de Auditorios - UTM (Equipo 11)
 * Descripción: Configuración para conectar el sistema a un servidor de base de datos remoto.
 */

// Parámetros de red del servidor remoto
$servername = "192.168.99.3";         // Dirección IP del servidor de base de datos
$username   = "user_equipo11";        // Usuario asignado al equipo 11
$password   = "user_secret_password11"; // Credencial de acceso segura
$dbname     = "proyecto_equipo11_db"; // Base de datos compartida del proyecto
$port       = 3311;                   // Puerto específico de comunicación SQL

// Conexión incluyendo el parámetro del puerto para servidores con configuración personalizada
$conexion = mysqli_connect($servername, $username, $password, $dbname, $port);

// Validación de enlace
if (!$conexion) {
    // Si la conexión remota falla, se informa el error para depuración de red
    die("Error de conexión al servidor remoto: " . mysqli_connect_error());
}
?>