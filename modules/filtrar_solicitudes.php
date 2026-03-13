<?php
include '../config/db.php';

$inicio = $_GET['inicio'];
$fin = $_GET['fin'];

$query = "SELECT * FROM solicitudes WHERE fecha_evento BETWEEN '$inicio' AND '$fin'";
$resultado = mysqli_query($conexion, $query);

$json = [];
while($row = mysqli_fetch_assoc($resultado)) {
    $json[] = $row;
}

echo json_encode($json); // Mandamos los datos a JavaScript
?>