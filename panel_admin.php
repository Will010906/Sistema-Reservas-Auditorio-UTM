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
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/admin.php.css">
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
<div id="modalDetalle" class="modal-container" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Revisión de Solicitud</h3>
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <div class="detalle-izq">
                <h2 id="detFolio">Folio: </h2>
                <p><strong>Fecha de Registro:</strong> <span id="detFechaSol"></span></p>
                <p><strong>Estado Actual:</strong> <span id="detEstado"></span></p>
                <p><strong>Fecha del Evento:</strong> <span id="detFechaEv"></span></p>
                
                <div class="acciones-btn">
                    <button class="btn red" onclick="actualizarEstado('Rechazada')">Rechazar</button>
                    <button class="btn green" onclick="actualizarEstado('Aceptada')">Aprobar</button>
                </div>
                <textarea id="motivoRechazo" placeholder="Por favor indicar el motivo de rechazo..."></textarea>
            </div>

            <div class="detalle-der">
                <h3>Usuario Solicitante:</h3>
                <p id="detUsuarioNombre" style="font-size: 1.2rem; font-weight: bold;"></p>
                <p><strong>Evento:</strong> <span id="detTituloEv"></span></p>
                <p><strong>Descripción:</strong></p>
                <p class="motivo-texto" id="detDescripcion"></p>
                <button class="btn blue" onclick="cerrarModal()">Confirmar / Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/admin_interactivo.js"></script>

</body>
</html>