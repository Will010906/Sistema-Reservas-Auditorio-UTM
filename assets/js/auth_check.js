/**
 * GUARDIÁN DE SESIÓN - SIRA UTM (Nivel TSU)
 * Implementa: Verificación de JWT, Expiración y Seguridad en Rutas.
 */

/* global Swal */

(async function() {
    const params = new URLSearchParams(window.location.search);
    const tokenUrl = params.get('token');
    const path = window.location.pathname;

    // 1. CAPTURA: Si el token viene de la redirección inicial de PHP
    if (tokenUrl) {
        localStorage.setItem('sira_session_token', tokenUrl);
        // Limpiamos la URL para que el token no sea visible (Seguridad)
        const cleanUrl = window.location.origin + path;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    const sessionToken = localStorage.getItem('sira_session_token');

    // 2. PROTECCIÓN DE RUTAS: ¿Quién tiene permitido entrar?
    const esPaginaLogin = path.includes('index.php') || path.includes('login.php');

    if (!sessionToken) {
        if (!esPaginaLogin) {
            // Si no hay token y no está en login, expulsión inmediata
            window.location.href = 'login.php?error=no_auth';
        }
    } else {
        // 3. VALIDACIÓN TSU: ¿El token sigue siendo válido y no ha expirado? 
        try {
            // Decodificamos el Payload del JWT (Base64) para ver la expiración y perfil
            const payload = JSON.parse(atob(sessionToken.split('.')[1]));
            const ahora = Math.floor(Date.now() / 1000);

            if (payload.exp < ahora) {
                throw new Error("Token expirado"); // El token ya cumplió su tiempo
            }

            // MEJORA: Redirección inteligente si intenta entrar al Login ya estando logueado
           if (esPaginaLogin) {
            const perfil = payload.perfil.toLowerCase();
            let destino = 'panel_usuario.php'; // Por defecto

            if (perfil === 'administrador') {
                destino = 'panel_admin.php';
            } else if (perfil === 'subdirector') {
                destino = 'panel_subdirector.php';
            }
            
            window.location.href = destino;
        }

        } catch (e) {
            console.warn("Sesión inválida o expirada:", e.message);
            ejecutarSalida('expirada'); // Limpieza por seguridad
        }
    }
})();

/**
 * CIERRE DE SESIÓN SEGURO (UX Mejorada)
 */
// --- FUNCIÓN GLOBAL DE SALIDA (Añadir al final de admin_usuarios.js) ---
async function confirmarSalida() {
    const result = await Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Se eliminará tu acceso actual del navegador.",
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
            // 1. Avisamos al servidor que cierre la sesión (PHP)
            await fetch('logout.php'); 

            // 2. Limpiamos los tokens locales del navegador
            localStorage.removeItem('token');
            localStorage.removeItem('sira_session_token');

            // 3. Redirigimos manualmente al login/index
            window.location.href = 'login.php?status=logout'; 
            
        } catch (error) {
            // Si algo falla, igual limpiamos el local y salimos
            localStorage.clear();
            window.location.href = 'login.php';
        }
    }
}

/**
 * LIMPIEZA TOTAL (Frontend + Backend)
 */
// En auth_check.js, busca la función ejecutarSalida
function ejecutarSalida(motivo = '') {
    localStorage.removeItem('sira_session_token');
    localStorage.removeItem('token'); // Borramos ambos por si acaso
    
    // Si tu login es index.php, déjalo así. Si es login.php, cámbialo:
    let urlRedireccion = 'login.php'; 
    if (motivo === 'expirada') urlRedireccion += '?error=expired';
    
    window.location.href = urlRedireccion;
}
/**
 * PROTECCIÓN DE HISTORIAL - SIRA UTM
 * Evita que el usuario regrese a la sesión tras cerrar sesión.
 */
(function() {
    // 1. Reemplazamos el estado actual para que el "atrás" sea el mismo Login
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // 2. Detectamos si la página se carga desde el "caché" (flecha atrás)
    window.onpageshow = function(event) {
        if (event.persisted) {
            // Si el navegador intenta mostrar una copia guardada, forzamos recarga
            window.location.reload();
        }
    };

    // 3. Opcional: Alerta de bienvenida si viene de un logout
    const params = new URLSearchParams(window.location.search);
    if (params.get('status') === 'logout') {
        Swal.fire({
            title: '¡Sesión Finalizada!',
            text: 'Has salido del sistema con éxito.',
            icon: 'success',
            confirmButtonColor: '#5B3D66'
        });
    }
})();