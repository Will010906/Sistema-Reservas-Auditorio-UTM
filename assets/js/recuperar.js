$(document).ready(function () {
    $('#formRecuperar').on('submit', async function (e) {
        e.preventDefault();
        
        const email = $('#email_recuperar').val();
        
        Swal.fire({
            title: 'Buscando cuenta...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            // LLAMADA REAL AL API
            const response = await fetch('api/auth/solicitar_reset.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ correo: email })
            });

            const data = await response.json();

            if (data.success) {
                // AQUÍ: En un servidor real, esto mandaría el correo.
                // Para tu prueba local, mostraremos el token en consola o alerta.
                Swal.fire({
                    icon: 'success',
                    title: '¡Proceso Iniciado!',
                    text: 'Se ha generado un token de seguridad en la base de datos.',
                    confirmButtonColor: '#5B3D66'
                });
                
                // TIP: Imprime el link en consola para que puedas probarlo fácil
                console.log("Link de prueba: restablecer.php?token=" + data.token);
                
            } else {
                Swal.fire('Error', data.error, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
        }
    });
});