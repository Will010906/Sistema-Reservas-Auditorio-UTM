<?php
/**
 * ARCHIVO DE CONEXIÓN LOCAL (XAMPP)
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Establece la comunicación con la base de datos MariaDB/MySQL en entorno local.
 */

// Parámetros de configuración del servidor local
$host = "localhost";             // Dirección del servidor (Local)
$user = "root";                  // Usuario administrador por defecto de XAMPP
$pass = "";                      // Contraseña (vacía por defecto en XAMPP)
$db   = "reservacionauditorios"; // Nombre de tu base de datos local

// Ejecución de la conexión mediante la extensión mysqli
$conexion = mysqli_connect($host, $user, $pass, $db);

// Verificación de la conexión
if (!$conexion) {
    // Si la conexión falla, detiene la ejecución y muestra el error técnico
    die("Error de conexión local: " . mysqli_connect_error());
}

/**
 * Nota: Esta conexión es la que utiliza actualmente 'panel_admin.php' 
 * para gestionar las solicitudes del dashboard.
 */
?>