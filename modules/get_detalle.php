<?php
/**
 * MOTOR DE CONSULTA DE DETALLES - SIRA UTM
 * Reutilizable para Admin y Usuarios.
 */
include("../config/db_local.php");

// Aseguramos que el ID sea un número para evitar inyecciones SQL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $query = "SELECT s.*, 
                     u.nombre, u.telefono, 
                     a.nombre_espacio, a.capacidad_maxima,
                     GROUP_CONCAT(CONCAT(e.nombre_equipo, ' (', de.cantidad, ')') SEPARATOR ', ') as equipos_solicitados
              FROM solicitudes s
              JOIN usuarios u ON s.id_usuario = u.id_usuario
              JOIN auditorio a ON s.id_auditorio = a.id_auditorio
              LEFT JOIN detalle_equipamiento de ON s.id_solicitud = de.id_solicitud
              LEFT JOIN equipamiento e ON de.id_equipamiento = e.id_equipamiento
              WHERE s.id_solicitud = $id
              GROUP BY s.id_solicitud";

    $res = mysqli_query($conexion, $query);
    
    if ($res && mysqli_num_rows($res) > 0) {
        $data = mysqli_fetch_assoc($res);
        // Formateo de fecha para el modal
        $data['fecha_evento_limpia'] = date('d/m/Y', strtotime($data['fecha_evento']));
        echo json_encode($data);
    } else {
        echo json_encode(["error" => "No se encontró la solicitud"]);
    }
} else {
    echo json_encode(["error" => "ID no válido"]);
}
?>