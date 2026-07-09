<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * CAPA DE DATOS: CONFIGURACIÓN DE CONEXIÓN INSTITUCIONAL (ENTORNO LOCAL)
 * * @package     Config
 * @subpackage  Database
 * @version     1.0.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Establece el túnel de comunicación síncrono entre el servidor Apache y el 
 * motor de base de datos MariaDB/MySQL. Utiliza la extensión nativa MySQLi 
 * para garantizar un bajo consumo de recursos en el entorno de desarrollo XAMPP.
 * * NOTA DE SEGURIDAD:
 * Este archivo contiene credenciales administrativas del sistema. Su acceso 
 * debe estar restringido mediante políticas de servidor (.htaccess).
 */

// Revierte al modo de error clásico (por defecto en PHP 8.1+ mysqli lanza excepciones,
// lo cual rompe el fallback local/producción de abajo si no se desactiva).
mysqli_report(MYSQLI_REPORT_OFF);

// PARÁMETROS DE CONFIGURACIÓN DEL SERVIDOR (ENTORNO LOCAL / XAMPP)
$host = "localhost";             // Hostname del servidor de base de datos
$user = "root";                  // Superusuario administrativo (Default XAMPP)
$pass = "";                      // Credencial de acceso (Vacía por política de desarrollo)
$db   = "reservacionauditorios"; // Identificador del esquema institucional

/**
 * INSTANCIACIÓN DE LA CONEXIÓN
 * @var mysqli $conexion Objeto de enlace con la base de datos.
 */
$conexion = mysqli_connect($host, $user, $pass, $db);

/**
 * FALLBACK AUTOMÁTICO A PRODUCCIÓN (SERVIDOR UTM)
 * Si el entorno local (XAMPP) no responde, se asume que el sistema corre
 * en el servidor de la universidad y se usan las credenciales asignadas
 * al Equipo 11 (ver config/db.php).
 */
if (!$conexion) {
    $host = "mysql";
    $user = "user_equipo11";
    $pass = "user_secret_password11";
    $db   = "proyecto_equipo11_db";

    $conexion = mysqli_connect($host, $user, $pass, $db);
}

/**
 * VALIDACIÓN DE INTEGRIDAD DE ENLACE
 * Verifica si el handshake con el motor de base de datos fue exitoso.
 */
if (!$conexion) {
    /**
     * GESTIÓN DE FALLOS CRÍTICOS
     * En caso de error, el sistema detiene el hilo de ejecución para evitar
     * inconsistencias de datos en los paneles administrativos.
     */
    die("Error crítico de persistencia: " . mysqli_connect_error());
}

/**
 * CONFIGURACIÓN DE CHARSET (RECOMENDADO)
 * Asegura la correcta codificación de caracteres especiales (acentos, ñ) 
 * para los nombres de auditorios y usuarios de la UTM.
 */
mysqli_set_charset($conexion, "utf8mb4");

/**
 * ALCANCE OPERATIVO:
 * Este recurso es requerido por 'panel_admin.php' para el motor de solicitudes,
 * 'get_mis_reservas.php' para usuarios, y el motor de gestión de auditorios.
 */
?>