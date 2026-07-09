<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: NAVEGACIÓN PERIMETRAL (SIDEBAR & ACTIVITY BAR)
 * * @package     Frontend_Layout
 * @subpackage  Navigation_UI
 * @author      Wilmer (Estudiante de Tecnologías de la Información, UTM)
 * @version     2.2.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Implementa una interfaz de navegación de doble columna (Versión Híbrida).
 * 1. Activity Bar: Columna de acceso rápido y branding institucional.
 * 2. Side Bar: Menú expandido con renderizado dinámico basado en Claims de JWT.
 * * LÓGICA DE SEGURIDAD:
 * Utiliza RBAC (Role-Based Access Control) mediante la decodificación del token 
 * en tiempo de ejecución para ocultar/mostrar módulos administrativos.
 */
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --sira-purple-dark: #2D1B33;
        --sira-purple-primary: #5B3D66;
        --sira-purple-active: #F4EFFF;
    }

    /* SISTEMA DE POSICIONAMIENTO FIJO */
    .activity-bar { 
        background-color: var(--sira-purple-dark); 
        width: 80px; 
        min-height: 100vh; 
        position: fixed; 
        left: 0; 
        top: 0; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        padding-top: 25px; 
        z-index: 1001; 
        transition: all 0.3s ease;
    }

    .side-bar { 
        background-color: #FDFBFF; 
        width: 230px; 
        min-height: 100vh; 
        position: fixed; 
        left: 80px; 
        top: 0; 
        border-right: 1px solid rgba(0,0,0,0.05); 
        padding: 30px 15px; 
        z-index: 1000; 
        transition: all 0.3s ease;
    }

    .nav-section-title { 
        font-size: 0.65rem; 
        font-weight: 800; 
        color: #adb5bd; 
        text-transform: uppercase; 
        letter-spacing: 1.2px; 
        margin: 20px 0 10px 10px; 
    }

    .nav-link-custom { 
        display: flex; 
        align-items: center; 
        padding: 12px 15px; 
        border-radius: 14px; 
        color: #6c757d; 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 5px; 
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
    }

    .nav-link-custom i { font-size: 1.1rem; margin-right: 12px; }

    .nav-link-custom.active { 
        background-color: var(--sira-purple-active); 
        color: var(--sira-purple-primary); 
    }

    .nav-link-custom:hover:not(.active) { 
        background-color: #f8f9fa; 
        transform: translateX(5px); 
    }

    /* 📱 AJUSTES CRÍTICOS PARA MÓVIL (Resuelve el solapamiento) */
    .sidebar-toggle-mobile { display: none; }
    .sidebar-backdrop { display: none; }

    @media (max-width: 768px) {
        /* 1. La barra de textos se convierte en un panel deslizable (drawer) */
        .side-bar {
            display: block;
            left: 0;
            width: 220px;
            max-width: 80vw;
            z-index: 1002;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .side-bar.mobile-open {
            transform: translateX(0);
            box-shadow: 8px 0 30px rgba(0,0,0,0.15);
        }

        /* 2. Reducimos la activity-bar a una franja muy delgada o iconos pequeños */
        .activity-bar {
            width: 60px;
        }

        /* 3. Empujamos el contenido principal solo lo necesario */
        .main-content {
            margin-left: 60px !important;
            width: calc(100% - 60px) !important;
        }

        /* 4. Botón hamburguesa para abrir el drawer de navegación */
        .sidebar-toggle-mobile {
            display: flex;
            position: fixed;
            top: 14px;
            right: 14px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--sira-purple-dark);
            color: white;
            border: none;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1003;
        }

        /* 5. Fondo oscuro detrás del drawer, para cerrarlo al tocar fuera */
        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1001;
        }

        .sidebar-backdrop.active { display: block; }
    }
</style>

<button type="button" class="sidebar-toggle-mobile" id="btnToggleSidebar" aria-label="Abrir menú">
    <i class="bi bi-list"></i>
</button>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="activity-bar">
    <img src="assets/img/logo_app_web_RA.png" style="max-width: 45px;" class="mb-5" alt="Logo SIRA">
    <div class="d-flex flex-column gap-4 text-center" id="iconosRapidos">
        </div>

    <div style="margin-top: auto; padding-bottom: 30px;">
        <a href="javascript:void(0);" onclick="confirmarSalida()" class="text-white opacity-50" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-left fs-4"></i>
        </a>
    </div>
</div>

<div class="side-bar" id="sideBar">
    <div id="menuDinamico">
        <div class="text-center py-5">
            <div class="spinner-border spinner-border-sm text-muted"></div>
        </div>
    </div>
</div>

<script>
/**
 * MOTOR DE RENDERIZADO DE NAVEGACIÓN
 * Procesa el JWT para determinar los privilegios de visualización.
 */
document.addEventListener("DOMContentLoaded", function() {
    const token = localStorage.getItem('sira_session_token');
    if (!token) return;

    try {
        // DECODIFICACIÓN DE PAYLOAD (Protocolo JWT)
        const payload = JSON.parse(atob(token.split('.')[1]));
        const perfil = payload.perfil.toLowerCase();
        
        const menu = document.getElementById('menuDinamico');
        const iconos = document.getElementById('iconosRapidos');
        const pagina = "<?php echo $pagina_actual; ?>";

        let htmlMenu = `<div class="nav-section-title">Principal</div>`;
        let htmlIconos = '';

        /**
         * LÓGICA DE PERFILAMIENTO (RBAC)
         */
        if (perfil === 'administrador') {
            // VISTA ADMINISTRATIVA: Módulos de gestión institucional
            htmlIconos = `
                <a href="panel_admin.php" title="Dashboard" class="text-white ${pagina==='panel_admin.php'?'':'opacity-50'}"><i class="bi bi-grid-fill fs-4"></i></a>
                <a href="admin_auditorios.php" title="Auditorios" class="text-white ${pagina==='admin_auditorios.php'?'':'opacity-50'}"><i class="bi bi-building fs-4"></i></a>
                <a href="admin_usuarios.php" title="Usuarios" class="text-white ${pagina==='admin_usuarios.php'?'':'opacity-50'}"><i class="bi bi-people fs-4"></i></a>`;
            
            htmlMenu += `
                <a href="panel_admin.php" class="nav-link-custom ${pagina==='panel_admin.php'?'active':''}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <div class="nav-section-title">Gestión Sistema</div>
                <a href="admin_auditorios.php" class="nav-link-custom ${pagina==='admin_auditorios.php'?'active':''}"><i class="bi bi-building"></i> Auditorios</a>
                <a href="admin_usuarios.php" class="nav-link-custom ${pagina==='admin_usuarios.php'?'active':''}"><i class="bi bi-people"></i> Usuarios</a>`;
        } else {
            // VISTA ESTÁNDAR: Enfocada en operatividad de usuario
            htmlIconos = `<a href="panel_usuario.php" title="Mis Reservas" class="text-white"><i class="bi bi-grid-fill fs-4"></i></a>`;
            
            htmlMenu += `
                <a href="panel_usuario.php" class="nav-link-custom ${pagina==='panel_usuario.php'?'active':''}"><i class="bi bi-journal-text"></i> Mis Reservas</a>`;
        }

        // SECCIÓN TRANSVERSAL: Control de Sesión
        htmlMenu += `
            <div class="nav-section-title">Sesión</div>
            <a href="javascript:void(0);" onclick="confirmarSalida()" class="nav-link-custom nav-link-exit">
                <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
            </a>`;
        
        menu.innerHTML = htmlMenu;
        iconos.innerHTML = htmlIconos;

    } catch (e) {
        console.error("Fallo de integridad en el componente de navegación:", e);
    }
});

/**
 * DRAWER DE NAVEGACIÓN MÓVIL
 * Controla la apertura/cierre del menú de texto en pantallas pequeñas.
 */
document.addEventListener("DOMContentLoaded", function() {
    const sideBar = document.getElementById('sideBar');
    const btnToggle = document.getElementById('btnToggleSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    function cerrarDrawer() {
        sideBar.classList.remove('mobile-open');
        backdrop.classList.remove('active');
    }

    btnToggle?.addEventListener('click', function() {
        sideBar.classList.toggle('mobile-open');
        backdrop.classList.toggle('active');
    });

    backdrop?.addEventListener('click', cerrarDrawer);

    // Cierra el drawer al seleccionar una opción del menú
    sideBar?.addEventListener('click', function(e) {
        if (e.target.closest('a')) cerrarDrawer();
    });
});

/**
 * CIERRE DE SESIÓN SEGURO
 * Implementa limpieza de persistencia local y redirección al punto de acceso.
 */
function confirmarSalida() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Su sesión de acceso al SIRA finalizará.",
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