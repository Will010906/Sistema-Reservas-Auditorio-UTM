<?php
// 1. Iniciamos sesión y conectamos a la base de datos
session_start();
if (!isset($_SESSION['nombre'])) {
    header("Location: index.php"); // Seguridad: si no hay sesión, regresa al login
    exit();
}
include 'config/db_local.php'; //

// 2. Consultas para las tarjetas (Contadores dinámicos)
// Estos querys alimentan las tarjetas de colores de tu wireframe
// Cambiamos 'estatus' por 'estado'
$res_urgentes = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Urgente'");
$urgentes = mysqli_fetch_assoc($res_urgentes)['total'];

$res_demorados = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Pendiente'");
$demorados = mysqli_fetch_assoc($res_demorados)['total'];

$res_tiempo = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'Aceptada'");
$con_tiempo = mysqli_fetch_assoc($res_tiempo)['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - UTM</title>
    <link rel="stylesheet" href="assets/css/estilo.css"> 
</head>
<body>

<div class="container">
    <h2>Panel Administrador</h2>

    <h3>
        Bienvenido Administrador: <?php echo $_SESSION['nombre']; ?>
    </h3>

    <div class="cards">
        <div class="card red">
            <h3>Urgentes por autorizar</h3>
            <div class="number"><?php echo $urgentes; ?></div> <p> < 3 días</p>
        </div>

        <div class="card yellow">
            <h3>Demorados por autorizar</h3>
            <div class="number"><?php echo $demorados; ?></div>
            <p>5 días</p>
        </div>

        <div class="card green">
            <h3>Con tiempo</h3>
            <div class="number"><?php echo $con_tiempo; ?></div> <p>+ días</p>
        </div>
    </div>

    <div class="filters">
        <input type="date" id="fecha_inicio">
        <input type="date" id="fecha_fin">
        <button id="btnFiltrar">Filtrar</button>
    </div>

    <h3>Tabla de Gestión</h3>

    <table id="tablaSolicitudes">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Solicitante</th>
                <th>Auditorio</th>
                <th>Autorizante</th>
                <th>Fecha del Evento</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 3. Llenado dinámico de la tabla
            $query = "SELECT * FROM solicitudes";
            $resultado = mysqli_query($conexion, $query);

            while ($row = mysqli_fetch_assoc($resultado)) {
                // Determinamos la clase CSS según el estatus para el semáforo
                $clase_estatus = ($row['estatus'] == 'Urgente') ? 'urgent' : (($row['estatus'] == 'Pendiente') ? 'delay' : 'ontime');
                
                echo "<tr>
                        <td>{$row['folio']}</td>
                        <td>{$row['nombre_solicitante']}</td>
                        <td>{$row['auditorio']}</td>
                        <td>{$row['autorizante']}</td>
                        <td>{$row['fecha_evento']}</td>
                        <td><span class='status {$clase_estatus}'>{$row['estatus']}</span></td>
                        <td><button class='btn' onclick='gestionar({$row['id']})'>Gestionar</button></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script src="assets/js/admin_interactivo.js"></script>

</body>
</html>