<?php
/**
 * MÓDULO DE CONSULTA DE DISPONIBILIDAD
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Verifica qué horarios están ya ocupados para un auditorio y fecha específicos.
 * Lógica: Solo considera solicitudes con estado 'Aceptada' para bloquear el calendario.
 */
include '../config/db_local.php';

// Captura de parámetros desde la petición asíncrona (Fetch)
$id_auditorio = $_GET['id'];
$fecha = $_GET['fecha'];

/**
 * Consulta de Ocupación:
 * Selecciona los rangos de tiempo de solicitudes confirmadas por el administrador.
 */
$query = "SELECT hora_inicio, hora_fin FROM solicitudes 
          WHERE id_auditorio = '$id_auditorio' 
          AND fecha_evento = '$fecha' 
          AND estado = 'Aceptada'";

$res = mysqli_query($conexion, $query);
$ocupados = [];

// Estructuración de datos para el motor de renderizado en gestion_reservas.js
while($row = mysqli_fetch_assoc($res)) {
    $ocupados[] = [
        'inicio' => $row['hora_inicio'],
        'fin' => $row['hora_fin']
    ];
}

// Respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($ocupados);