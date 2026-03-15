<?php

/**
 * COMPONENTE DE NAVEGACIÓN LATERAL (SIDEBAR)
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Este archivo genera dos barras laterales: 
 * 1. Activity Bar: Barra delgada con iconos para accesos rápidos.
 * 2. Side Bar: Barra con etiquetas de texto para navegación principal.
 * * El sistema utiliza PHP para detectar la página actual y marcar el enlace como 'activo'.
 */
?>

<div class="activity-bar d-flex flex-column align-items-center py-4">
    <div class="mb-5">
        <i class="bi bi-mortarboard-fill fs-2 text-success"></i>
    </div>

    <a href="panel_admin.php"
        class="text-white mb-4 <?php echo basename($_SERVER['PHP_SELF']) == 'panel_admin.php' ? 'opacity-100' : 'opacity-50'; ?>"
        title="Solicitudes">
        <i class="bi bi-file-earmark-text fs-4"></i>
    </a>

    <a href="admin_auditorios.php"
        class="text-white mb-4 <?php echo basename($_SERVER['PHP_SELF']) == 'admin_auditorios.php' ? 'opacity-100' : 'opacity-50'; ?>"
        title="Auditorios">
        <i class="bi bi-building fs-4"></i>
    </a>

    <a href="admin_usuarios.php"
        class="text-white mb-4 <?php echo basename($_SERVER['PHP_SELF']) == 'admin_usuarios.php' ? 'opacity-100' : 'opacity-50'; ?>"
        title="Usuarios">
        <i class="bi bi-people fs-4"></i>
    </a>

    <div class="mt-auto">
        <a href="modules/logout.php" class="text-danger" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-left fs-4"></i>
        </a>
    </div>
</div>

<div class="side-bar p-3">
    <h6 class="text-uppercase x-small fw-bold text-muted mb-4 px-2">Gestión Sistema</h6>
    <nav class="d-flex flex-column">

        <a href="panel_admin.php"
            class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) == 'panel_admin.php' ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill me-2"></i> Dashboard
        </a>

        <a href="admin_auditorios.php"
            class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) == 'admin_auditorios.php' ? 'active' : ''; ?>">
            <i class="bi bi-building-gear me-2"></i> Auditorios
        </a>

        <a href="admin_usuarios.php"
            class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) == 'admin_usuarios.php' ? 'active' : ''; ?>">
            <i class="bi bi-people-fill me-2"></i> Usuarios
        </a>

    </nav>
</div>