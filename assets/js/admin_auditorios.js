/**
 * GESTIÓN DE AUDITORIOS - SIRA UTM
 * Implementa: CRUD completo, JWT Security y validación de formularios.
 */

/* global Swal, bootstrap */

// --- INICIALIZACIÓN GLOBAL ---
document.addEventListener("DOMContentLoaded", () => {
  console.log("Sistema de Auditorios cargado...");

  // 1. Cargar la lista inicial de auditorios
  cargarAuditorios();

  // 2. ESCUCHADOR DEL FORMULARIO (GUARDAR / ACTUALIZAR)
  const formulario = document.getElementById("formAuditorio");
  if (formulario) {
    formulario.addEventListener("submit", async function (e) {
      e.preventDefault();
      console.log("Detectado intento de guardado...");

      // Carga visual
      Swal.fire({
        title: "Guardando...",
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false,
      });

      const formData = new FormData(this);
      const id = this.dataset.id;
      if (id) formData.append("id_auditorio", id);

      try {
        const response = await fetch("api/admin/gestion_auditorios.php", {
          method: "POST", // Siempre POST para procesar $_FILES en PHP
          headers: {
            Authorization: `Bearer ${localStorage.getItem("sira_session_token")}`,
          },
          body: formData,
        });

        if (response.status === 401) return manejarSesionExpirada();

        const res = await response.json();
        if (res.success) {
          Swal.fire("¡Éxito!", res.message, "success").then(() =>
            location.reload(),
          );
        } else {
          Swal.fire("Error", res.error, "error");
        }
      } catch (error) {
        console.error("Error en submit:", error);
        Swal.fire(
          "Error",
          "Fallo en la comunicación con el servidor.",
          "error",
        );
      }
    });
  }
});

/**
 * CARGA INICIAL: Obtiene los auditorios al abrir la página
 */
/**
 * CARGA INICIAL: Obtiene los auditorios al abrir la página
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
    contenedor.innerHTML = "";

    data.forEach((aud) => {
      const esDisponible = parseInt(aud.disponibilidad) === 1;

      const statusBadge = esDisponible
        ? '<span class="badge bg-success shadow-sm">Activo</span>'
        : '<span class="badge bg-warning text-dark shadow-sm">Mantenimiento</span>';

      const btnStatusClass = esDisponible ? "btn-outline-guinda" : "btn-guinda";
      const btnStatusText = esDisponible ? "Mantenimiento" : "Activar Espacio";

      // --- LÓGICA PARA BLOQUES DE EQUIPAMIENTO MINIMALISTAS ---
      const listaEquipamiento = aud.equipamiento_fijo ? aud.equipamiento_fijo.split(",") : [];
      let htmlEquipamiento = "";

      if (listaEquipamiento.length > 0 && listaEquipamiento[0].trim() !== "") {
        listaEquipamiento.forEach((item) => {
          // Usamos la nueva clase 'badge-minimal'
          htmlEquipamiento += `<span class="badge-minimal me-1 mb-1">${item.trim()}</span>`;
        });
      } else {
        htmlEquipamiento = '<span class="text-muted" style="font-size: 0.65rem;">Sin equipamiento</span>';
      }

      contenedor.innerHTML += `
        <div class="col-md-4 mb-4">
            <div class="card auditorio-card h-100 border-0 shadow-sm" style="border-radius: 20px;">
                <img src="assets/img/auditorios/${aud.id_auditorio}.jpg" class="card-img-top" 
                     onerror="this.src='assets/img/placeholder.jpg'" style="height: 180px; object-fit: cover; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem;">${aud.nombre_espacio}</h5>
                        ${statusBadge}
                    </div>
                    <p class="text-muted mb-3" style="font-size: 0.8rem;"><i class="bi bi-geo-alt"></i> ${aud.ubicacion}</p>
                    
                    <div class="mb-4 flex-grow-1">
                        <label class="d-block text-muted fw-bold mb-1" style="font-size: 0.55rem; text-transform: uppercase; letter-spacing: 0.5px;">Equipamiento Fijo</label>
                        <div class="d-flex flex-wrap">
                            ${htmlEquipamiento}
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light border flex-fill fw-bold" style="font-size: 0.75rem;" onclick='editarAuditorio(${JSON.stringify(aud)})'>
                            <i class="bi bi-pencil-square"></i> Editar
                        </button>
                        <button class="btn btn-sm ${btnStatusClass} flex-fill fw-bold" style="font-size: 0.75rem;" onclick="cambiarEstado(${aud.id_auditorio}, ${aud.disponibilidad})">
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
    contenedor.innerHTML = '<p class="text-danger text-center">Error al conectar con la base de datos.</p>';
  }
}
/**
 * CAMBIAR DISPONIBILIDAD (PATCH)
 */
async function cambiarEstado(id, estadoActual) {
  const nuevoEstado = estadoActual === 1 ? 0 : 1;

  const { isConfirmed } = await Swal.fire({
    title:
      nuevoEstado === 1 ? "¿Activar Auditorio?" : "¿Poner en Mantenimiento?",
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
        body: JSON.stringify({
          id: parseInt(id),
          estado: parseInt(nuevoEstado),
        }),
      });

      const data = await res.json();
      if (data.success) {
        Swal.fire("¡Éxito!", data.message, "success").then(() =>
          location.reload(),
        );
      } else {
        Swal.fire("Error", data.error, "error");
      }
    } catch (error) {
      Swal.fire("Error", "Fallo de conexión con la API", "error");
    }
  }
}

/**
 * ELIMINAR (DELETE)
 */
async function eliminarAuditorio(id) {
  const { isConfirmed } = await Swal.fire({
    title: "¿Eliminar permanentemente?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
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
      Swal.fire("Error", "No se pudo eliminar", "error");
    }
  }
}

/**
 * PREPARA MODAL PARA NUEVO O EDICIÓN
 */
window.prepararNuevoAuditorio = function () {
  const form = document.getElementById("formAuditorio");
  if (form) {
    form.reset();
    delete form.dataset.id;
    document.getElementById("tituloModal").innerText =
      "Registrar Nuevo Auditorio";
    document.getElementById("edit_id").value = "";
    bootstrap.Modal.getOrCreateInstance(
      document.getElementById("modalNuevoAuditorio"),
    ).show();
  }
};

function editarAuditorio(auditorio) {
  document.getElementById("tituloModal").innerText = "Editar Auditorio";
  const form = document.getElementById("formAuditorio");
  form.dataset.id = auditorio.id_auditorio;

  document.getElementById("edit_id").value = auditorio.id_auditorio;
  document.getElementById("edit_nombre").value = auditorio.nombre_espacio;
  document.getElementById("edit_ubicacion").value = auditorio.ubicacion;
  document.getElementById("edit_capacidad").value = auditorio.capacidad_maxima;
  document.getElementById("edit_equipamiento").value =
    auditorio.equipamiento_fijo;

  bootstrap.Modal.getOrCreateInstance(
    document.getElementById("modalNuevoAuditorio"),
  ).show();
}

function manejarSesionExpirada() {
  localStorage.removeItem("sira_session_token");
  window.location.href = "login.php";
}
