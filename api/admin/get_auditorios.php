<?php
/**
 * API: OBTENER INVENTARIO DE AUDITORIOS - SIRA UTM
 */
// Limpiar cualquier salida accidental (espacios en blanco)
ob_clean(); 
header('Content-Type: application/json');

include '../../config/db_local.php';

// SEGURIDAD: Validación de Bearer Token
$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    // VALIDACIÓN CRÍTICA: ¿La conexión existe?
    if (!isset($conexion) || !$conexion) {
        throw new Exception("La conexión a la base de datos no se estableció. Revisa config/db_local.php");
    }

    // Cambia 'auditorio' por 'auditorios' si ese es el nombre real de tu tabla
    $sql = "SELECT * FROM auditorio ORDER BY nombre_espacio ASC";
    $resultado = mysqli_query($conexion, $sql);

    if (!$resultado) {
        throw new Exception("Error en la consulta SQL: " . mysqli_error($conexion));
    }

    $auditorios = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $row['id_auditorio'] = (int)$row['id_auditorio'];
        $row['capacidad_maxima'] = (int)$row['capacidad_maxima'];
        
        // CORRECCIÓN: Verifica si tu columna se llama 'disponible' o 'disponibilidad'
        // Si en el otro archivo usamos 'disponible', asegúrate que en la BD se llame igual
        $row['disponible'] = isset($row['disponible']) ? (int)$row['disponible'] : 0;
        
        $auditorios[] = $row;
    }

    echo json_encode($auditorios);

} catch (Exception $e) {
    http_response_code(500);
    // Esto enviará el error real al JS para que lo veas en la consola
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}