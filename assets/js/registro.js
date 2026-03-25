/**
 * LÓGICA DE REGISTRO DE USUARIOS - NIVEL TSU
 * Estado: Producción Segura (Async/Await + JSON)
 * Ajuste: Ruta modularizada a api/auth/
 */

/* global Swal */

// --- A. FORMATEADOR DINÁMICO DE TELÉFONO (UX) ---
document.getElementById("reg_tel")?.addEventListener("input", function (e) {
  let val = e.target.value.replace(/\D/g, "");
  let finalVal = "";

  if (val.length > 0) {
    finalVal += val.substring(0, 3);
    if (val.length > 3) finalVal += "-" + val.substring(3, 6);
    if (val.length > 6) finalVal += "-" + val.substring(6, 10);
  }
  e.target.value = finalVal;
});

// --- B. PROCESAMIENTO DE REGISTRO ---
document
  .getElementById("registroForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();

    // 1. Captura de todos los campos del formulario
    const nombre = this.nombre.value.trim();
    const matricula = document.getElementById("reg_matricula").value.trim();
    const correo = this.correo.value.trim(); // <--- AGREGADO
    const carrera = this.carrera.value; // <--- AGREGADO
    const password = document.getElementById("reg_pass").value.trim();
    const telefonoRaw = document
      .getElementById("reg_tel")
      .value.replace(/\D/g, "");

    // 2. Validaciones Frontend
    if (
      !nombre ||
      !matricula ||
      !correo ||
      !carrera ||
      !password ||
      telefonoRaw.length === 0
    ) {
      return mostrarAlerta(
        "warning",
        "Campos vacíos",
        "Por favor, llena todos los campos del formulario.",
      );
    }

    // Validación de Correo UTM
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Valida formato texto@texto.dominio

    if (!regexEmail.test(correo)) {
      return mostrarAlerta(
        "info",
        "Correo no válido",
        "Por favor, ingresa un correo electrónico real (ej: usuario@gmail.com).",
      );
    }
    // Validación de Matrícula UTM (Flexible: letras y números)
const regexMatri = /^UTM[A-Z0-9]{6,10}$/i; 

if (!regexMatri.test(matricula)) {
    return mostrarAlerta('info', 'Matrícula no válida', 'Ingresa tu matrícula completa de la UTM.');
}

    if (telefonoRaw.length !== 10) {
      return mostrarAlerta(
        "info",
        "Teléfono no válido",
        "Ingresa los 10 dígitos de tu número.",
      );
    }

    // 3. ENVÍO ASÍNCRONO AL BACKEND
    try {
      const response = await fetch("api/auth/registro_usuario.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          nombre,
          matricula,
          correo, // <--- ENVIADO AL PHP
          carrera, // <--- ENVIADO AL PHP
          password,
          telefono: telefonoRaw,
        }),
      });

      const data = await response.json();

      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "¡Registro Exitoso!",
          text: "Ya puedes iniciar sesión con tu cuenta.",
          confirmButtonColor: "#5B3D66",
        }).then(() => {
          window.location.href = "index.php";
        });
      } else {
        mostrarAlerta(
          "error",
          "Error en registro",
          data.error || "La matrícula o correo ya existen.",
        );
      }
   // assets/js/registro.js
} catch (error) {
    console.error("Error técnico:", error);
    // Agregamos esto para ver qué respondió el servidor si no es JSON
    mostrarAlerta(
        "error",
        "Error de conexión",
        "El servidor mandó una respuesta inválida. Revisa la consola (F12)."
    );
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
    confirmButtonColor: "#5B3D66",
  });
}
