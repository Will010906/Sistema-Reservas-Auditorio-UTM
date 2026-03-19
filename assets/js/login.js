document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const btnEntrar = document.getElementById("btnEntrar");

  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      const matricula = document.getElementById("matricula").value.trim();
      const password = document.getElementById("password").value.trim();

      // 1. Validar campos vacíos con SweetAlert2 (Adiós burbuja fea)
      if (matricula === "" || password === "") {
        e.preventDefault(); // Detenemos el envío nativo
        Swal.fire({
          icon: 'warning',
          title: 'Campos incompletos',
          text: 'Por favor, llena ambos campos para acceder.',
          confirmButtonColor: '#5B3D66'
        });
        return;
      }

      // 2. Validar Formato UTM o ID Trabajador
      // Alumnos: UTM + 6 números + carrera | Trabajadores: 2 a 5 dígitos
      const regexMatricula = /^(UTM\d{6}[A-Z]{2,4}|\d{2,5})$/i;
      if (!regexMatricula.test(matricula)) {
        e.preventDefault();
        Swal.fire({
          icon: 'info',
          title: 'Formato incorrecto',
          text: 'Ingresa una matrícula válida (UTM...) o tu ID de trabajador.',
          confirmButtonColor: '#5B3D66'
        });
        return;
      }

      // 3. Validar Contraseña Fuerte
      // Mínimo 8 caracteres, letras, números y un especial
      const regexPassword = /^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_]).{8,}$/;
      if (!regexPassword.test(password)) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Contraseña no válida',
          text: 'Debe tener al menos 8 caracteres, incluyendo letras, números y un símbolo.',
          confirmButtonColor: '#5B3D66'
        });
        return;
      }

      // 4. Si todo está BIEN, mostramos la carga (No usamos preventDefault aquí)
      btnEntrar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Validando acceso...';
      btnEntrar.style.opacity = "0.8";
    });
  }

  // --- Manejo de Notificaciones de URL (Logout y Errores de BD) ---
  const urlParams = new URLSearchParams(window.location.search);

  if (urlParams.get('status') === 'logout') {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Sesión cerrada correctamente',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  if (urlParams.get('error') === 'auth') {
    Swal.fire({
      icon: 'error',
      title: 'Error de acceso',
      text: 'La matrícula o contraseña son incorrectas.',
      confirmButtonColor: '#5B3D66'
    });
    window.history.replaceState({}, document.title, window.location.pathname);
  }
});