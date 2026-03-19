/**
 * LÓGICA INTERACTIVA DEL PANEL ADMINISTRADOR - SIRA UTM
 * Corregido: Filtrado por estatus y rango de fechas sincronizado.
 */

let idSeleccionado = null;
let bsModal = null;

/**
 * Carga el detalle y abre el modal
 */
function gestionar(id) {
  idSeleccionado = id;
  if (!bsModal) {
    const modalElement = document.getElementById("bsModalDetalle");
    if (modalElement) bsModal = new bootstrap.Modal(modalElement);
  }

  fetch(`modules/get_detalle.php?id=${id}`)
    .then((res) => res.json())
    .then((data) => {
      // Llenado básico
      document.getElementById("detFolio").innerText = "Folio: " + data.folio;
      document.getElementById("detTituloEv").innerText = data.titulo_event;
      document.getElementById("detUsuarioNombre").innerText = data.nombre;
      document.getElementById("detAuditorio").innerText = data.nombre_espacio;
      document.getElementById("detFechaEvento").innerText = data.fecha_evento;
      document.getElementById("detHorario").innerText =
        `${data.hora_inicio} a ${data.hora_fin}`;
      document.getElementById("detDescription").innerText = data.descripcion;

      // 1. Lógica de WhatsApp
      const btnWA = document.getElementById("btnWhatsApp");
      if (data.telefono) {
        btnWA.href = `https://wa.me/52${data.telefono.replace(/\D/g, "")}`;
        btnWA.style.display = "inline-block";
      } else {
        btnWA.style.display = "none";
      }

      // 2. Alerta de Capacidad
      const asistentes = parseInt(data.num_asistentes || 0);
      const capacidad = parseInt(data.capacidad_maxima || 0);
      const alertaCap = document.getElementById("detAsistentes");
      if (asistentes > capacidad) {
        alertaCap.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> Sobrecupo: ${asistentes}/${capacidad}</span>`;
      } else {
        alertaCap.innerHTML = `<span class="text-muted small">${asistentes} asistentes (Capacidad: ${capacidad})</span>`;
      }

      // 3. Renderizar Equipamiento
      const contenedorEquip = document.getElementById("detEquipamiento");
      contenedorEquip.innerHTML = "";
      if (data.equipos_solicitados) {
        data.equipos_solicitados.split(", ").forEach((item) => {
          contenedorEquip.innerHTML += `<span class="badge bg-light text-dark border small">${item}</span>`;
        });
      } else {
        contenedorEquip.innerHTML =
          '<span class="text-muted small italic">Ninguno solicitado</span>';
      }

      bsModal.show();
    });
}
/**
 * SISTEMA DE FILTRADO (Estatus + Fechas)
 */

// Escuchar cambios en checkboxes y fechas
// --- LÓGICA DE EXCLUSIÓN PARA "TODOS" ---

// 1. Si marco "TODOS", desmarco los demás
document.getElementById("chkTodos").addEventListener("change", function (e) {
  if (this.checked) {
    document
      .querySelectorAll(".filter-check")
      .forEach((cb) => (cb.checked = false));
  }
  aplicarFiltros();
});

// 2. Si marco cualquier otro, desmarco "TODOS"
document.querySelectorAll(".filter-check").forEach((checkbox) => {
  checkbox.addEventListener("change", function () {
    if (this.checked) {
      document.getElementById("chkTodos").checked = false;
    }
    aplicarFiltros();
  });
});

/**
 * SISTEMA DE FILTRADO (Estatus + Fechas)
 */
function aplicarFiltros() {
  const todosActivo = document.getElementById("chkTodos").checked;

  // Obtener valores solo si "TODOS" no está marcado
  const seleccionados = todosActivo
    ? []
    : Array.from(document.querySelectorAll(".filter-check:checked")).map((cb) =>
        cb.value.toUpperCase(),
      );

  const inicio = document.getElementById("fecha_inicio").value;
  const fin = document.getElementById("fecha_fin").value;
  const filas = document.querySelectorAll("#tablaSolicitudes tbody tr");

  filas.forEach((fila) => {
    const badge = fila.querySelector(".badge-status");
    if (!badge) return;

    const textoEstado = badge.innerText.trim().toUpperCase();
    const fechaCeldaRaw = fila
      .querySelector("td:nth-child(4)")
      .innerText.trim();
    const partes = fechaCeldaRaw.split("/");
    const fechaFila = `${partes[2]}-${partes[1]}-${partes[0]}`;

    // Lógica de validación
    // Si 'todosActivo' es true, cumpleEstatus siempre es true
    const cumpleEstatus = todosActivo || seleccionados.includes(textoEstado);

    let cumpleFecha = true;
    if (inicio && fin) {
      cumpleFecha = fechaFila >= inicio && fechaFila <= fin;
    } else if (inicio) {
      cumpleFecha = fechaFila >= inicio;
    } else if (fin) {
      cumpleFecha = fechaFila <= fin;
    }

    fila.style.display = cumpleEstatus && cumpleFecha ? "" : "none";
  });
}

/**
 * Función Limpiar (RESTAURADA)
 */
/**
 * Función Limpiar (RESTAURADA Y GLOBAL)
 */
// Lógica para el checkbox "TODOS"
document.getElementById("chkTodos").addEventListener("change", function (e) {
  const estaMarcado = e.target.checked;
  const filtros = document.querySelectorAll(".filter-check");

  // Marcamos o desmarcamos todos los demás
  filtros.forEach((cb) => (cb.checked = estaMarcado));

  // Aplicamos el filtro a la tabla inmediatamente
  aplicarFiltros();
});

// Función Limpiar (Actualizada para incluir 'Todos')
window.resetFiltros = function () {
  console.log("Reiniciando panel SIRA...");

  // Marcamos 'Todos' y activamos los filtros principales
  document.getElementById("chkTodos").checked = true;
  document
    .querySelectorAll(".filter-check")
    .forEach((cb) => (cb.checked = false));
  document.getElementById("chkUrg").checked = false;
  document.getElementById("chkPen").checked = false;
  document.getElementById("chkTie").checked = false;

  // Dejamos las terminadas desmarcadas por orden visual
  document.getElementById("chkAce").checked = false;
  document.getElementById("chkRec").checked = false;

  // Limpiar fechas
  document.getElementById("fecha_inicio").value = "";
  document.getElementById("fecha_fin").value = "";

  // Mostrar todas las filas antes de filtrar
  document
    .querySelectorAll("#tablaSolicitudes tbody tr")
    .forEach((f) => (f.style.display = ""));

  aplicarFiltros();
};

/**
 * Reporte PDF
 */
window.descargarReporte = function () {
  const inicio = document.getElementById("fecha_inicio").value;
  const fin = document.getElementById("fecha_fin").value;

  // Obtenemos los estatus marcados para el reporte
  const seleccionados = Array.from(
    document.querySelectorAll(".filter-check:checked"),
  )
    .map((cb) => cb.value)
    .join(",");

  if (!inicio || !fin) {
    Swal.fire({
      icon: "info",
      title: "Rango incompleto",
      text: "Selecciona una fecha de inicio y fin para el PDF.",
      confirmButtonColor: "#5B3D66",
    });
    return;
  }

  const url = `modules/generar_reporte.php?inicio=${inicio}&fin=${fin}&estatus=${seleccionados}`;
  window.open(url, "_blank");
};

/**
 * Actualizar Estado (Aprobar/Rechazar)
 */
window.actualizarEstado = function(nuevoEstado) {
    if (!idSeleccionado) return;
    
    // 1. CAPTURAMOS EL TEXTO (Asegúrate de que este nombre sea el que usas abajo)
    const motivoTexto = document.getElementById('motivoRechazo').value;

    Swal.fire({
        title: 'Procesando...',
        didOpen: () => { Swal.showLoading() },
        allowOutsideClick: false
    });

    fetch('modules/actualizar_estado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            id: idSeleccionado, 
            estado: nuevoEstado,
            comentario: motivoTexto // <-- AQUÍ: Debe decir 'motivoTexto', no 'motivo'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Construir mensaje para WhatsApp usando la misma variable 'motivoTexto'
            const msg = nuevoEstado === 'Aceptada' 
                ? `¡Hola ${data.usuario}! Tu solicitud para "${data.evento}" fue ACEPTADA. \nInstrucciones: ${motivoTexto}`
                : `Hola ${data.usuario}, tu solicitud para "${data.evento}" fue RECHAZADA. \nMotivo: ${motivoTexto}`;

            const urlWA = `https://wa.me/52${data.telefono.replace(/\D/g,'')}?text=${encodeURIComponent(msg)}`;
            
            Swal.fire('¡Éxito!', 'Estado actualizado correctamente.', 'success').then(() => {
                window.open(urlWA, '_blank');
                location.reload(); 
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });
};
