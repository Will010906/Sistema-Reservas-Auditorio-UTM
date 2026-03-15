/**
 * Lógica para la navegación y validación de horarios
 * Proyecto: Sistema de Reservación UTM
 */

let auditorioSeleccionado = null;
let horaSeleccionada = null;

// PASO 1: Al elegir auditorio del catálogo
function irAlCalendario(id, nombre) {
    auditorioSeleccionado = id;
    
    // Guardar datos en el formulario final
    const inputId = document.getElementById('input_id_auditorio');
    const displayNombre = document.getElementById('display_nombre_auditorio');
    
    if (inputId && displayNombre) {
        inputId.value = id;
        displayNombre.innerText = nombre;
    }

    // Navegación visual
    document.getElementById('paso_catalogo').style.display = 'none';
    document.getElementById('paso_calendario').style.display = 'block';
}

// PASO 2: Al cambiar la fecha en el input date
async function actualizarDisponibilidad() {
    const fecha = document.getElementById('fecha_seleccionada').value;
    const contenedor = document.getElementById('contenedor_horarios');
    const btnSiguiente = document.getElementById('btnIrAPasoFinal');

    if (!fecha) return;

    btnSiguiente.disabled = true; // Bloquear hasta que elija hora
    contenedor.innerHTML = '<div class="spinner-border text-primary sm"></div> Cargando...';

    try {
        const response = await fetch(`modules/get_disponibilidad.php?id=${auditorioSeleccionado}&fecha=${fecha}`);
        const ocupados = await response.json();
        renderizarHorarios(ocupados);
    } catch (error) {
        contenedor.innerHTML = '<p class="text-danger">Error al conectar con el servidor.</p>';
    }
}

// Renderizar botones de 7:00 AM a 9:00 PM
function renderizarHorarios(ocupados) {
    const contenedor = document.getElementById('contenedor_horarios');
    contenedor.innerHTML = '';

    // Rango UTM: 7 a 21 (9 PM)
    for (let h = 7; h <= 21; h++) {
        const hInicio = `${h.toString().padStart(2, '0')}:00:00`;
        const hFin = `${(h + 1).toString().padStart(2, '0')}:00:00`;
        const texto = `${h > 12 ? h - 12 : h}:00 ${h >= 12 ? 'PM' : 'AM'}`;

        // Validar si el bloque está ocupado
        const estaOcupado = ocupados.some(r => (hInicio < r.fin && hFin > r.inicio));

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = estaOcupado ? 'btn btn-outline-danger disabled opacity-50 m-1' : 'btn btn-outline-success m-1';
        btn.style.width = '100px';
        btn.innerText = texto;

        if (!estaOcupado) {
            btn.onclick = function() {
                // Estilo de selección
                document.querySelectorAll('#contenedor_horarios .btn-success').forEach(b => {
                    b.classList.replace('btn-success', 'btn-outline-success');
                });
                this.classList.replace('btn-outline-success', 'btn-success');
                
                // Guardar selección temporal
                horaSeleccionada = { inicio: hInicio, fin: hFin };
                document.getElementById('btnIrAPasoFinal').disabled = false;
            };
        }
        contenedor.appendChild(btn);
    }
}

// PASO 3: Ir al formulario de datos finales
function irAlFormularioFinal() {
    const fecha = document.getElementById('fecha_seleccionada').value;
    
    // Pasar datos al formulario oculto
    document.getElementById('input_fecha_evento').value = fecha;
    document.getElementById('input_hora_inicio').value = horaSeleccionada.inicio;
    document.getElementById('input_hora_fin').value = horaSeleccionada.fin;

    document.getElementById('paso_calendario').style.display = 'none';
    document.getElementById('paso_formulario').style.display = 'block';
}

function regresarAlCatalogo() {
    document.getElementById('paso_calendario').style.display = 'none';
    document.getElementById('paso_formulario').style.display = 'none';
    document.getElementById('paso_catalogo').style.display = 'block';
}

// Limpiar todo al cerrar
document.getElementById('modalNuevaSolicitud').addEventListener('hidden.bs.modal', function () {
    regresarAlCatalogo();
    this.querySelector('form').reset();
    document.getElementById('contenedor_horarios').innerHTML = '';
});