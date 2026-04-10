<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * CAPA DE DATOS: CONFIGURACIÓN DE CONEXIÓN PARA ENTORNO DE PRODUCCIÓN (DOCKER/RED)
 * * @package     Config
 * @subpackage  Database_Remote
 * @version     2.0.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Implementa la interfaz de conexión para arquitecturas basadas en red o contenedores.
 * A diferencia de la conexión local, este módulo utiliza resolución de nombres 
 * de servicio (DNS Interno) para localizar el motor de base de datos MariaDB.
 * * PARÁMETROS DE INFRAESTRUCTURA:
 * - Servername: Identificador del servicio en la red interna (ej. Contenedor MySQL).
 * - Autenticación: Credenciales específicas para el entorno del Equipo 11.
 */

// PARÁMETROS DE INTERCONEXIÓN DE RED
$servername = "mysql";                 // Alias del servicio de base de datos en la red
$username   = "user_equipo11";         // Identidad del usuario de base de datos
$password   = "user_secret_password11"; // Credencial de acceso de producción
$dbname     = "proyecto_equipo11_db";  // Catálogo de datos del proyecto SIRA

/**
 * INSTANCIACIÓN DE LA CONEXIÓN (PROTOCOLOS ESTÁNDAR)
 * Se establece la comunicación mediante el puerto 3306 por defecto.
 * @var mysqli $conexion Objeto de persistencia para el sistema.
 */
$conexion = mysqli_connect($servername, $username, $password, $dbname);

/**
 * CONTROL DE INTEGRIDAD DE SESIÓN
 * Verifica la disponibilidad del servicio remoto antes de permitir la 
 * ejecución de las APIs de solicitudes o auditorios.
 */
if (!$conexion) {
    /**
     * GESTIÓN DE FALLOS DE INFRAESTRUCTURA
     * Interrumpe la ejecución si el servicio 'mysql' no responde, 
     * protegiendo la integridad del Dashboard administrativo.
     */
    die("Error crítico de conexión remota: " . mysqli_connect_error());
}

/**
 * CONFIGURACIÓN DE CODIFICACIÓN INSTITUCIONAL
 * Asegura la correcta representación de caracteres especiales en los 
 * nombres de los espacios físicos de la UTM.
 */
mysqli_set_charset($conexion, "utf8mb4");

/**
 * NOTA TÉCNICA:
 * Este archivo sustituye a 'db_local.php' cuando el proyecto se despliega
 * en el servidor final o en infraestructura de contenedores.
 */
?>