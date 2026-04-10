/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: CONTROLADOR DE VISIBILIDAD DE CREDENCIALES
 * * @package     Frontend_Logic
 * @subpackage  Security_UI
 * @version     1.0.5
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Implementa un Toggle Dinámico para la máscara de caracteres en campos de 
 * contraseña. Utiliza navegación relativa del DOM para asegurar compatibilidad 
 * con múltiples instancias de entrada de datos.
 * * CAPACIDADES:
 * 1. Delegación de Eventos: Escucha en el documento para soportar elementos inyectados dinámicamente.
 * 2. Navegación Siblings: Localiza el campo 'input' sin depender de IDs únicos por campo.
 * 3. Feedback Visual: Alternancia de clases de Bootstrap Icons (bi-eye / bi-eye-slash).
 */

$(document).ready(function () {
    /**
     * MANEJADOR DE EVENTO CLIC
     * Implementa delegación para optimizar el uso de memoria en el navegador.
     */
    $(document).on('click', '#togglePassword', function () {
        
        /**
         * LOCALIZACIÓN RELATIVA DE ELEMENTOS
         * @var jQueryObject passwordInput - Selecciona el campo hermano de tipo input.
         * @var jQueryObject icono - Selecciona el elemento representativo de la fuente de iconos.
         */
        const passwordInput = $(this).siblings('input');
        const icono = $(this).find('i');

        /**
         * LÓGICA DE CONMUTACIÓN (TOGGLE)
         * Evalúa el atributo 'type' del DOM para alternar la máscara de seguridad.
         */
        if (passwordInput.attr('type') === 'password') {
            // Fase: Revelar Credencial
            passwordInput.attr('type', 'text');
            icono.removeClass('bi-eye-slash').addClass('bi-eye');
        } else {
            // Fase: Enmascarar Credencial (Estado por defecto)
            passwordInput.attr('type', 'password');
            icono.removeClass('bi-eye').addClass('bi-eye-slash');
        }
    });
});