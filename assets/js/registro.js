/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: CONTROLADOR DE REGISTRO Y ALTA DE USUARIOS
 * * @package     Frontend_Security
 * @subpackage  Registration_Logic
 * @version     1.2.5
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Gestiona la captura, validación y persistencia de nuevos perfiles de usuario. 
 * Implementa una capa de pre-procesamiento de datos (máscaras de teléfono) y 
 * validación estricta de sintaxis institucional antes de la transmisión asíncrona.
 * * CAPACIDADES:
 * 1. UX Adaptativa: Formateador automático de números telefónicos (XXX-XXX-XXXX).
 * 2. Validación Regex: Verificación de patrones de matrícula institucional (UTM...).
 * 3. Comunicación Asíncrona: Transmisión de DTOs (Data Transfer Objects) vía JSON.
 * 4. Gestión de Errores: Manejo de excepciones de red y colisiones de datos (duplicados).
 */

/* global Swal */

/**
 * 1. SUBSISTEMA DE FORMATEO DINÁMICO (UX)
 * Escucha el flujo de entrada en el campo de teléfono para aplicar máscara automática.
 */
document.getElementById("reg_tel")?.addEventListener("input", function (e) {
  let val = e.target.value.replace(/\D/g, ""); // Sanitización: solo dígitos
  let finalVal = "";

  if (val.length > 0) {
    finalVal += val.substring(0, 3);
    if (val.length > 3) finalVal += "-" + val.substring(3, 6);
    if (val.length > 6) finalVal += "-" + val.substring(6, 10);
  }
  e.target.value = finalVal;
});

/**
 * 2. SUBSISTEMA DE PROCESAMIENTO DE REGISTRO
 * Intercepta el evento submit para validar y transmitir la información al backend.
 */
document.getElementById("registroForm")?.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Extracción y limpieza de variables del formulario
    const nombre    = this.nombre.value.trim();
    const matricula = document.getElementById("reg_matricula").value.trim();
    const correo    = this.correo.value.trim(); 
    const carrera   = this.carrera.value; 
    const password  = document.getElementById("reg_pass").value.trim();
    const telefonoRaw = document.getElementById("reg_tel").value.replace(/\D/g, "");

    /**
     * MOTOR DE VALIDACIÓN PREVENTIVA (CLIENT-SIDE)
     * Asegura que el payload cumpla con los requisitos mínimos antes del fetch.
     */
    if (!nombre || !matricula || !correo || !carrera || !password || telefonoRaw.length === 0) {
      return mostrarAlerta("warning", "Campos vacíos", "Por favor, llena todos los campos del formulario.");
    }

    // Validación de sintaxis de Correo Electrónico
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regexEmail.test(correo)) {
      return mostrarAlerta("info", "Correo no válido", "Ingrese un formato de correo electrónico real.");
    }

    // Validación de Matrícula Institucional (Patrón UTM + 6-10 caracteres alfanuméricos)
    const regexMatri = /^UTM[A-Z0-9]{6,10}$/i; 
    if (!regexMatri.test(matricula)) {
        return mostrarAlerta('info', 'Matrícula no válida', 'Ingresa tu matrícula completa de la UTM.');
    }

    if (telefonoRaw.length !== 10) {
      return mostrarAlerta("info", "Teléfono no válido", "Ingrese los 10 dígitos de su número.");
    }

    /**
     * 3. TRANSMISIÓN ASÍNCRONA AL BACKEND
     * Consume el microservicio de registro mediante el protocolo HTTP POST.
     */
    try {
      const response = await fetch("api/auth/registro_usuario.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          nombre,
          matricula,
          correo,
          carrera,
          password,
          telefono: telefonoRaw,
        }),
      });

      const data = await response.json();

      if (data.success) {
        // Fase de Éxito: Notificación y Redirección al Portal de Acceso
        Swal.fire({
          icon: "success",
          title: "¡Registro Exitoso!",
          text: "Su cuenta ha sido creada. Ya puede iniciar sesión.",
          confirmButtonColor: "#5B3D66",
        }).then(() => {
          window.location.href = "index.php";
        });
      } else {
        // Manejo de errores lógicos (Ej: Matrícula duplicada en la base de datos)
        mostrarAlerta("error", "Error en registro", data.error || "La matrícula o correo ya existen.");
      }

    } catch (error) {
        console.error("Fallo técnico en registro:", error);
        mostrarAlerta("error", "Error de conexión", "El servidor mandó una respuesta inválida o el núcleo falló.");
    }
});

/**
 * 4. UTILIDADES DE INTERFAZ Estandarizadas
 */
function mostrarAlerta(icon, title, text) {
  Swal.fire({
    icon,
    title,
    text,
    confirmButtonColor: "#5B3D66",
  });
}