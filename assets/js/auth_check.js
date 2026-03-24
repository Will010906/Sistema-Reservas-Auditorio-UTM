/**
 * GUARDIÁN DE SESIÓN - SIRA UTM
 * Verifica el token en LocalStorage y gestiona la salida segura.
 */
(function() {
    const params = new URLSearchParams(window.location.search);
    const tokenUrl = params.get('token');

    // 1. Si el token viene de la redirección de PHP, lo guardamos
    if (tokenUrl) {
        localStorage.setItem('sira_session_token', tokenUrl);
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
    }

    // 2. Verificación de seguridad
    const sessionToken = localStorage.getItem('sira_session_token');
    if (!sessionToken && !window.location.pathname.includes('login.php')) {
        window.location.href = 'login.php?error=no_auth';
    }
})();

/**
 * FUNCIÓN GLOBAL DE SALIDA
 * Limpia el token y cierra la sesión en el servidor.
 */
function confirmarSalida() {
    if (typeof Swal === 'undefined') {
        if (confirm("¿Cerrar sesión?")) ejecutarSalida();
        return;
    }

    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Se eliminará tu acceso temporal en este navegador.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#5B3D66',
        cancelButtonColor: '#adb5bd',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarSalida();
        }
    });
}

function ejecutarSalida() {
    // 1. Borramos el token del navegador (Vital para la seguridad)
    localStorage.removeItem('sira_session_token');
    
    // 2. Redirigimos al logout de PHP
    // Usamos una ruta relativa a la raíz para que funcione desde cualquier carpeta
    window.location.href = 'modules/logout.php';
}