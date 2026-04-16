<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * API: PROCESAMIENTO DE REASIGNACIÓN Y ACTIVACIÓN DE NOTIFICACIÓN
 */

header('Content-Type: application/json');
include '../../config/db_local.php';

// 1. RECOLECCIÓN DE DATOS
$data = json_decode(file_get_contents('php://input'), true);

/**
 * SINCRONIZACIÓN DE IDENTIFICADORES
 */
$id_solicitud = 0;
if (isset($data['id_solicitud'])) {
    $id_solicitud = (int)$data['id_solicitud'];
} elseif (isset($data['id_editando'])) {
    $id_solicitud = (int)$data['id_editando'];
}

$id_auditorio = isset($data['id_auditorio']) ? (int)$data['id_auditorio'] : 0;
$fecha_evento = isset($data['fecha']) ? mysqli_real_escape_string($conexion, $data['fecha']) : '';
$hora_inicio  = isset($data['hora_inicio']) ? mysqli_real_escape_string($conexion, $data['hora_inicio']) : '';
$hora_fin     = isset($data['hora_fin']) ? mysqli_real_escape_string($conexion, $data['hora_fin']) : '';

// 🟢 CAMBIO CRÍTICO: Recibimos el número de asistentes del JSON
$num_asistentes = isset($data['num_asistentes']) ? (int)$data['num_asistentes'] : 0;
$notas = isset($data['notas']) ? mysqli_real_escape_string($conexion, $data['notas']) : '';

// Validación de seguridad básica
if ($id_solicitud === 0 || $id_auditorio === 0 || empty($fecha_evento) || empty($hora_inicio)) {
    echo json_encode([
        'success' => false, 
        'error' => 'Datos incompletos para procesar. ID: ' . $id_solicitud
    ]);
    exit;
}

/**
 * 2. LÓGICA DE ACTUALIZACIÓN (SINERGIA TSU)
 * 🟢 Se añade 'num_asistentes' a la consulta SQL.
 */
$query = "UPDATE solicitudes SET 
            id_auditorio = $id_auditorio, 
            fecha_evento = '$fecha_evento', 
            hora_inicio  = '$hora_inicio', 
            hora_fin     = '$hora_fin',
            num_asistentes = $num_asistentes, 
            observaciones_admin = '$notas',
            notificacion_admin = 1, 
            estado = 'Pendiente' 
          WHERE id_solicitud = $id_solicitud";

// 3. EJECUCIÓN Y RESPUESTA
if (mysqli_query($conexion, $query)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Reasignación exitosa. Folio #' . $id_solicitud . ' y asistentes actualizados.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Error SQL: ' . mysqli_error($conexion)
    ]);
}

exit;