/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO DE GESTIÓN DE INFRAESTRUCTURA (ADMIN)
 * * @package     Frontend_Logic
 * @subpackage  Gestion_Auditorios
 * @version     1.0.8
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Implementa un CRUD completo (Create, Read, Update, Delete) para la entidad 'auditorio'.
 * Utiliza la API Fetch para comunicación asíncrona y SweetAlert2 para UX.
 * * SEGURIDAD:
 * - Inyecta el Token Bearer en cada cabecera para validación de sesión administrativa.
 * - Maneja la expiración de sesiones redirigiendo al usuario al login.
 */

/* global Swal, bootstrap */

/**
 * 1. INICIALIZACIÓN DEL ENTORNO
 * Configura los escuchadores de eventos una vez que el DOM está listo.
 */
document.addEventListener("DOMContentLoaded", () => {
  console.log("Sistema de Auditorios UTM cargado...");

  // Carga inicial de datos desde el servidor
  cargarAuditorios();

  /**
   * MANEJADOR DEL FORMULARIO (TRANSACCIONES POST)
   * Procesa tanto el registro de nuevos auditorios como la actualización de existentes.
   */
  const formulario = document.getElementById("formAuditorio");
  if (formulario) {
    formulario.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Feedback visual de procesamiento
      Swal.fire({
        title: "Guardando cambios...",
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false,
      });

      const formData = new FormData(this);
      const id = this.dataset.id; // Identifica si es edición mediante el dataset
      if (id) formData.append("id_auditorio", id);

      try {
        const response = await fetch("api/admin/gestion_auditorios.php", {
          method: "POST", // Se usa POST para soportar el envío de archivos (imágenes)
          headers: {
            Authorization: `Bearer ${localStorage.getItem("sira_session_token")}`,
          
          },
          body: formData,
        });

        if (response.status === 401) return manejarSesionExpirada();

     const res = await response.json();

       if (res.success) {
            Swal.fire("¡Éxito!", res.message, "success").then(() => {
                // 1. Intentar cierre oficial por instancia
                const modalElem = document.getElementById("modalNuevoAuditorio");
                const instance = bootstrap.Modal.getInstance(modalElem);
                if (instance) instance.hide();

                // 2. LIMPIEZA TÉCNICA MANUAL (Garantiza el desbloqueo)
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = 'auto';
                document.body.style.paddingRight = '0';

                // 3. Sincronizar Dashboard
                location.reload();
            });
        } else {
            Swal.fire("Error", res.error, "error");
        }
      } catch (error) {
          console.error("Error en comunicación:", error);
          Swal.fire("Error", "Fallo al conectar con el servidor.", "error");
      }
    });
  }
});


/**
 * 2. RENDERIZADO DINÁMICO DE AUDITORIOS (READ)
 * Recupera la colección de espacios y genera las tarjetas visuales (Cards).
 * @async
 */
async function cargarAuditorios() {
  const contenedor = document.getElementById("contenedorAuditorios");
  if (!contenedor) return;

  try {
    const response = await fetch("api/admin/get_auditorios.php", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("sira_session_token")}`,
      },
    });

    if (response.status === 401) return manejarSesionExpirada();

    const data = await response.json();
    contenedor.innerHTML = ""; // Limpieza del buffer visual

    data.forEach((aud) => {
      const esDisponible = parseInt(aud.disponibilidad) === 1;

      // LÓGICA DE BADGES INSTITUCIONALES
      const statusBadge = esDisponible
        ? '<span class="badge badge-sira-status shadow-sm">Activo</span>'
        : '<span class="badge bg-warning text-dark shadow-sm" style="font-size: 0.65rem;">Mantenimiento</span>';

      const btnStatusClass = esDisponible ? "btn-outline-guinda" : "btn-guinda";
      const btnStatusText = esDisponible ? "Mantenimiento" : "Activar Espacio";

      // PROCESAMIENTO DE EQUIPAMIENTO (STRING TO LIST)
      const listaEquipamiento = aud.equipamiento_fijo ? aud.equipamiento_fijo.split(",") : [];
      let htmlEquipamiento = "";

      if (listaEquipamiento.length > 0 && listaEquipamiento[0].trim() !== "") {
        htmlEquipamiento = '<div class="mt-2">';
        listaEquipamiento.forEach((item) => {
          htmlEquipamiento += `
            <div class="d-flex align-items-center mb-1">
                <i class="bi bi-check2-square text-primary me-2" style="font-size: 0.8rem;"></i>
                <span class="text-dark" style="font-size: 0.75rem;">${item.trim()}</span>
            </div>`;
        });
        htmlEquipamiento += '</div>';
      } else {
        htmlEquipamiento = '<span class="text-muted" style="font-size: 0.7rem;">Sin equipamiento registrado</span>';
      }

      // INYECCIÓN DE COMPONENTES AL DOM
      contenedor.innerHTML += `
        <div class="col-md-4 mb-4">
            <div class="card auditorio-card h-100 border-0 shadow-sm" style="border-radius: 20px;">
                <img src="assets/img/auditorios/${aud.id_auditorio}.jpg" class="card-img-top" 
                     onerror="this.src='assets/img/placeholder.jpg'" 
                     style="height: 180px; object-fit: cover; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem;">${aud.nombre_espacio}</h5>
                        ${statusBadge}
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-1" style="font-size: 0.8rem;"><i class="bi bi-geo-alt"></i> ${aud.ubicacion}</p>
                        <div class="capacidad-info" style="font-size: 0.85rem; color: #5B3D66; font-weight: 700;">
                            <i class="bi bi-people-fill"></i> ${aud.capacidad_maxima} <span style="font-weight: 500; font-size: 0.75rem;">Capacidad</span>
                        </div>
                    </div>
                    <div class="mb-4 flex-grow-1">
                        <label class="d-block text-muted fw-bold mb-1" style="font-size: 0.55rem; text-transform: uppercase;">Equipamiento Fijo</label>
                        ${htmlEquipamiento}
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light border flex-fill fw-bold" onclick='editarAuditorio(${JSON.stringify(aud)})'>
                            <i class="bi bi-pencil-square"></i> Editar
                        </button>
                        <button class="btn btn-sm ${btnStatusClass} flex-fill fw-bold" onclick="cambiarEstado(${aud.id_auditorio}, ${aud.disponibilidad})">
                            ${btnStatusText}
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarAuditorio(${aud.id_auditorio})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    });
  } catch (error) {
    contenedor.innerHTML = '<p class="text-danger text-center">Fallo crítico al conectar con el servicio de datos.</p>';
  }
}

/**
 * 3. CONTROL DE DISPONIBILIDAD (PATCH)
 * Modifica el estatus operativo sin afectar el resto de los datos.
 */
async function cambiarEstado(id, estadoActual) {
  const nuevoEstado = estadoActual === 1 ? 0 : 1;
  const { isConfirmed } = await Swal.fire({
    title: nuevoEstado === 1 ? "¿Activar Auditorio?" : "¿Poner en Mantenimiento?",
    text: "Esto afectará la disponibilidad para futuras reservaciones.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#5B3D66",
    confirmButtonText: "Sí, confirmar",
  });

  if (isConfirmed) {
    try {
      const res = await fetch("api/admin/gestion_auditorios.php", {
        method: "PATCH",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("sira_session_token")}`,
        },
        body: JSON.stringify({ id: parseInt(id), estado: parseInt(nuevoEstado) }),
      });

      const data = await res.json();
      if (data.success) {
        Swal.fire("Actualizado", data.message, "success").then(() => location.reload());
      } else {
        Swal.fire("Error", data.error, "error");
      }
    } catch (error) {
      Swal.fire("Error de API", "Fallo de conexión con el controlador.", "error");
    }
  }
}

/**
 * 4. ELIMINACIÓN DE REGISTROS (DELETE)
 */
async function eliminarAuditorio(id) {
  const { isConfirmed } = await Swal.fire({
    title: "¿Eliminar permanentemente?",
    text: "Esta acción es irreversible.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Eliminar",
  });

  if (isConfirmed) {
    try {
      const res = await fetch("api/admin/gestion_auditorios.php", {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("sira_session_token")}`,
        },
        body: JSON.stringify({ id: parseInt(id) }),
      });
      const data = await res.json();
      if (data.success) location.reload();
    } catch (error) {
      Swal.fire("Error", "No se pudo procesar la eliminación.", "error");
    }
  }
}

/**
 * 5. CONTROLADORES DE INTERFAZ (UI)
 */
window.prepararNuevoAuditorio = function () {
  const form = document.getElementById("formAuditorio");
  if (form) {
    form.reset();
    delete form.dataset.id; // Limpia el estado de edición
    document.getElementById("tituloModal").innerText = "Registrar Nuevo Auditorio";
    document.getElementById("edit_id").value = "";
    bootstrap.Modal.getOrCreateInstance(document.getElementById("modalNuevoAuditorio")).show();
  }
};

/**
 * PREPARACIÓN DE INTERFAZ PARA EDICIÓN
 * Mapea los datos del objeto auditorio al formulario y activa el modal.
 * @param {Object} auditorio - Entidad con los metadatos del espacio físico.
 */
function editarAuditorio(auditorio) {
    document.getElementById("tituloModal").innerText = "Editar Auditorio";
    const form = document.getElementById("formAuditorio");
    
    /**
     * PERSISTENCIA DE IDENTIDAD
     * Seteamos el ID en el dataset para que el evento 'submit' sepa que es 
     * una actualización y no un registro nuevo.
     */
    form.dataset.id = auditorio.id_auditorio; 

    // Población de campos del formulario
    document.getElementById("edit_id").value = auditorio.id_auditorio;
    document.getElementById("edit_nombre").value = auditorio.nombre_espacio;
    document.getElementById("edit_ubicacion").value = auditorio.ubicacion;
    document.getElementById("edit_capacidad").value = auditorio.capacidad_maxima;
    document.getElementById("edit_equipamiento").value = auditorio.equipamiento_fijo;

    /**
     * GESTIÓN DE INSTANCIA BOOTSTRAP
     * Usamos getOrCreateInstance para evitar duplicidad de capas (backdrops) 
     * en el DOM de la UTM.
     */
    const modalElement = document.getElementById("modalNuevoAuditorio");
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
    
    // Mostramos el modal de forma controlada
    modalInstance.show();
}

function manejarSesionExpirada() {
  localStorage.removeItem("sira_session_token");
  window.location.href = "login.php";
}