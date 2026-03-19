// A. FORMATEADOR DINÁMICO (Se ejecuta mientras el usuario escribe)
document.getElementById('reg_tel').addEventListener('input', function (e) {
    let val = e.target.value.replace(/\D/g, ''); // Quitamos todo lo que no sea número
    let finalVal = "";

    if (val.length > 0) {
        finalVal += val.substring(0, 3);
        if (val.length > 3) finalVal += '-' + val.substring(3, 6);
        if (val.length > 6) finalVal += '-' + val.substring(6, 10);
    }
    e.target.value = finalVal;
});

// B. VALIDACIÓN AL ENVIAR EL FORMULARIO
document.getElementById('registroForm').addEventListener('submit', function(e) {
    const nombre = this.nombre.value.trim();
    const matricula = document.getElementById('reg_matricula').value.trim();
    const password = document.getElementById('reg_pass').value.trim();
    
    // Limpiamos los guiones para validar que tengamos 10 números exactos
    const telefonoRaw = document.getElementById('reg_tel').value.replace(/\D/g, '');

    // 1. Validar campos vacíos
    if (!nombre || !matricula || !password || telefonoRaw.length === 0) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Campos vacíos', text: 'Todos los campos son obligatorios.', confirmButtonColor: '#5B3D66' });
        return;
    }

    // 2. Validar que el teléfono tenga exactamente 10 dígitos
    if (telefonoRaw.length !== 10) {
        e.preventDefault();
        Swal.fire({ icon: 'info', title: 'Teléfono no válido', text: 'Por favor, ingresa los 10 dígitos de tu número.', confirmButtonColor: '#5B3D66' });
        return;
    }

    // 3. Validar Matrícula (Formato UTM)
    const regexMatri = /^UTM\d{6}[A-Z]{2,4}$/i;
    if (!regexMatri.test(matricula)) {
        e.preventDefault();
        Swal.fire({ icon: 'info', title: 'Matrícula no válida', text: 'Usa el formato: UTM + 6 números + Carrera.', confirmButtonColor: '#5B3D66' });
        return;
    }

    // 4. Validar Contraseña Fuerte
    const regexPass = /^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_]).{8,}$/;
    if (!regexPass.test(password)) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'Contraseña débil', text: 'Mínimo 8 caracteres, letras, números y un símbolo.', confirmButtonColor: '#5B3D66' });
        return;
    }
});