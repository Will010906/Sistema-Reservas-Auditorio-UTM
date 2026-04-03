$(document).ready(function () {
    $('#formNuevaPass').on('submit', async function (e) {
        e.preventDefault();
        
        const p1 = $('#pass1').val();
        const p2 = $('#pass2').val();
        const token = $('#token_reset').val();

        if (p1 !== p2) {
            return Swal.fire('Error', 'Las contraseñas no coinciden.', 'error');
        }
        if (p1.length < 8) {
            return Swal.fire('Seguridad', 'La contraseña debe tener al menos 8 caracteres.', 'warning');
        }

        Swal.fire({ title: 'Procesando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

        try {
            const response = await fetch('api/auth/actualizar_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: token, password: p1 })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                    window.location.href = 'index.php';
                });
            } else {
                Swal.fire('Error', data.error, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        }
    });
});