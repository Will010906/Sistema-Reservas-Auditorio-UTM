<thead>
    <tr class="text-muted x-small fw-bold text-uppercase border-bottom">
        <th class="ps-4">Folio</th>
        <th>Título del Evento</th>
        <th>Auditorio</th>
        <th>Fecha</th>
        <th class="text-center">Estatus</th>
        <th class="text-center">Acciones</th> </tr>
</thead>
<tbody>
    <?php while ($fila = mysqli_fetch_assoc($resultado)): 
        $status_val = strtoupper($fila['estado']);
        $st_class = ($status_val == 'ACEPTADA') ? 'st-aceptada' : (($status_val == 'RECHAZADA') ? 'st-rechazada' : 'st-pendiente');
    ?>
        <tr>
            <td class="ps-4 fw-bold" style="color: var(--sira-purple-primary);">#<?php echo $fila['folio']; ?></td>
            <td class="fw-600"><?php echo $fila['titulo_event']; ?></td>
            <td><span class="badge rounded-pill bg-light text-dark border px-3 py-2"><?php echo $fila['nombre_espacio']; ?></span></td>
            <td class="text-muted fw-bold"><?php echo date('d/m/Y', strtotime($fila['fecha_evento'])); ?></td>
            <td class="text-center"><span class="badge-status <?php echo $st_class; ?> shadow-sm"><?php echo $status_val; ?></span></td>
            <td class="text-center">
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-sm btn-outline-primary border-0" onclick="verDetalleUsuario(<?php echo $fila['id_solicitud']; ?>)" title="Ver Detalle">
                        <i class="bi bi-eye-fill"></i>
                    </button>

                    <?php if ($status_val == 'PENDIENTE'): ?>
                        <button class="btn btn-sm btn-outline-warning border-0" onclick="editarMiSolicitud(<?php echo $fila['id_solicitud']; ?>)" title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger border-0" onclick="cancelarMiSolicitud(<?php echo $fila['id_solicitud']; ?>)" title="Cancelar">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
<script src="assets/js/usuario_reservas.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>