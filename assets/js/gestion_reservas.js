/**
 * LÓGICA DE GESTIÓN SIRA UTM
 * Manejo de Calendario, Rangos de Horas, Filtrado y Limpieza
 */

let auditorioSeleccionado = null;
let horaSeleccionada = null;
let calendarInstance = null;
let ocupadosGlobal = [];
let seleccionInicio = null;
let seleccionFin = null;
let equipamientoActual = "";

// --- 1. NAVEGACIÓN ENTRE PASOS ---

function irAlCalendario(id, nombre, equipamiento) {
  auditorioSeleccionado = id;
  if (equipamiento !== undefined) equipamientoActual = equipamiento || "";

  document.getElementById("input_id_auditorio").value = id;
  document.getElementById("display_nombre_auditorio").innerText = nombre;
  document.getElementById("display_nombre_final").innerText = nombre;

  document.getElementById("paso_catalogo").style.display = "none";
  document.getElementById("paso_formulario").style.display = "none";
  document.getElementById("paso_calendario").style.display = "block";

  setTimeout(() => {
    initCalendar();
    
    // SI ESTAMOS EDITANDO: Forzamos al calendario a ir a la fecha precargada
    let fechaPrecargada = document.getElementById("input_fecha_evento").value;
    if(fechaPrecargada && calendarInstance) {
        calendarInstance.gotoDate(fechaPrecargada);
        // Opcional: Disparar el click automático en esa fecha para ver horarios
        actualizarDisponibilidad(); 
    }
  }, 200);
}

function regresarAlCatalogo() {
  document.getElementById("paso_calendario").style.display = "none";
  document.getElementById("paso_formulario").style.display = "none";
  document.getElementById("paso_catalogo").style.display = "block";
}

function irAlFormularioFinal() {
  // 📸 CARGA DE IMAGEN CON PROTECCIÓN DE BUCLE
  const imgPreview = document.getElementById("img_final_preview");
  if (imgPreview && auditorioSeleccionado) {
    imgPreview.onerror = function () {
      this.src = "assets/img/placeholder.jpg";
      this.onerror = null;
    };
    imgPreview.src = `assets/img/auditorios/${auditorioSeleccionado}.jpg`;
  }

  // 🛠️ RENDERIZAR EQUIPAMIENTO FIJO
  const contenedor = document.getElementById("check_equipamiento_fijo");
  if (contenedor) {
    contenedor.innerHTML = "";
    if (equipamientoActual && equipamientoActual.trim() !== "") {
      const items = equipamientoActual.split(",");
      items.forEach((item) => {
        let nombreItem = item.trim();
        if (nombreItem !== "" && nombreItem !== "bszsrbsr") {
          contenedor.innerHTML += `
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-check-circle-fill text-primary me-1" style="font-size: 0.7rem;"></i>
                            <span>${nombreItem}</span>
                        </div>`;
        }
      });
    } else {
      contenedor.innerHTML =
        '<span class="text-muted italic">No especificado</span>';
    }
  }

  // 📄 ASIGNAR VALORES Y CAMBIAR VISTA
  document.getElementById("input_fecha_evento").value =
    document.getElementById("fecha_seleccionada").value;
  document.getElementById("input_hora_inicio").value = horaSeleccionada.inicio;
  document.getElementById("input_hora_fin").value = horaSeleccionada.fin;

  document.getElementById("paso_calendario").style.display = "none";
  document.getElementById("paso_formulario").style.display = "block";

  const modalBody = document.querySelector(".modal-body");
  if (modalBody) modalBody.scrollTop = 0;
}

// --- 2. CONFIGURACIÓN DEL CALENDARIO ---

function initCalendar() {
  const calendarEl = document.getElementById("calendar_interactivo");
  if (calendarInstance) {
    calendarInstance.destroy();
  }

  calendarInstance = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    locale: "es",
    buttonText: { today: "Hoy", month: "Mes", day: "Día" },
    titleFormat: { year: "numeric", month: "long" },
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth",
    },
    selectable: true,
    validRange: { start: new Date().toISOString().split("T")[0] },
    events: `modules/get_eventos_calendario.php?id_auditorio=${auditorioSeleccionado}`,

    dateClick: function (info) {
      const fechaTxt = info.dateStr.split("-").reverse().join("/");
      const inputDisplay = document.getElementById("fecha_display");
      document.getElementById("fecha_display").value = fechaTxt;
      document.getElementById("fecha_seleccionada").value = info.dateStr;
      document.getElementById("fecha_seleccionada_txt").innerText =
        `Horarios para el ${fechaTxt}`;

      document
        .querySelectorAll(".fc-daygrid-day")
        .forEach((el) => (el.style.background = ""));
      info.dayEl.style.backgroundColor = "rgba(91, 61, 102, 0.1)";

      inputDisplay.value = fechaTxt;
      inputDisplay.classList.add("fw-bold", "text-primary");

      actualizarDisponibilidad();
    },
  });
  calendarInstance.render();
}

// --- 3. DISPONIBILIDAD Y RANGO DE HORAS ---

async function actualizarDisponibilidad() {
  const fecha = document.getElementById("fecha_seleccionada").value;
  const grid = document.getElementById("grid_horarios");
  grid.innerHTML =
    '<div class="text-center w-100 py-3"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

  try {
    const response = await fetch(
      `modules/get_disponibilidad.php?id=${auditorioSeleccionado}&fecha=${fecha}`,
    );
    ocupadosGlobal = await response.json();
    renderizarBotonesHorario();
  } catch (error) {
    grid.innerHTML =
      '<p class="text-danger small">Error al conectar con el servidor.</p>';
  }
}

function renderizarBotonesHorario() {
  const contenedor = document.getElementById("grid_horarios");
  const btnConfirmar = document.getElementById("btnConfirmarHorario");
  contenedor.innerHTML = "";
  btnConfirmar.disabled = true;
  seleccionInicio = null;
  seleccionFin = null;

  for (let h = 7; h <= 20; h++) {
    let hInicio = `${h.toString().padStart(2, "0")}:00:00`;
    let tDisplay = `${h > 12 ? h - 12 : h}:00 ${h >= 12 ? "PM" : "AM"}`;
    const estaOcupado = ocupadosGlobal.some(
      (r) => hInicio >= r.inicio && hInicio < r.fin,
    );

    const btn = document.createElement("button");
    btn.type = "button";
    btn.dataset.hora = hInicio;
    btn.dataset.indice = h;
    btn.className = estaOcupado
      ? "btn btn-danger disabled opacity-50"
      : "btn btn-outline-success btn-hora-libre";
    btn.style.cssText =
      "width: 100px; font-weight: 700; border-radius: 12px; font-size: 0.7rem; padding: 10px 5px;";
    btn.innerHTML = estaOcupado
      ? `<i class="bi bi-x-circle d-block"></i> ${tDisplay}`
      : tDisplay;

    if (!estaOcupado) {
      btn.onclick = function () {
        gestionarSeleccionRango(this);
      };
    }
    contenedor.appendChild(btn);
  }
}

function gestionarSeleccionRango(boton) {
  const horaHeader = boton.dataset.hora;
  const indice = parseInt(boton.dataset.indice);
  const btnConfirmar = document.getElementById("btnConfirmarHorario");
  const msjError = document.getElementById("msj_error_rango");
  const txtFecha = document.getElementById("fecha_seleccionada_txt");

  if (boton.classList.contains("btn-success")) {
    seleccionInicio = null;
    seleccionFin = null;
    actualizarVisualizacionBotones();
    btnConfirmar.disabled = true;
    txtFecha.innerText = `Horarios para el ${document.getElementById("fecha_display").value}`;
    return;
  }

  if (!seleccionInicio || (seleccionInicio && seleccionFin)) {
    seleccionInicio = { hora: horaHeader, indice: indice };
    seleccionFin = null;

    horaSeleccionada = {
      inicio: horaHeader,
      fin: `${(indice + 1).toString().padStart(2, "0")}:00:00`,
    };

    if (msjError) msjError.style.display = "none";
    txtFecha.innerText = `Horarios para el ${document.getElementById("fecha_display").value} (1 hr seleccionada)`;
  } else {
    let inicioRango = Math.min(seleccionInicio.indice, indice);
    let finRango = Math.max(seleccionInicio.indice, indice);
    let totalHoras = finRango - inicioRango + 1;

    if (totalHoras > 2) {
      if (msjError) {
        msjError.innerText = "⚠️ La reserva máxima permitida es de 2 horas.";
        msjError.style.display = "block";
      }
      return;
    }

    let rangoValido = true;
    for (let i = inicioRango; i <= finRango; i++) {
      let hCheck = `${i.toString().padStart(2, "0")}:00:00`;
      if (ocupadosGlobal.some((r) => hCheck >= r.inicio && hCheck < r.fin)) {
        rangoValido = false;
        break;
      }
    }

    if (rangoValido) {
      seleccionInicio = {
        hora: `${inicioRango.toString().padStart(2, "0")}:00:00`,
        indice: inicioRango,
      };
      seleccionFin = {
        hora: `${(finRango + 1).toString().padStart(2, "0")}:00:00`,
        indice: finRango,
      };

      horaSeleccionada = {
        inicio: seleccionInicio.hora,
        fin: seleccionFin.hora,
      };
      txtFecha.innerText = `Horarios para el ${document.getElementById("fecha_display").value} (${totalHoras} hrs seleccionadas)`;
      if (msjError) msjError.style.display = "none";
    } else {
      if (msjError) {
        msjError.innerText =
          "⚠️ No puedes seleccionar un rango con horas ocupadas.";
        msjError.style.display = "block";
      }
      return;
    }
  }

  actualizarVisualizacionBotones();
  btnConfirmar.disabled = false;
}

function actualizarVisualizacionBotones() {
  document.querySelectorAll(".btn-hora-libre").forEach((btn) => {
    const idx = parseInt(btn.dataset.indice);
    btn.classList.remove("btn-success");
    btn.classList.add("btn-outline-success");

    if (seleccionFin) {
      const hInicioNum = parseInt(seleccionInicio.hora.split(":")[0]);
      const hFinNum = parseInt(seleccionFin.hora.split(":")[0]);

      const inicioVisual = Math.min(hInicioNum, hFinNum - 1);
      const finVisual = Math.max(hInicioNum, hFinNum - 1);

      if (idx >= inicioVisual && idx <= finVisual) {
        btn.classList.replace("btn-outline-success", "btn-success");
      }
    } else if (seleccionInicio && idx === seleccionInicio.indice) {
      btn.classList.replace("btn-outline-success", "btn-success");
    }
  });
}

// --- 4. DATATABLES Y FILTROS ---

$(document).ready(function () {
  // Inicializar tabla principal
  const table = $("#tablaMisReservas").DataTable({
    language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
    pageLength: 10,
    dom: "rtip",
    ordering: true,
    order: [[3, "desc"]],
  });

  // Evento de filtrado por estatus (Checkboxes)
  $(".check-filtro").on("change", function () {
    let valoresSeleccionados = [];

    $(".check-filtro:checked").each(function () {
      valoresSeleccionados.push($(this).val());
    });

    if (valoresSeleccionados.length > 0) {
      let busqueda = valoresSeleccionados.join("|");
      // Columna 4 es el ESTATUS
      table.column(4).search(busqueda, true, false).draw();
    } else {
      table.column(4).search("").draw();
    }
  });
});

// --- LÓGICA DE FILTROS Y REPORTES SIRA ---

// 1. Función para resetear todo el panel
function limpiarFiltros() {
    // A. Resetear Checkboxes (Marcamos los activos, desmarcamos los finales)
    $("#chkUrg, #chkPen, #chkTie").prop("checked", true);
    $("#chkAce, #chkRec").prop("checked", false);

    // B. Limpiar Rango de Fechas
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value = '';

    // C. Recargar la página para ver todo de nuevo (o llamar a tu función de filtrado)
    location.reload(); 
    console.log("Filtros limpiados.");
}

// 2. Escuchador para el botón PDF
document.addEventListener("DOMContentLoaded", function () {
    const btnPDF = document.getElementById('btnPDF');
    if (btnPDF) {
        btnPDF.addEventListener('click', function() {
            // Capturamos las fechas para el reporte
            const fInicio = document.getElementById('fecha_inicio').value;
            const fFin = document.getElementById('fecha_fin').value;
            
            // Abrimos el generador pasándole los parámetros
            const url = `modules/generar_reporte.php?inicio=${fInicio}&fin=${fFin}`;
            window.open(url, '_blank');
        });
    }
});

// --- 5. LIMPIEZA DE MODAL ---

function limpiarModalReservacion() {
  auditorioSeleccionado = null;
  horaSeleccionada = null;
  seleccionInicio = null;
  seleccionFin = null;
  equipamientoActual = "";

  const formulario = document.querySelector("#paso_formulario form");
  if (formulario) formulario.reset();

  document.getElementById("check_equipamiento_fijo").innerHTML = "";
  document.getElementById("grid_horarios").innerHTML = "";
  document.getElementById("fecha_display").value = "";
  document.getElementById("fecha_seleccionada").value = "";

  const errRango = document.getElementById("msj_error_rango");
  if (errRango) errRango.style.display = "none";

  document.getElementById("paso_calendario").style.display = "none";
  document.getElementById("paso_formulario").style.display = "none";
  document.getElementById("paso_catalogo").style.display = "block";

  if (calendarInstance) {
    calendarInstance.destroy();
    calendarInstance = null;
  }
}

// Listener para el modal
document.addEventListener("DOMContentLoaded", function () {
  const myModalEl = document.getElementById("modalNuevaSolicitud");
  if (myModalEl) {
    myModalEl.addEventListener("hidden.bs.modal", function () {
      limpiarModalReservacion();
    });
  }
});
