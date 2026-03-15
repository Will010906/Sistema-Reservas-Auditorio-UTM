<?php
/**
 * MÓDULO DE EDICIÓN DE AUDITORIOS
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Actualiza la información técnica de un auditorio y gestiona la carga de nuevas fotografías.
 */
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura de datos enviados desde el modal de administración
    $id = $_POST['id_auditorio'];
    $nombre = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'];
    $capacidad = $_POST['capacidad'];
    $equipamiento = $_POST['equipamiento'];

    /**
     * SQL de Actualización:
     * Actualiza nombre, ubicación, capacidad y la lista de equipamiento fijo.
     */
    $sql = "UPDATE auditorio SET 
            nombre_espacio = '$nombre', 
            ubicacion = '$ubicacion', 
            capacidad_maxima = '$capacidad', 
            equipamiento_fijo = '$equipamiento' 
            WHERE id_auditorio = $id";

    if (mysqli_query($conexion, $sql)) {
        /**
         * Gestión de Imagen:
         * Si el usuario seleccionó una foto nueva, se sobreescribe el archivo .jpg existente 
         * usando el ID del auditorio como nombre único.
         */
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            move_uploaded_file($_FILES['foto']['tmp_name'], "../assets/img/auditorios/$id.jpg");
        }
        
        // Redirección con parámetro de éxito para retroalimentación visual
        header("Location: ../admin_auditorios.php?success=edit");
    }
}
?>