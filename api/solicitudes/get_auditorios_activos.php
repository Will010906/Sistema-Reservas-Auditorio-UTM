<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: CONSULTA DE INFRAESTRUCTURA ACTIVA
 * * @package     Controladores_API
 * @subpackage  Gestion_Auditorios
 * @version     1.0.2
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint recupera el catálogo de espacios físicos que se encuentran 
 * operativos en el sistema. Filtra los registros según su estatus de 
 * disponibilidad para asegurar que solo se muestren auditorios aptos 
 * para nuevas reservaciones en el Frontend.
 * * FLUJO DE DATOS:
 * 1. Conexión: Establece vínculo con el motor de base de datos MySQL local.
 * 2. Extracción: Ejecuta una consulta selectiva sobre la tabla 'auditorio'.
 * 3. Serialización: Transforma el conjunto de resultados en un objeto JSON.
 */

/**
 * CONFIGURACIÓN DE CABECERAS
 * Define que la respuesta del servidor será un objeto de datos estructurado.
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS
 */
include '../../config/db_local.php';

/**
 * 1. CAPA DE VALIDACIÓN DE CONECTIVIDAD
 * Verifica la integridad del puente de datos antes de proceder con la consulta.
 */
if (!$conexion) {
    echo json_encode(['success' => false, 'error' => 'Fallo crítico: No se pudo establecer conexión con el servidor de datos institucional.']);
    exit;
}

/**
 * 2. NÚCLEO FUNCIONAL: CONSULTA DE DISPONIBILIDAD
 * Selecciona los atributos clave de los auditorios donde 'disponibilidad' es igual a 1 (Activo).
 * 
 */
$query = "SELECT id_auditorio, nombre_espacio, capacidad_maxima, ubicacion, equipamiento_fijo 
          FROM auditorio 
          WHERE disponibilidad = 1";

$res = mysqli_query($conexion, $query);

/**
 * 3. PROCESAMIENTO DE RESULTADOS
 * Itera los registros obtenidos y los encapsula en un array asociativo.
 */
$datos = [];
while ($row = mysqli_fetch_assoc($res)) {
    $datos[] = $row;
}

/**
 * 4. RESPUESTA FINAL
 * Retorna la colección de auditorios al cliente solicitante.
 */
echo json_encode($datos);

/**
 * CIERRE DE EJECUCIÓN
 */
exit;