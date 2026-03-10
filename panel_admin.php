<?php session_start(); echo "<h1>Bienvenido Administrador: " . $_SESSION['nombre'] . "</h1>"; ?>

<?php 
session_start(); 
require_once("conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Administrador</title>
<link rel="stylesheet" href="assets/css/estilo.css">
</head>

<body>

<div class="container">

<h2>Panel Administrador</h2>

<h3>
<?php 
echo "Bienvenido Administrador: " . $_SESSION['nombre']; 
?>
</h3>

<div class="cards">

<div class="card red">
<h3>Urgentes por autorizar</h3>
<div class="number">8</div>
<p>< 3 días</p>
</div>

<div class="card yellow">
<h3>Demorados por autorizar</h3>
<div class="number">15</div>
<p>5 días</p>
</div>

<div class="card green">
<h3>Con tiempo</h3>
<div class="number">7</div>
<p>+ días</p>
</div>

</div>

<div class="filters">
<input type="date">
<input type="date">
<button>Filtrar</button>
</div>

<h3>Tabla de Gestión</h3>

<table>

<tr>
<th>Folio</th>
<th>Solicitante</th>
<th>Auditorio</th>
<th>Autorizante</th>
<th>Fecha del Evento</th>
<th>Estatus</th>
<th></th>
</tr>

<tr>
<td>FOL-001</td>
<td>Juan Perez</td>
<td>Auditorio A</td>
<td>Juan Carlos</td>
<td>23-04-2025</td>
<td><span class="status urgent">Urgente</span></td>
<td><button class="btn">Gestionar</button></td>
</tr>

<tr>
<td>FOL-002</td>
<td>Laura Rodriguez</td>
<td>Auditorio B</td>
<td>Juan Carlos</td>
<td>23-04-2025</td>
<td><span class="status delay">Demorado</span></td>
<td><button class="btn">Gestionar</button></td>
</tr>

<tr>
<td>FOL-003</td>
<td>Carlos Martinez</td>
<td>Auditorio C</td>
<td>Juan Carlos</td>
<td>23-04-2025</td>
<td><span class="status ontime">Con Tiempo</span></td>
<td><button class="btn">Gestionar</button></td>
</tr>

<tr>
<td>FOL-004</td>
<td>Ana Lopez</td>
<td>Auditorio D</td>
<td>Juan Carlos</td>
<td>23-04-2025</td>
<td><span class="status urgent">Urgente</span></td>
<td><button class="btn">Gestionar</button></td>
</tr>

</table>

</div>

</body>
</html>