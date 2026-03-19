/**
 * LÓGICA DE SALIDA CON LIMPIEZA DE TOKEN
 */
function confirmarSalida() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "Se eliminará tu acceso temporal en este navegador.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#5B3D66', // Tu color sira-purple
        cancelButtonColor: '#adb5bd',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // 1. LIMPIAR EL TOKEN DEL NAVEGADOR (Paso Vital)
            localStorage.removeItem('sira_session_token');
            
            // 2. REDIRIGIR AL LOGOUT DE PHP
            window.location.href = 'modules/logout.php';
        }
    })
}