$(document).on('click', '#togglePassword', function (e) {
    e.preventDefault();
    
    // Buscamos el input que esté justo antes o por su ID
    const passwordInput = $('#reg_pass').length ? $('#reg_pass') : $('#pass_usuario');
    const icono = $('#iconoOjo');

    if (passwordInput.attr('type') === 'password') {
        passwordInput.attr('type', 'text');
        icono.removeClass('bi-eye-slash').addClass('bi-eye');
    } else {
        passwordInput.attr('type', 'password');
        icono.removeClass('bi-eye').addClass('bi-eye-slash');
    }
});