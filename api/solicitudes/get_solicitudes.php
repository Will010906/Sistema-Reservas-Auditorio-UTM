<?php
header('Content-Type: application/json');
include '../../config/db_local.php';

try {
    // 1. Inicializamos contadores en 0
    $stats = [
        'urgentes'   => 0,
        'pendientes' => 0,
        'atiempo'    => 0,
        'aceptadas'  => 0,
        'rechazadas' => 0
    ];

    // 2. Conteo de Estados Reales
    $sql_estados = "SELECT estado, COUNT(*) as total FROM solicitudes GROUP BY estado";
    $res_estados = mysqli_query($conexion, $sql_estados);
    
    while ($row = mysqli_fetch_assoc($res_estados)) {
        $estado = strtoupper($row['estado']);
        if ($estado == 'PENDIENTE') $stats['pendientes'] = (int)$row['total'];
        if ($estado == 'ACEPTADA')  $stats['aceptadas']  = (int)$row['total'];
        if ($estado == 'RECHAZADA') $stats['rechazadas'] = (int)$row['total'];
    }

    // 3. Conteo de Urgentes (Pendientes en los próximos 3 días)
    $sql_urg = "SELECT COUNT(*) as total FROM solicitudes 
                WHERE estado = 'PENDIENTE' 
                AND fecha_evento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
    $res_urg = mysqli_query($conexion, $sql_urg);
    $stats['urgentes'] = (int)mysqli_fetch_assoc($res_urg)['total'];

    // 4. Conteo "A Tiempo" (Pendientes con más de 3 días de margen)
    $sql_time = "SELECT COUNT(*) as total FROM solicitudes 
                 WHERE estado = 'PENDIENTE' 
                 AND fecha_evento > DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
    $res_time = mysqli_query($conexion, $sql_time);
    $stats['atiempo'] = (int)mysqli_fetch_assoc($res_time)['total'];

    // 5. Listado de la tabla (Lo que ya te funciona)
    $sql_lista = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
                  FROM solicitudes s
                  JOIN usuarios u ON s.id_usuario = u.id_usuario
                  JOIN auditorio a ON s.id_auditorio = a.id_auditorio
                  ORDER BY s.fecha_evento ASC";
    $res_lista = mysqli_query($conexion, $sql_lista);
    $solicitudes = [];
    while ($row = mysqli_fetch_assoc($res_lista)) {
        $solicitudes[] = $row;
    }

    echo json_encode([
        "success" => true,
        "stats" => $stats,
        "solicitudes" => $solicitudes
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}