<?php session_start(); echo "<h1>Bienvenido Usuario: " . $_SESSION['nombre'] . "</h1>"; ?>

<?php
include("db.php");

/* CONSULTA PARA LA TABLA */
$sql = "SELECT * FROM solicitudes";
$resultado = mysqli_query($conexion,$sql);

?>
<!DOCTYPE html>
<html>

<head>
<title>Panel Usuario</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<h2>Mis Solicitudes</h2>

<!-- Tarjetas de aceptación  -->

<div class="table-container">

<table>

<tr>
<th>Folio</th>
<th>Evento</th>
<th>Auditorio</th>
<th>Fecha</th>
<th>Estatus</th>
</tr>

<?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

<tr>

<td><?php echo $fila['titulo_event']; ?></td>
<td><?php echo $fila['id_auditorio']; ?></td>
<td><?php echo $fila['fecha_evento']; ?></td>

<td class="<?php

if($fila['estado']=="Rechazada"){
echo "status-urgent";
}
elseif($fila['estado']=="Pendiente"){
echo "status-process";
}
else{
echo "status-ok";
}

?>">

<?php echo $fila['estado']; ?>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>