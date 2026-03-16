<?php
/**
 * COMPONENTE DE NAVEGACIÓN LATERAL (SIDEBAR) - SIRA
 * Adaptado a la paleta de colores Púrpura/Lila
 */
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --sira-purple-dark: #714B75;
        --sira-purple-light: #F4EFFF;
        --sira-text-muted: #8E7A91;
    }

    /* Barra de iconos delgada */
    .activity-bar {
        background-color: var(--sira-purple-dark);
        width: 70px;
        min-height: 100vh;
        z-index: 1001;
    }

    /* Barra de navegación con texto */
    .side-bar {
        background-color: white;
        width: 200px;
        min-height: 100vh;
        border-right: 1px solid rgba(113, 75, 117, 0.1);
    }

    .nav-link-custom {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-radius: 12px;
        color: var(--sira-text-muted);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .nav-link-custom:hover {
        background-color: var(--sira-purple-light);
        color: var(--sira-purple-dark);
    }

    .nav-link-custom.active {
        background-color: var(--sira-purple-light);
        color: var(--sira-purple-dark);
        box-shadow: inset 4px 0 0 var(--sira-purple-dark);
    }

    .activity-bar a i {
        transition: transform 0.2s;
    }

    .activity-bar a:hover i {
        transform: scale(1.2);
    }
</style>

<div class="activity-bar d-flex flex-column align-items-center py-4">
    <div class="mb-5 text-white">
        <i class="bi bi-shield-check fs-2"></i>
    </div>

    <a href="panel_admin.php"
        class="text-white mb-4 <?php echo $pagina_actual == 'panel_admin.php' ? 'opacity-100' : 'opacity-50'; ?>"
        title="Dashboard">
        <i class="bi bi-grid-fill fs-4"></i>
    </a>

    <a href="admin_auditorios.php"
        class="text-white mb-4 <?php echo $pagina_actual == 'admin_auditorios.php' ? 'opacity-100' : 'opacity-50'; ?>"
        title="Auditorios">
        <i class="bi bi-building fs-4"></i>
    </a>

    <a href="admin_usuarios.php"
        class="text-white mb-4 <?php echo $pagina_actual == 'admin_usuarios.php' ? 'opacity-100' : 'opacity-50'; ?>"
        title="Usuarios">
        <i class="bi bi-people fs-4"></i>
    </a>

    <div class="mt-auto pb-4">
        <a href="modules/logout.php" class="text-white opacity-75 hover-opacity-100" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-left fs-4"></i>
        </a>
    </div>
</div>

<div class="side-bar p-3">
    <h6 class="text-uppercase fw-bold mb-4 px-2" style="font-size: 0.65rem; color: var(--sira-purple-dark); letter-spacing: 1px;">Gestión Sistema</h6>
    <nav class="d-flex flex-column">

        <a href="panel_admin.php"
            class="nav-link-custom <?php echo $pagina_actual == 'panel_admin.php' ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill me-2"></i> Dashboard
        </a>

        <a href="admin_auditorios.php"
            class="nav-link-custom <?php echo $pagina_actual == 'admin_auditorios.php' ? 'active' : ''; ?>">
            <i class="bi bi-building-gear me-2"></i> Auditorios
        </a>

        <a href="admin_usuarios.php"
            class="nav-link-custom <?php echo $pagina_actual == 'admin_usuarios.php' ? 'active' : ''; ?>">
            <i class="bi bi-people-fill me-2"></i> Usuarios
        </a>

    </nav>
</div>