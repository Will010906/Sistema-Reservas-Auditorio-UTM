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
    // 1. Usamos 'estado' en lugar de 'estatus'
    $clase_estatus = ($row['estado'] == 'Urgente') ? 'urgent' : (($row['estado'] == 'Pendiente') ? 'delay' : 'ontime');
    
    echo "<tr>
            <td>{$row['folio']}</td>
            <td>{$row['titulo_event']}</td> <td>ID Auditorio: {$row['id_auditorio']}</td> <td>Pendiente</td> <td>{$row['fecha_evento']}</td>
            <td><span class='status {$clase_estatus}'>{$row['estado']}</span></td>
            <td>
                <button class='btn' onclick='gestionar({$row['id_solicitud']})'>Gestionar</button>
            </td>
          </tr>";
}
            ?>
        </tbody>
    </table>
</div>
<div id="modalGestion" class="modal" style="display:none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 8px;">
        <span onclick="cerrarModal()" style="float: right; cursor: pointer; font-size: 28px;">&times;</span>
        <h2 id="modalFolio">Detalle de la Solicitud</h2>
        <hr>
        <p><strong>Solicitante:</strong> <span id="modalNombre"></span></p>
        <p><strong>Auditorio:</strong> <span id="modalAuditorio"></span></p>
        <p><strong>Fecha:</strong> <span id="modalFecha"></span></p>
        <div style="margin-top: 20px;">
            <button class="btn ontime" onclick="actualizarEstado('Aceptada')">Aprobar</button> <button class="btn urgent" onclick="actualizarEstado('Rechazada')">Rechazar</button> </div>
    </div>
</div>

<script src="assets/js/admin_interactivo.js"></script>

</body>
</html>