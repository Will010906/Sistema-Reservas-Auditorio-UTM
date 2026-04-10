/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: CONTROLADOR DE ACTUALIZACIÓN DE CREDENCIALES (PASSWORD RESET)
 * * @package     Frontend_Security
 * @subpackage  Recovery_Logic
 * @version     1.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Orquestador de la fase final de recuperación de cuenta. Valida la paridad de 
 * las nuevas credenciales y consume el microservicio de actualización mediante 
 * el protocolo HTTP POST. 
 * * SEGURIDAD Y VALIDACIÓN:
 * 1. Client-Side Validation: Verifica la coincidencia de campos y longitud mínima.
 * 2. Token-Based Auth: Utiliza un token único para autorizar el cambio en la DB.
 * 3. UX: Implementa estados de carga y notificaciones reactivas con SweetAlert2.
 */

$(document).ready(function () {
    /**
     * ESCUCHADOR DE EVENTO 'SUBMIT'
     * Intercepta la petición para aplicar lógica de validación antes del envío.
     */
    $('#formNuevaPass').on('submit', async function (e) {
        e.preventDefault();
        
        // Extracción de datos del DOM institucional
        const p1 = $('#pass1').val();
        const p2 = $('#pass2').val();
        const token = $('#token_reset').val();

        /**
         * 1. MOTOR DE VALIDACIÓN DE INTEGRIDAD
         * Asegura que los datos cumplan con las políticas de seguridad de la UTM.
         */
        if (p1 !== p2) {
            return Swal.fire('Error de Paridad', 'Las contraseñas ingresadas no coinciden.', 'error');
        }
        
        // Política de complejidad mínima
        if (p1.length < 8) {
            return Swal.fire('Seguridad Débil', 'La nueva contraseña debe tener al menos 8 caracteres.', 'warning');
        }

        // Feedback visual de procesamiento (Bloqueo de interfaz)
        Swal.fire({ 
            title: 'Procesando cambios...', 
            didOpen: () => Swal.showLoading(), 
            allowOutsideClick: false 
        });

        try {
            /**
             * 2. TRANSMISIÓN ASÍNCRONA AL BACKEND
             * Consume el endpoint de actualización enviando el DTO (Data Transfer Object).
             */
            const response = await fetch('api/auth/actualizar_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: token, password: p1 })
            });

            const data = await response.json();

            if (data.success) {
                /**
                 * FASE DE ÉXITO: CIERRE DE CICLO
                 * Notifica al usuario y redirige al portal de acceso principal.
                 */
                Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                    window.location.href = 'index.php';
                });
            } else {
                // Manejo de errores lógicos (Ej: Token expirado o ya utilizado)
                Swal.fire('Fallo en Validación', data.error, 'error');
            }
        } catch (error) {
            // Gestión de excepciones de red o fallos en el núcleo PHP
            Swal.fire('Error de Conexión', 'No se pudo establecer vínculo con el servidor de seguridad.', 'error');
        }
    });
});