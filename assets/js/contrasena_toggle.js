$(document).ready(function () {
    // Usamos el ID del botón como activador
    $(document).on('click', '#togglePassword', function () {
        
        // ESTA ES LA MAGIA: 
        // 'this' es el botón al que le diste clic.
        // 'siblings' busca al input que está en su mismo nivel (su hermano).
        const passwordInput = $(this).siblings('input');
        const icono = $(this).find('i');

        // Cambiamos el tipo de input
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icono.removeClass('bi-eye-slash').addClass('bi-eye');
        } else {
            passwordInput.attr('type', 'password');
            icono.removeClass('bi-eye').addClass('bi-eye-slash');
        }
    });
});