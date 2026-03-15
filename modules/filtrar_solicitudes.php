<?php
/**
 * MÓDULO DE FILTRADO DINÁMICO DE SOLICITUDES
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Filtra las reservaciones por rango de fechas y prepara los datos para el frontend.
 * Formato de salida: JSON.
 */
include '../config/db_local.php';

// Captura y saneamiento de los parámetros de fecha enviados por el frontend
$inicio = mysqli_real_escape_string($conexion, $_GET['inicio']);
$fin = mysqli_real_escape_string($conexion, $_GET['fin']);

/**
 * Consulta SQL con Relaciones (JOIN):
 * Recupera los datos de la solicitud junto con el nombre del usuario y del auditorio 
 * en una sola petición a la base de datos.
 */
$query = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
          FROM solicitudes s
          JOIN usuarios u ON s.id_usuario = u.id_usuario
          JOIN auditorio a ON s.id_auditorio = a.id_auditorio
          WHERE s.fecha_evento BETWEEN '$inicio' AND '$fin'
          ORDER BY s.fecha_evento ASC";

$resultado = mysqli_query($conexion, $query);

$json = [];
while($row = mysqli_fetch_assoc($resultado)) {
    
    /**
     * Lógica de Presentación (Pre-procesamiento):
     * Determinamos la clase CSS y el texto que deberá mostrar el badge en la tabla
     * antes de enviarlo al JavaScript. Esto asegura consistencia visual.
     */
    if ($row['estado'] == 'Pendiente') {
        // Si está pendiente, el color depende de la prioridad (Semáforo)
        $row['clase_status'] = ($row['prioridad'] == 'Urgente') ? 'card-urgent' : (($row['prioridad'] == 'Pendiente') ? 'card-pending' : 'card-on-time');
        $row['texto_status'] = strtoupper($row['prioridad']);
    } else {
        // Si ya fue gestionada, el color depende de si fue Aceptada o Rechazada
        $row['clase_status'] = ($row['estado'] == 'Aceptada') ? 'card-accepted' : 'card-rejected';
        $row['texto_status'] = strtoupper($row['estado']);
    }
    
    $json[] = $row;
}

// Configuración de cabecera para respuesta de API y envío de datos
header('Content-Type: application/json');
echo json_encode($json);
?>