<?php
/**
 * SIDEBAR SIRA - UTM
 * Versión Híbrida: Estructura PHP + Lógica Dinámica por JS.
 */
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --sira-purple-dark: #2D1B33;
        --sira-purple-primary: #5B3D66;
        --sira-purple-active: #F4EFFF;
    }
    /* Mantenemos tus estilos originales */
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
    <div class="d-flex flex-column gap-4 text-center" id="iconosRapidos">
        </div>
    
    <div style="margin-top: auto; padding-bottom: 30px;">
        <a href="javascript:void(0);" onclick="confirmarSalida()" class="text-white opacity-50" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-left fs-4"></i>
        </a>
    </div>
</div>

<div class="side-bar">
    <div id="menuDinamico">
        <div class="text-center py-5">
            <div class="spinner-border spinner-border-sm text-muted"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const token = localStorage.getItem('sira_session_token');
    if (!token) return;

    // Decodificamos el perfil del Token (JWT)
    const payload = JSON.parse(atob(token.split('.')[1]));
    const perfil = payload.perfil.toLowerCase();
    const menu = document.getElementById('menuDinamico');
    const iconos = document.getElementById('iconosRapidos');
    const pagina = "<?php echo $pagina_actual; ?>";

    let htmlMenu = `<div class="nav-section-title">Principal</div>`;
    let htmlIconos = '';

    if (perfil === 'administrador') {
        // --- VISTA ADMIN (Limpia) ---
        htmlIconos = `
            <a href="panel_admin.php" class="text-white"><i class="bi bi-grid-fill fs-4"></i></a>
            <a href="admin_auditorios.php" class="text-white opacity-50"><i class="bi bi-building fs-4"></i></a>
            <a href="admin_usuarios.php" class="text-white opacity-50"><i class="bi bi-people fs-4"></i></a>`;
        
        htmlMenu += `
            <a href="panel_admin.php" class="nav-link-custom ${pagina==='panel_admin.php'?'active':''}"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <div class="nav-section-title">Gestión Sistema</div>
            <a href="admin_auditorios.php" class="nav-link-custom ${pagina==='admin_auditorios.php'?'active':''}"><i class="bi bi-building"></i> Auditorios</a>
            <a href="admin_usuarios.php" class="nav-link-custom ${pagina==='admin_usuarios.php'?'active':''}"><i class="bi bi-people"></i> Usuarios</a>`;
    } else {
        // --- VISTA ALUMNO (Limpia) ---
        htmlIconos = `<a href="panel_usuario.php" class="text-white"><i class="bi bi-grid-fill fs-4"></i></a>`;
        
        htmlMenu += `
            <a href="panel_usuario.php" class="nav-link-custom ${pagina==='panel_usuario.php'?'active':''}"><i class="bi bi-journal-text"></i> Mis Reservas</a>`;
    }

    // --- SECCIÓN REQUERIDA POR LA MAESTRA ---
    htmlMenu += `
        <div class="nav-section-title">Sesión</div>
        <a href="javascript:void(0);" onclick="confirmarSalida()" class="nav-link-custom nav-link-exit">
            <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
        </a>`;
    
    menu.innerHTML = htmlMenu;
    iconos.innerHTML = htmlIconos;
});

// Función de salida limpia
function confirmarSalida() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#5B3D66',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.removeItem('sira_session_token');
            window.location.href = 'login.php';
        }
    });
}
</script>