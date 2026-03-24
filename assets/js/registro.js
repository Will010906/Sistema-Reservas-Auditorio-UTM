/**
 * LÓGICA DE REGISTRO DE USUARIOS - NIVEL TSU
 * Estado: Producción Segura (Async/Await + JSON)
 * Ajuste: Ruta modularizada a api/auth/
 */

/* global Swal */

// --- A. FORMATEADOR DINÁMICO DE TELÉFONO (UX) ---
document.getElementById('reg_tel')?.addEventListener('input', function (e) {
    let val = e.target.value.replace(/\D/g, ''); 
    let finalVal = "";

    if (val.length > 0) {
        finalVal += val.substring(0, 3);
        if (val.length > 3) finalVal += '-' + val.substring(3, 6);
        if (val.length > 6) finalVal += '-' + val.substring(6, 10);
    }
    e.target.value = finalVal;
});

// --- B. PROCESAMIENTO DE REGISTRO ---
document.getElementById('registroForm')?.addEventListener('submit', async function(e) {
    e.preventDefault(); // Evita la recarga de página para manejarlo vía API

    // 1. Captura y Limpieza de datos
    const nombre = this.nombre.value.trim();
    const matricula = document.getElementById('reg_matricula').value.trim();
    const password = document.getElementById('reg_pass').value.trim();
    const telefonoRaw = document.getElementById('reg_tel').value.replace(/\D/g, '');

    // 2. Validaciones Frontend (Reglas Institucionales)
    if (!nombre || !matricula || !password || telefonoRaw.length === 0) {
        return mostrarAlerta('warning', 'Campos vacíos', 'Todos los campos son obligatorios.');
    }

    if (telefonoRaw.length !== 10) {
        return mostrarAlerta('info', 'Teléfono no válido', 'Ingresa los 10 dígitos de tu número.');
    }

    // Validación de Matrícula UTM
    const regexMatri = /^UTM\d{6}[A-Z]{2,4}$/i;
    if (!regexMatri.test(matricula)) {
        return mostrarAlerta('info', 'Matrícula no válida', 'Formato: UTM + 6 números + Carrera.');
    }

    // Validación de Contraseña Segura (Letras, números y símbolo)
    const regexPass = /^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_]).{8,}$/;
    if (!regexPass.test(password)) {
        return mostrarAlerta('error', 'Contraseña débil', 'Mínimo 8 caracteres, letras, números y un símbolo.');
    }

    // 3. ENVÍO ASÍNCRONO AL BACKEND
    try {
        // CORRECCIÓN: Ruta actualizada a la subcarpeta 'auth'
        const response = await fetch('api/auth/registro_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({
                nombre,
                matricula,
                password,
                telefono: telefonoRaw
            })
        });

        const data = await response.json(); // Consumo de respuesta JSON

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Registro Exitoso!',
                text: 'Ya puedes iniciar sesión con tu cuenta.',
                confirmButtonColor: '#5B3D66'
            }).then(() => {
                window.location.href = 'index.php'; // Redirección al login tras éxito
            });
        } else {
            mostrarAlerta('error', 'Error en registro', data.error || 'No se pudo completar el registro.');
        }

    } catch (error) {
        console.error("Error técnico:", error);
        mostrarAlerta('error', 'Error de conexión', 'El núcleo del sistema no responde.');
    }
});

/**
 * Función auxiliar para alertas estandarizadas
 */
function mostrarAlerta(icon, title, text) {
    Swal.fire({
        icon,
        title,
        text,
        confirmButtonColor: '#5B3D66'
    });
}