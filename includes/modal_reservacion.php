<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">

            <div class="modal-header text-white p-4 border-0" style="background-color: var(--sira-purple-dark);">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                        <i class="bi bi-calendar-plus fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 mb-0" style="font-weight: 800;">Nueva Reservación</h5>
                        <small class="opacity-75" id="display_nombre_auditorio">Selecciona un espacio para tu evento</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="regresarAlCatalogo()"></button>
            </div>

            <div class="modal-body p-0 bg-light">

                <div id="paso_catalogo" class="p-4">
                    <div class="d-flex align-items-center mb-4 px-2">
                        <span class="badge bg-primary rounded-pill me-2">1</span>
                        <h6 class="fw-800 text-uppercase small mb-0" style="letter-spacing: 1px; color: var(--sira-purple-primary);">Selecciona el Auditorio</h6>
                    </div>
                    <div class="row g-4 px-2">
                        <?php
                        // Consulta optimizada para traer todos los datos necesarios
                        $auds = mysqli_query($conexion, "SELECT * FROM auditorio WHERE disponibilidad = 1 ORDER BY nombre_espacio ASC");
                        while ($a = mysqli_fetch_assoc($auds)):
                            $ruta_foto = "assets/img/auditorios/" . $a['id_auditorio'] . ".jpg";
                        ?>
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm card-auditorio overflow-hidden" style="border-radius: 24px; cursor: pointer;"
                                    onclick="irAlCalendario(<?php echo $a['id_auditorio']; ?>, '<?php echo addslashes($a['nombre_espacio']); ?>', '<?php echo addslashes($a['equipamiento_fijo']); ?>')">
                                    <div style="height: 150px; overflow: hidden; position: relative;">
                                        <img src="<?php echo $ruta_foto; ?>" onerror="this.src='assets/img/placeholder.jpg'" style="width: 100%; height: 100%; object-fit: cover;">
                                        <div class="position-absolute bottom-0 start-0 m-2">
                                            <span class="badge bg-dark bg-opacity-75 rounded-pill small">
                                                <i class="bi bi-people-fill me-1"></i><?php echo $a['capacidad_maxima']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold mb-1"><?php echo $a['nombre_espacio']; ?></h6>
                                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-primary"></i> <?php echo $a['ubicacion']; ?></p>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php
                                            $equipos = explode(',', $a['equipamiento_fijo']);
                                            foreach ($equipos as $e): if (trim($e) != "" && trim($e) != "bszsrbsr"): ?>
                                                    <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.65rem;"><?php echo trim($e); ?></span>
                                            <?php endif;
                                            endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div id="paso_calendario" class="p-4" style="display: none;">
                    <button class="btn btn-sm text-primary fw-bold mb-4 p-0" onclick="regresarAlCatalogo()">
                        <i class="bi bi-arrow-left"></i> Volver al catálogo
                    </button>
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div id="calendar_interactivo" class="shadow-sm border rounded-4 bg-white p-3"></div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                                <h6 class="fw-800 text-uppercase small mb-3" style="color: var(--sira-purple-primary);">Configura tu horario:</h6>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Fecha seleccionada</label>
                                    <input type="text" id="fecha_display" class="form-control bg-light border-0 fw-bold" readonly placeholder="Toca un día en el calendario">
                                    <input type="hidden" id="fecha_seleccionada">
                                </div>

                                <h6 class="fw-800 text-uppercase small mb-1 mt-4" style="color: var(--sira-purple-primary); font-size: 0.7rem;">Disponibilidad (Haz clic para definir inicio y fin):</h6>
                                <p class="text-muted small mb-3" id="fecha_seleccionada_txt">Selecciona un día para ver bloques</p>

                                <div id="grid_horarios" class="d-flex flex-wrap gap-2 mb-2"></div>

                                <div id="msj_error_rango" class="alert alert-warning small rounded-3 border-0 mt-2" style="display: none;">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i> No puedes seleccionar un rango que incluya horas ya ocupadas.
                                </div>

                                <div class="mt-auto border-top pt-4">
                                    <button id="btnConfirmarHorario" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm" disabled onclick="irAlFormularioFinal()">
                                        Confirmar horario <i class="bi bi-check2-circle ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="paso_formulario" class="p-5" style="display: none;">
                    <button class="btn btn-sm text-primary fw-bold mb-4 p-0" onclick="irAlCalendario(auditorioSeleccionado, '')">
                        <i class="bi bi-arrow-left"></i> Cambiar horario
                    </button>

                    <form action="modules/procesar_solicitud.php" method="POST">
                        <input type="hidden" name="id_auditorio" id="input_id_auditorio">
                        <input type="hidden" name="fecha_evento" id="input_fecha_evento">
                        <input type="hidden" name="hora_inicio" id="input_hora_inicio">
                        <input type="hidden" name="hora_fin" id="input_hora_fin">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Nombre del Evento</label>
                                <input type="text" name="titulo" class="form-control border-0 shadow-sm p-3 rounded-4 mb-3" placeholder="Ingresa el nombre del evento" required>

                                <label class="form-label small fw-bold text-muted text-uppercase">Razón de la Solicitud</label>
                                <textarea name="descripcion" class="form-control border-0 shadow-sm p-3 rounded-4 mb-3" rows="3" placeholder="Describe brevemente tu evento..." required></textarea>

                                <label class="form-label small fw-bold text-muted text-uppercase">Otros requerimientos (Opcional)</label>
                                <input type="text" name="otros_servicios" class="form-control border-0 shadow-sm p-3 rounded-4" placeholder="Ej. Cafetería, sillas extra, etc.">
                            </div>

                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                                    <div style="height: 140px; width: 100%; overflow: hidden; background: #f8f9fa;">
                                        <img id="img_final_preview" src="assets/img/placeholder.jpg" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>

                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span id="display_nombre_final" class="fw-bold text-dark small text-uppercase">Auditorio</span>
                                            <span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">Confirmado</span>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-6 border-end">
                                                <label class="extra-small fw-bold text-primary d-block mb-2">INCLUYE:</label>
                                                <div id="check_equipamiento_fijo" class="extra-small text-muted" style="min-height: 50px;">
                                                </div>
                                            </div>
                                            <div class="col-6 ps-3">
                                                <label class="extra-small fw-bold text-success d-block mb-2">EXTRAS:</label>
                                                <div class="form-check extra-small p-0 m-0" style="font-size: 0.65rem;">
                                                    <input class="form-check-input ms-0 me-1" type="checkbox" name="extras[]" value="Laptop"> Laptop<br>
                                                    <input class="form-check-input ms-0 me-1" type="checkbox" name="extras[]" value="Grabacion"> Grabación
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" onclick="irAlCalendario(auditorioSeleccionado, '')">Anterior</button>
                            <button type="submit" class="btn btn-success px-5 py-3 rounded-pill fw-bold shadow">Aceptar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>