/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: CONTROLADOR DE ACCESO Y AUTENTICACIÓN (LOGIN)
 * * @package     Frontend_Security
 * @subpackage  Auth_Gateway
 * @version     2.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Orquestador del flujo de inicio de sesión. Gestiona la captura de credenciales,
 * la validación de sintaxis institucional y la comunicación con el servicio 
 * de autenticación mediante el protocolo HTTP POST y JSON.
 * * SEGURIDAD Y UX:
 * 1. Validación Regex: Filtra formatos de matrícula UTM antes del envío.
 * 2. Feedback de Carga: Implementa estados de espera (Spinners) en tiempo real.
 * 3. Token Persistence: Almacena el JWT bajo la clave 'sira_session_token'.
 * 4. Sanitización: Limpia parámetros de error en la URL para evitar redundancia.
 */

/* global Swal */

/**
 * 1. INICIALIZACIÓN DEL COMPONENTE DE ACCESO
 */
document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const btnEntrar = document.getElementById("btnEntrar");

    if (loginForm) {
        /**
         * MANEJADOR DE EVENTO SUBMIT
         * Implementa el patrón Async/Await para evitar el bloqueo del hilo principal.
         */
        loginForm.addEventListener("submit", async function (e) {
            e.preventDefault(); 

            // Recuperación y saneamiento de entradas
            const matricula = document.getElementById("matricula").value.trim();
            const password = document.getElementById("password").value.trim();

            /**
             * 2. SUBSISTEMA DE VALIDACIÓN PREVENTIVA (CLIENT-SIDE)
             * Minimiza las peticiones innecesarias al servidor validando formatos locales.
             */
            if (matricula === "" || password === "") {
                return mostrarAlerta('warning', 'Campos incompletos', 'Llena ambos campos para acceder.');
            }

            // Regla de negocio: Validación de matrícula UTM (Patrón institucional)
            const regexMatricula = /^(UTM\d{6}[A-Z]{2,4}|\d{2,5})$/i;
            if (!regexMatricula.test(matricula)) {
                return mostrarAlerta('info', 'Formato incorrecto', 'Ingresa una matrícula válida (UTM...) o ID.');
            }

            /**
             * 3. PROCESO DE AUTENTICACIÓN ASÍNCRONO (CORE)
             * Realiza el envío cifrado (si se usa HTTPS) de credenciales al núcleo PHP.
             */
            btnEntrar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validando...';
            btnEntrar.disabled = true;

            try {
                // Comunicación con el microservicio de autenticación
                const response = await fetch('api/auth/autenticacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ matricula, password })
                });

                const data = await response.json(); 

                if (data.success) {
                    /**
                     * 4. GESTIÓN DE PERSISTENCIA (TOKEN MANAGEMENT)
                     * Se establece el JWT como el identificador único de sesión en el navegador.
                     */
                    localStorage.setItem('sira_session_token', data.token); 
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: 'Acceso concedido exitosamente.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        /**
                         * REDIRECCIÓN INTELIGENTE
                         * El destino se determina en el servidor según el perfil del usuario (RBAC).
                         */
                        window.location.href = data.redirect; 
                    });
                } else {
                    mostrarAlerta('error', 'Acceso denegado', data.error || 'Credenciales incorrectas.');
                    resetBoton(btnEntrar);
                }
            } catch (error) {
                console.error("Fallo de comunicación con la API:", error);
                mostrarAlerta('error', 'Error técnico', 'No se pudo conectar con el núcleo del sistema.');
                resetBoton(btnEntrar);
            }
        });
    }

    limpiarMensajesURL();
});

/**
 * 5. UTILIDADES DE INTERFAZ Y MANTENIMIENTO
 */
function mostrarAlerta(icon, title, text) {
    Swal.fire({ icon, title, text, confirmButtonColor: '#5B3D66' });
}

function resetBoton(btn) {
    btn.innerHTML = 'Entrar';
    btn.disabled = false;
}

/**
 * Limpia los parámetros de estado en la URL para mantener una estética limpia 
 * y evitar re-ejecución de alertas por recarga de página.
 */
function limpiarMensajesURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'logout' || urlParams.get('error')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}