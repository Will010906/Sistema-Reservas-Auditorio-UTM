<?php
session_start();
include("../config/db_local.php");

// 1. Validar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php?error=session");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Recolección y limpieza de datos (Sanitización)
    $id_usuario    = $_SESSION['id_usuario'];
    $id_auditorio  = mysqli_real_escape_string($conexion, $_POST['id_auditorio']);
    $titulo_event  = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion   = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $fecha_evento  = mysqli_real_escape_string($conexion, $_POST['fecha_evento']);
    $hora_inicio   = mysqli_real_escape_string($conexion, $_POST['hora_inicio']);
    $hora_fin      = mysqli_real_escape_string($conexion, $_POST['hora_fin']);

    // Otros campos capturados del formulario
    $otros_req     = mysqli_real_escape_string($conexion, $_POST['otros_servicios']);
    $extras        = isset($_POST['extras']) ? implode(', ', $_POST['extras']) : 'Ninguno';

    // 3. Generar Folio Automático CONSECUTIVO (Sin saltos)
    // Buscamos el ID más alto actualmente en la tabla solicitudes
    $res_folio = mysqli_query($conexion, "SELECT MAX(id_solicitud) as ultimo FROM solicitudes");
    $row = mysqli_fetch_assoc($res_folio);
    
    // Si la tabla está vacía, empezamos en 1. Si no, tomamos el último + 1.
    $proximo_id = ($row['ultimo']) ? $row['ultimo'] + 1 : 1;
    
    // Formateamos a FOL-001, FOL-002, etc.
    $folio = "FOL-" . str_pad($proximo_id, 3, "0", STR_PAD_LEFT);

    // 4. Inserción en la base de datos
    // NOTA: Asegúrate de que las columnas coincidan exactamente con tu DB
    $sql = "INSERT INTO solicitudes (
                id_usuario, 
                id_auditorio, 
                folio, 
                titulo_event, 
                descripcion, 
                fecha_evento, 
                hora_inicio, 
                hora_fin, 
                estado, 
                fecha_registro
            ) VALUES (
                '$id_usuario', 
                '$id_auditorio', 
                '$folio', 
                '$titulo_event', 
                '$descripcion', 
                '$fecha_evento', 
                '$hora_inicio', 
                '$hora_fin', 
                'PENDIENTE', 
                NOW()
            )";

    if (mysqli_query($conexion, $sql)) {
        // ÉXITO: Redirigimos al panel con el estatus y el folio generado para el SweetAlert
        header("Location: ../panel_usuario.php?status=success&folio=$folio");
    } else {
        // ERROR: Redirigimos indicando el fallo
        header("Location: ../panel_usuario.php?status=error");
    }
}
?>