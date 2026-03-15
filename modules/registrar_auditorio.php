<?php
/**
 * MÓDULO DE REGISTRO DE AUDITORIOS
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Registra un nuevo espacio físico y procesa la carga de su imagen identificativa.
 */
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'];
    $capacidad = $_POST['capacidad'];
    $equipamiento = $_POST['equipamiento'];

    // Registro inicial de los datos técnicos del espacio
    $sql = "INSERT INTO auditorio (nombre_espacio, ubicacion, capacidad_maxima, equipamiento_fijo, disponibilidad) 
            VALUES ('$nombre', '$ubicacion', '$capacidad', '$equipamiento', 1)";

    if (mysqli_query($conexion, $sql)) {
        // Obtención del ID generado para nombrar el archivo de imagen
        $last_id = mysqli_insert_id($conexion);

        // Procesamiento de la fotografía del auditorio
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $ruta_destino = "../assets/img/auditorios/" . $last_id . ".jpg";
            move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino); // Persistencia en servidor
        }

        header("Location: ../admin_auditorios.php?success=1");
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>