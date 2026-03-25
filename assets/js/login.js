/**
 * LÓGICA DE ACCESO SIRA UTM - NIVEL TSU
 * Estado: Producción Segura (JWT + Async/Await)
 * Ajuste: Rutas modulares y sincronización con Guardián de Sesión.
 */

/* global Swal */

document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const btnEntrar = document.getElementById("btnEntrar");

    if (loginForm) {
        loginForm.addEventListener("submit", async function (e) {
            e.preventDefault(); 

            const matricula = document.getElementById("matricula").value.trim();
            const password = document.getElementById("password").value.trim();

            // 1. Validaciones de Formato (UX Evaluada)
            if (matricula === "" || password === "") {
                return mostrarAlerta('warning', 'Campos incompletos', 'Llena ambos campos para acceder.');
            }

            // Expresión regular para matrículas UTM o IDs cortos
            const regexMatricula = /^(UTM\d{6}[A-Z]{2,4}|\d{2,5})$/i;
            if (!regexMatricula.test(matricula)) {
                return mostrarAlerta('info', 'Formato incorrecto', 'Ingresa una matrícula válida (UTM...) o ID.');
            }

            // 2. PROCESO DE AUTENTICACIÓN ASÍNCRONO
            btnEntrar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validando...';
            btnEntrar.disabled = true;

            try {
                // CORRECCIÓN: Ruta actualizada a la subcarpeta 'auth'
                const response = await fetch('api/auth/autenticacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ matricula, password })
                });

                const data = await response.json(); 

                if (data.success) {
                    // 3. GUARDADO DE IDENTIDAD (Punto Crítico de Seguridad)
                    // CORRECCIÓN: Nombre exacto para que auth_check.js lo valide
                    localStorage.setItem('sira_session_token', data.token); 
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: 'Acceso concedido exitosamente.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // El servidor decide a qué panel enviarte según tu perfil
                        window.location.href = data.redirect; 
                    });
                } else {
                    mostrarAlerta('error', 'Acceso denegado', data.error || 'Credenciales incorrectas.');
                    resetBoton(btnEntrar);
                }
            } catch (error) {
                console.error("Error en Fetch:", error);
                mostrarAlerta('error', 'Error técnico', 'No se pudo conectar con el núcleo del sistema.');
                resetBoton(btnEntrar);
            }
        });
    }

    limpiarMensajesURL();
});

/**
 * FUNCIONES AUXILIARES DE INTERFAZ
 */
function mostrarAlerta(icon, title, text) {
    Swal.fire({ icon, title, text, confirmButtonColor: '#5B3D66' });
}

function resetBoton(btn) {
    btn.innerHTML = 'Entrar';
    btn.disabled = false;
}

/**
 * Limpia los parámetros de la URL después del cierre de sesión
 */
function limpiarMensajesURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'logout' || urlParams.get('error')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

