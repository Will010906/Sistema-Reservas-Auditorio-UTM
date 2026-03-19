document.getElementById('registroForm').addEventListener('submit', function(e) {
    const nombre = this.nombre.value.trim();
    const matricula = document.getElementById('reg_matricula').value.trim();
    const password = document.getElementById('reg_pass').value.trim();
    const telefono = document.getElementById('reg_tel').value.trim();

    // 1. Validar campos vacíos
    if (!nombre || !matricula || !password) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Campos vacíos', text: 'Todos los campos son obligatorios.', confirmButtonColor: '#5B3D66' });
        return;
    }

    // 2. Validar Matrícula Alumno
    const regexMatri = /^UTM\d{6}[A-Z]{2,4}$/i;
    if (!regexMatri.test(matricula)) {
        e.preventDefault();
        Swal.fire({ icon: 'info', title: 'Matrícula no válida', text: 'Usa el formato: UTM + 6 números + Carrera.', confirmButtonColor: '#5B3D66' });
        return;
    }

    // 3. Validar Contraseña Fuerte
    const regexPass = /^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_]).{8,}$/;
    if (!regexPass.test(password)) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'Contraseña débil', text: 'Debe tener al menos 8 caracteres, letras, números y un símbolo.', confirmButtonColor: '#5B3D66' });
    }

    

// Validar que sean exactamente 10 números
const regexTel = /^\d{10}$/;
if (!regexTel.test(telefono)) {
    e.preventDefault();
    Swal.fire({
        icon: 'info',
        title: 'Teléfono no válido',
        text: 'Por favor, ingresa un número de 10 dígitos (ej. 4431234567).',
        confirmButtonColor: '#5B3D66'
    });
    return;
}
});