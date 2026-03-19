/**
 * GUARDIÁN DE SESIÓN - SIRA UTM
 * Verifica el token en LocalStorage y protege la ruta.
 */
(function() {
    const params = new URLSearchParams(window.location.search);
    const tokenUrl = params.get('token');

    // 1. Si el token viene de la redirección de PHP, lo guardamos
    if (tokenUrl) {
        localStorage.setItem('sira_session_token', tokenUrl);
        
        // Limpiamos la URL (quita el "?token=...") para que se vea estético
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
    }

    // 2. Verificación de seguridad
    const sessionToken = localStorage.getItem('sira_session_token');

    if (!sessionToken) {
        // Si no hay token, el usuario es un intruso: ¡Fuera!
        console.warn("Acceso denegado: No se encontró token de sesión.");
        window.location.href = 'login.php?error=no_auth';
    }
})();