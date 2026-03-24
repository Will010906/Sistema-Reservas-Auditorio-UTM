<?php
/**
 * SIDEBAR SIRA - CON CONFIRMACIÓN DE SALIDA
 */
$pagina_actual = basename($_SERVER['PHP_SELF']);
$perfil_usuario = isset($_SESSION['perfil']) ? strtolower($_SESSION['perfil']) : 'alumno'; 
?>

<style>
    :root {
        --sira-purple-dark: #2D1B33;
        --sira-purple-primary: #5B3D66;
        --sira-purple-active: #F4EFFF;
    }
    .activity-bar { background-color: var(--sira-purple-dark); width: 80px; min-height: 100vh; position: fixed; left: 0; top: 0; display: flex; flex-direction: column; align-items: center; padding-top: 25px; z-index: 1001; }
    .side-bar { background-color: #FDFBFF; width: 230px; min-height: 100vh; position: fixed; left: 80px; top: 0; border-right: 1px solid rgba(0,0,0,0.05); padding: 30px 15px; z-index: 1000; }
    .nav-section-title { font-size: 0.65rem; font-weight: 800; color: #adb5bd; text-transform: uppercase; letter-spacing: 1.2px; margin: 20px 0 10px 10px; }
    .nav-link-custom { display: flex; align-items: center; padding: 12px 15px; border-radius: 14px; color: #6c757d; text-decoration: none; font-weight: 600; margin-bottom: 5px; transition: 0.3s; }
    .nav-link-custom i { font-size: 1.1rem; margin-right: 12px; }
    .nav-link-custom.active { background-color: var(--sira-purple-active); color: var(--sira-purple-primary); }
    .nav-link-custom:hover:not(.active) { background-color: #f8f9fa; transform: translateX(5px); }
</style>

<div class="activity-bar">
    <img src="assets/img/logo_app_web_RA.png" style="max-width: 45px;" class="mb-5">
    <div class="d-flex flex-column gap-4">
        <?php $url_home = ($perfil_usuario == 'administrador') ? 'panel_admin.php' : 'panel_usuario.php'; ?>
        <a href="<?php echo $url_home; ?>" class="text-white">
            <i class="bi bi-grid-fill fs-4"></i>
        </a>
        <?php if ($perfil_usuario == 'administrador'): ?>
            <a href="admin_auditorios.php" class="text-white opacity-25"><i class="bi bi-building fs-4"></i></a>
            <a href="admin_usuarios.php" class="text-white opacity-25"><i class="bi bi-people fs-4"></i></a>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: auto; padding-bottom: 30px;">
        <a href="javascript:void(0);" onclick="confirmarSalida()" class="text-white opacity-50" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-left fs-4"></i>
        </a>
    </div>
</div>

<div class="side-bar">
    <div class="nav-section-title">Principal</div>
    <nav>
        <a href="<?php echo $url_home; ?>" class="nav-link-custom <?php echo ($pagina_actual == 'panel_admin.php' || $pagina_actual == 'panel_usuario.php') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </nav>

    <?php if ($perfil_usuario == 'administrador'): ?>
        <div class="nav-section-title">Gestión Sistema</div>
        <nav>
            <a href="admin_auditorios.php" class="nav-link-custom <?php echo ($pagina_actual == 'admin_auditorios.php') ? 'active' : ''; ?>"><i class="bi bi-building"></i> Auditorios</a>
            <a href="admin_usuarios.php" class="nav-link-custom <?php echo ($pagina_actual == 'admin_usuarios.php') ? 'active' : ''; ?>"><i class="bi bi-people"></i> Usuarios</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-calendar3"></i> Calendario</a>
        </nav>
        <div class="nav-section-title">Reportes y Ayuda</div>
        <nav>
            <a href="#" class="nav-link-custom"><i class="bi bi-file-earmark-pdf"></i> Reportes PDF</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-question-circle"></i> Soporte</a>
        </nav>
    <?php else: ?>
        <div class="nav-section-title">Mi Cuenta</div>
        <nav>
            <a href="panel_usuario.php" class="nav-link-custom <?php echo ($pagina_actual == 'panel_usuario.php') ? 'active' : ''; ?>"><i class="bi bi-journal-text"></i> Mis Reservas</a>
        </nav>
        <div class="nav-section-title">Reportes y Ayuda</div>
        <nav>
            <a href="#" class="nav-link-custom"><i class="bi bi-journal-text"></i> Manual Usuario</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-question-circle"></i> Soporte</a>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="assets/js/auth_check.js"></script>