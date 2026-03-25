<?php
header('Content-Type: application/json');
include '../../config/db_local.php';

try {
    // 1. Inicializamos TODOS los contadores en 0 (incluyendo demoradas)
    $stats = [
        'urgentes'   => 0,
        'demoradas'  => 0, // Agregamos este para la card amarilla
        'atiempo'    => 0,
        'aceptadas'  => 0,
        'rechazadas' => 0
    ];

    // 2. Consulta única para la tabla
    $sql_lista = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
                  FROM solicitudes s
                  JOIN usuarios u ON s.id_usuario = u.id_usuario
                  JOIN auditorio a ON s.id_auditorio = a.id_auditorio
                  ORDER BY s.fecha_evento ASC";
                  
    $res_lista = mysqli_query($conexion, $sql_lista);
    $solicitudes = [];

    // 3. Procesamos y contamos TODO en un solo ciclo
    while ($row = mysqli_fetch_assoc($res_lista)) {
        $fecha_evento = new DateTime($row['fecha_evento']);
        $hoy = new DateTime();
        
        // Calculamos la diferencia de días real
        $diff = $hoy->diff($fecha_evento)->days;
        $estado = strtoupper(trim($row['estado']));

        if ($estado === 'PENDIENTE') {
            // Lógica unificada para TABLA y CARDS
            if ($diff <= 3) {
                $row['prioridad_visual'] = 'URGENTE'; 
                $stats['urgentes']++;
            } elseif ($diff <= 7) {
                $row['prioridad_visual'] = 'DEMORADA'; 
                $stats['demoradas']++; // AQUÍ SE REPARA EL CONTEO AMARILLO
            } else {
                $row['prioridad_visual'] = 'A TIEMPO';
                $stats['atiempo']++;
            }
        } else {
            $row['prioridad_visual'] = $estado;
            // Contamos los estados finales
            if ($estado === 'ACEPTADA') $stats['aceptadas']++;
            if ($estado === 'RECHAZADA') $stats['rechazadas']++;
        }
        
        $solicitudes[] = $row;
    }

    // 4. Enviamos la respuesta unificada
    echo json_encode([
        "success" => true,
        "stats" => $stats,
        "solicitudes" => $solicitudes
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}