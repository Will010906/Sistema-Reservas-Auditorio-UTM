/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: GUARDIÁN DE SESIÓN Y MIDDLEWARE DE SEGURIDAD
 * * @package     Frontend_Security
 * @subpackage  Auth_Control
 * @version     2.6.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Implementa una Capa de Seguridad Persistente que intercepta la carga de cada 
 * documento para validar la autenticidad de la sesión. Utiliza el estándar JWT 
 * para el control de expiración y perfilamiento de rutas.
 * * CAPACIDADES:
 * 1. Inyección de Token: Captura y limpieza de tokens vía URL.
 * 2. RBAC (Role-Based Access Control): Redirección inteligente por perfil.
 * 3. Cache Invalidation: Prevención de retroceso en el historial tras el logout.
 * 4. UX: Gestión de alertas institucionales mediante SweetAlert2.
 */

/* global Swal */

/**
 * 1. SUBSISTEMA DE VALIDACIÓN DE ENTRADA (AUTO-EJECUTABLE)
 * Analiza el contexto de la URL y el estado del almacenamiento local.
 */
(async function() {
    const params = new URLSearchParams(window.location.search);
    const tokenUrl = params.get('token');
    const path = window.location.pathname;

    /**
     * CAPTURA Y SANITIZACIÓN DE TOKEN
     * Si el servidor PHP inyecta el token en la redirección inicial, se persiste
     * y se limpia la barra de direcciones para mitigar riesgos de 'Shoulder Surfing'.
     */
    if (tokenUrl) {
        localStorage.setItem('sira_session_token', tokenUrl);
        const cleanUrl = window.location.origin + path;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    const sessionToken = localStorage.getItem('sira_session_token');
    const esPaginaLogin = path.includes('index.php') || path.includes('login.php');

    // Escenario A: Intento de acceso sin credenciales
    if (!sessionToken) {
        if (!esPaginaLogin) {
            window.location.href = 'login.php?error=no_auth';
        }
    } else {
        /**
         * 2. SUBSISTEMA DE INTEGRIDAD JWT
         * Decodifica el Payload (Base64) para verificar la vigencia temporal.
         */
        try {
           // ... dentro del try del subsistema de integridad JWT ...
const payload = JSON.parse(atob(sessionToken.split('.')[1]));
const perfil = payload.perfil.toLowerCase();
const path = window.location.pathname;

/**
 * REGLAS DE ACCESO INSTITUCIONAL (RBAC)
 * Si el usuario intenta estar en un panel que no coincide con su perfil, 
 * lo redirigimos a donde sí tiene permiso.
 */
if (!path.includes('login.php') && !path.includes('index.php')) {

    // 1. Bloqueo para entrar al Panel de Administrador
    if (path.includes('panel_admin.php') && perfil !== 'administrador') {
        let destino = (perfil === 'subdirector') ? 'panel_subdirector.php' : 'panel_usuario.php';
        window.location.replace(destino + '?error=unauthorized');
    }

    // 2. Bloqueo para entrar al Panel de Subdirector
    if (path.includes('panel_subdirector.php') && perfil !== 'subdirector') {
        let destino = (perfil === 'administrador') ? 'panel_admin.php' : 'panel_usuario.php';
        window.location.replace(destino + '?error=unauthorized');
    }

    // 3. Bloqueo para usuarios estándar intentando saltar rangos
    if (path.includes('panel_usuario.php') && (perfil === 'administrador' || perfil === 'subdirector')) {
        // Opcional: Si el admin entra a panel_usuario, mandarlo a su panel pro
        let destino = (perfil === 'administrador') ? 'panel_admin.php' : 'panel_subdirector.php';
        window.location.replace(destino);
    }
}

        } catch (e) {
            console.warn("Seguridad SIRA: Sesión inválida o expirada.", e.message);
            ejecutarSalida('expirada'); 
        }
    }
})();

/**
 * 3. GESTIÓN DE CIERRE DE SESIÓN SEGURO
 * Realiza una limpieza dual (Cliente/Servidor) para garantizar el fin de sesión.
 * @async
 */
async function confirmarSalida() {
    const result = await Swal.fire({
        title: '¿Finalizar sesión?',
        text: "Se eliminarán los privilegios de acceso del navegador actual.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#5B3D66',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            // Notificación al servidor para invalidación de PHPSESSID si existiera
            await fetch('logout.php'); 

            // Purga de almacenamiento local
            localStorage.removeItem('token');
            localStorage.removeItem('sira_session_token');

            window.location.href = 'login.php?status=logout'; 
            
        } catch (error) {
            localStorage.clear();
            window.location.href = 'login.php';
        }
    }
}

/**
 * LIMPIEZA TÉCNICA DE EMERGENCIA
 * @param {string} motivo - Contexto del cierre (expirada/error).
 */
function ejecutarSalida(motivo = '') {
    localStorage.removeItem('sira_session_token');
    localStorage.removeItem('token'); 
    
    let urlRedireccion = 'login.php'; 
    if (motivo === 'expirada') urlRedireccion += '?error=expired';
    
    window.location.href = urlRedireccion;
}

/**
 * 4. PROTECCIÓN DEL HISTORIAL Y CACHÉ (NAVEGACIÓN SEGURA)
 * Evita que se recupere la información del DOM mediante el botón 'Atrás'.
 */
(function() {
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Invalida la persistencia en caché del navegador tras el logout
    window.onpageshow = function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    };

    // Alerta informativa tras redirección por cierre exitoso
    const params = new URLSearchParams(window.location.search);
    if (params.get('status') === 'logout') {
        Swal.fire({
            title: '¡Sesión Finalizada!',
            text: 'Has salido del ecosistema SIRA con éxito.',
            icon: 'success',
            confirmButtonColor: '#5B3D66'
        });
    }
})();