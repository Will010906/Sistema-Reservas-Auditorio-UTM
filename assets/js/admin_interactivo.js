// Función que se activa al dar clic en el botón "Gestionar" de la tabla
function gestionar(id) {
    // En un proyecto real, aquí harías un fetch() para traer datos de la DB. 
    // Por ahora, simularemos que JS "lee" la fila de la tabla.
    const modal = document.getElementById('modalGestion');
    modal.style.display = "block";
    
    // Aquí podrías llenar los datos del modal dinámicamente
    document.getElementById('modalFolio').innerText = "Gestionando ID: " + id;
}

function cerrarModal() {
    document.getElementById('modalGestion').style.display = "none";
}

function actualizarEstado(nuevoEstado) {
    alert("Cambiando estatus a: " + nuevoEstado);
    // Aquí es donde JS hablaría con un archivo PHP para actualizar la base de datos sin recargar.
    cerrarModal();
}

document.querySelector('.filters button').addEventListener('click', () => {
    const inicio = document.querySelectorAll('.filters input')[0].value;
    const fin = document.querySelectorAll('.filters input')[1].value;

    fetch(`modules/filtrar_solicitudes.php?inicio=${inicio}&fin=${fin}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = ""; // Limpiamos la tabla
            
            data.forEach(sol => {
                tbody.innerHTML += `
                    <tr>
                        <td>${sol.folio}</td>
                        <td>${sol.titulo_event}</td>
                        <td>ID Auditorio: ${sol.id_auditorio}</td>
                        <td>Pendiente</td>
                        <td>${sol.fecha_evento}</td>
                        <td><span class="status">${sol.estado}</span></td>
                        <td><button class="btn" onclick="gestionar(${sol.id_solicitud})">Gestionar</button></td>
                    </tr>`;
            });
        });
});

function gestionar(id) {
    const modal = document.getElementById('modalDetalle');
    
    fetch(`modules/get_detalle.php?id=${id}`) // Ahora sí encontrará el archivo
        .then(res => res.json())
        .then(data => {
            document.getElementById('detFolio').innerText = "Folio: " + data.folio;
            document.getElementById('detFechaSol').innerText = data.fecha_registro;
            document.getElementById('detFechaEv').innerText = data.fecha_evento;
            document.getElementById('detUsuarioNombre').innerText = data.nombre; // Viene del JOIN
            document.getElementById('detTituloEv').innerText = data.titulo_event;
            document.getElementById('detDescripcion').innerText = data.descripcion;
            
            modal.style.display = "flex";
        })
        .catch(err => console.error("Error al cargar detalles:", err));
}