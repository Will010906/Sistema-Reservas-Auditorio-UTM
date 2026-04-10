/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: CONTROLADOR DE SOLICITUD DE RESTABLECIMIENTO DE CONTRASEÑA
 * * @package     Frontend_Security
 * @subpackage  Recovery_Logic
 * @version     1.0.3
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Orquestador del flujo de recuperación de cuenta. Gestiona la solicitud de 
 * tokens de seguridad mediante la validación del correo electrónico institucional. 
 * Implementa el patrón Async/Await para el consumo de la API de autenticación.
 * * FLUJO DE SEGURIDAD:
 * 1. Captura: Obtiene el correo electrónico del usuario.
 * 2. Validación: Solicita al servidor verificar la existencia de la cuenta.
 * 3. Tokenización: Recibe un token temporal para el proceso de reset.
 */

$(document).ready(function () {
    /**
     * ESCUCHADOR DE EVENTO 'SUBMIT'
     * Intercepta el envío del formulario para procesarlo mediante fetch asíncrono.
     */
    $('#formRecuperar').on('submit', async function (e) {
        e.preventDefault(); 
        
        const email = $('#email_recuperar').val();
        
        // Retroalimentación visual: Bloqueo de interfaz durante la búsqueda
        Swal.fire({
            title: 'Buscando cuenta...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            /**
             * PETICIÓN AL MICROSERVICIO DE SEGURIDAD
             * Envía el correo al endpoint para generar la entrada en la tabla de reset.
             */
            const response = await fetch('api/auth/solicitar_reset.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ correo: email })
            });

            const data = await response.json();

            if (data.success) {
                /**
                 * FASE DE ÉXITO: GENERACIÓN DE TOKEN
                 * En entorno de producción, este paso dispara el envío de correos SMTP.
                 */
                Swal.fire({
                    icon: 'success',
                    title: '¡Proceso Iniciado!',
                    text: 'Se ha generado un token de seguridad en la base de datos institucional.',
                    confirmButtonColor: '#5B3D66'
                });
                
                // Registro de depuración para validación técnica en entorno local
                console.log("SIRA-DEBUG: Link de restablecimiento -> restablecer.php?token=" + data.token);
                
            } else {
                // Notificación de error si el correo no existe en la base de datos
                Swal.fire('Error de Verificación', data.error, 'error');
            }
        } catch (error) {
            // Manejo de excepciones de red o fallos en el núcleo PHP
            Swal.fire('Error Crítico', 'Fallo de conexión con el servidor de autenticación.', 'error');
        }
    });
});