<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background-color: #fcfcfc;">

            <div class="modal-header text-white p-4 border-0" style="background: linear-gradient(135deg, #3a2741 0%, #5B3D66 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 50px; height: 50px; border: 1px solid rgba(255,255,255,0.2);">
                        <i class="bi bi-calendar-plus fs-3 text-white"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-800 mb-0" style="font-weight: 800;">Nueva Reservación de Espacio</h4>
                        <small class="opacity-75 fw-bold" id="display_nombre_auditorio" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">SISTEMA INTEGRAL DE RESERVACIÓN DE AUDITORIOS (SIRA)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="regresarAlCatalogo()"></button>
            </div>

            <div class="modal-body p-0 bg-white">

                <div id="paso_catalogo" class="p-4 p-lg-5 animate__animated animate__fadeIn">
                    <div class="d-flex align-items-center mb-4 px-2 pb-3 border-bottom">
                        <span class="badge rounded-pill me-3 shadow-sm" style="background-color: #5B3D66; padding: 10px 16px; font-size: 1rem;">1</span>
                        <h5 class="fw-800 text-uppercase mb-0" style="color: #5B3D66; font-weight: 800;">Selecciona el Auditorio o Espacio</h5>
                    </div>
                    
                    <div class="row g-4 px-2">
                        <?php
                        // Consulta optimizada para auditorios disponibles
                        $auds = mysqli_query($conexion, "SELECT * FROM auditorio WHERE disponibilidad = 1 ORDER BY nombre_espacio ASC");
                        while ($a = mysqli_fetch_assoc($auds)):
                            $ruta_foto = "assets/img/auditorios/" . $a['id_auditorio'] . ".jpg";
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-auditorio-pro h-100 border shadow-sm overflow-hidden" 
                                     style="border-radius: 16px; cursor: pointer; transition: all 0.3s ease; border: 1px solid #eee;"
                                     onclick="irAlCalendario(<?php echo $a['id_auditorio']; ?>, '<?php echo addslashes($a['nombre_espacio']); ?>', '<?php echo addslashes($a['equipamiento_fijo']); ?>')">
                                    
                                    <div class="position-relative" style="height: 160px; overflow: hidden;">
                                        <img src="<?php echo $ruta_foto; ?>" onerror="this.src='assets/img/placeholder.jpg'" style="width: 100%; height: 100%; object-fit: cover;">
                                        <div class="position-absolute bottom-0 start-0 m-3">
                                            <span class="badge bg-white text-dark rounded-pill px-3 py-2 shadow" style="font-size: 0.65rem; font-weight: 700; border: 1px solid #ddd;">
                                                <i class="bi bi-people-fill me-1 text-primary"></i><?php echo $a['capacidad_maxima']; ?> Personas
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-4 bg-white">
                                        <h6 class="fw-bold mb-1 text-dark" style="font-size: 1.1rem;"><?php echo $a['nombre_espacio']; ?></h6>
                                        <p class="text-muted small mb-3"><i class="bi bi-geo-alt-fill text-primary"></i> <?php echo $a['ubicacion']; ?></p>
                                        <div class="d-flex flex-wrap gap-1 border-top pt-3">
                                            <?php
                                            $equipos = explode(',', $a['equipamiento_fijo']);
                                            foreach ($equipos as $e): if (trim($e) != "" && trim($e) != "bszsrbsr"): ?>
                                                <span class="badge bg-light text-muted border fw-normal px-2 py-1" style="font-size: 0.6rem; border-radius: 4px;"><?php echo trim($e); ?></span>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div id="paso_calendario" class="p-4 p-lg-5 animate__animated animate__fadeIn" style="display: none;">
                    <button class="btn btn-sm text-primary fw-bold mb-4 p-0 d-flex align-items-center gap-2" onclick="regresarAlCatalogo()" style="font-size: 0.8rem; text-transform: uppercase;">
                        <i class="bi bi-arrow-left fs-5"></i> Volver al catálogo
                    </button>
                    
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div id="calendar_interactivo" class="shadow-sm border rounded-4 bg-white p-4"></div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border shadow-sm rounded-4 h-100 p-4 bg-white">
                                <h6 class="fw-800 text-uppercase mb-4 pb-2 border-bottom" style="color: #5B3D66; font-weight: 800; font-size: 0.8rem;">Configuración de Horario</h6>

                                <div class="mb-4">
                                    <label class="form-label label-sira-pro">Fecha Seleccionada</label>
                                    <div class="d-flex align-items-center bg-light rounded-3 px-3 py-3 border shadow-inner">
                                        <i class="bi bi-calendar-check fs-5 text-primary me-3"></i>
                                        <input type="text" id="fecha_display" class="form-control bg-transparent border-0 fw-bold p-0 fs-6 text-dark" readonly placeholder="Toca un día en el calendario">
                                    </div>
                                    <input type="hidden" id="fecha_seleccionada">
                                </div>

                                <h6 class="fw-800 text-uppercase mb-2 mt-4" style="color: #5B3D66; font-size: 0.7rem; font-weight: 800;">Bloques de Horas Disponibles:</h6>
                                <p class="text-muted extra-small mb-3" id="fecha_seleccionada_txt">Toca un día para cargar disponibilidad</p>

                                <div id="grid_horarios" class="d-flex flex-wrap gap-2 mb-3"></div>

                                <div class="mt-auto border-top pt-4">
                                    <button id="btnConfirmarHorario" class="btn btn-confirmar-sira w-100 py-3 rounded-pill fw-bold shadow d-flex align-items-center justify-content-center gap-2" disabled onclick="irAlFormularioFinal()">
                                        Confirmar Horario <i class="bi bi-arrow-right-short fs-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="paso_formulario" class="p-4 p-lg-5 animate__animated animate__fadeIn" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <button type="button" class="btn btn-sm text-primary fw-bold p-0 d-flex align-items-center gap-2" onclick="irAlCalendario(auditorioSeleccionado, document.getElementById('display_nombre_final').innerText)" style="font-size: 0.8rem; text-transform: uppercase;">
                            <i class="bi bi-arrow-left fs-5"></i> Cambiar Fecha u Horario
                        </button>
                        <div class="text-end">
                            <h5 id="display_nombre_final" class="fw-800 text-dark mb-0" style="font-weight: 800; font-size: 1.3rem;">Auditorio</h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 border border-primary border-opacity-25" style="font-size: 0.65rem;">
                                <i class="bi bi-check-circle-fill me-1"></i> Espacio Seleccionado
                            </span>
                        </div>
                    </div>

                    <form id="formNuevaReservacion">
                        <input type="hidden" id="id_editando" name="id_editando">
                        <input type="hidden" name="id_auditorio" id="input_id_auditorio">
                        <input type="hidden" name="fecha_evento" id="input_fecha_evento">
                        <input type="hidden" name="hora_inicio" id="input_hora_inicio">
                        <input type="hidden" name="hora_fin" id="input_hora_fin">

                        <div class="row g-5">
                            <div class="col-lg-7 border-end pe-lg-5">
                                <h6 class="fw-800 text-uppercase mb-4 pb-2 border-bottom" style="color: #5B3D66; font-weight: 800; font-size: 0.8rem;">Información del Evento</h6>
                                
                                <div class="mb-4">
                                    <label class="form-label label-sira-pro">Nombre del Evento</label>
                                    <input type="text" name="titulo" class="form-control input-sira-pro" placeholder="Escribe el nombre aquí..." required>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label label-sira-pro">Cantidad de Asistentes</label>
                                        <div class="input-group input-group-sira-pro rounded-3 overflow-hidden border">
                                            <span class="input-group-text border-0 bg-transparent ps-3"><i class="bi bi-people text-primary fs-5"></i></span>
                                            <input type="number" name="num_asistentes" id="num_asistentes_input" class="form-control border-0 p-3" placeholder="Cantidad" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label label-sira-pro">Otros Requerimientos</label>
                                        <input type="text" name="otros_servicios" class="form-control input-sira-pro" placeholder="Ej. Cafetería, presídium...">
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label label-sira-pro">Justificación o Descripción</label>
                                    <textarea name="descripcion" class="form-control input-sira-pro" rows="4" placeholder="Propósito del evento..." required></textarea>
                                </div>
                            </div>

                            <div class="col-lg-5 ps-lg-4">
                                <h6 class="fw-800 text-uppercase mb-4 pb-2 border-bottom" style="color: #5B3D66; font-weight: 800; font-size: 0.8rem;">Resumen Visual</h6>
                                
                                <div class="mb-4 text-center bg-light rounded-4 p-3 border shadow-inner">
                                    <img id="img_final_preview" src="assets/img/placeholder.jpg" class="rounded-3 shadow-sm mb-3 border" style="width: 100%; height: 130px; object-fit: cover;">
                                    <div class="d-flex align-items-center justify-content-center py-2 px-3 rounded-pill bg-white border mx-auto shadow-sm" style="width: fit-content;">
                                        <i class="bi bi-people-fill text-primary me-2"></i>
                                        <span class="text-muted fw-bold extra-small">CAPACIDAD MÁX.: <span class="text-dark fs-6" id="capacidad_numero_txt">0</span></span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-6 border-end">
                                        <label class="fw-bold text-primary d-block mb-2 text-uppercase" style="font-size: 0.6rem;">INCLUYE:</label>
                                        <div id="check_equipamiento_fijo" class="text-muted extra-small" style="line-height: 1.5; min-height: 70px;"></div>
                                    </div>

                                    <div class="col-6 ps-3 sira-checks-pro-success">
                                        <label class="fw-bold text-success d-block mb-2 text-uppercase" style="font-size: 0.6rem;">SOLICITAR EXTRAS:</label>
                                        <div class="form-check-group" style="font-size: 0.75rem;">
                                            <div class="mb-2 custom-checkbox"><input class="form-check-input" type="checkbox" name="extras[]" value="Proyector" id="ckP"> <label class="form-check-label ms-1" for="ckP">Proyector</label></div>
                                            <div class="mb-2 custom-checkbox"><input class="form-check-input" type="checkbox" name="extras[]" value="Extensiones" id="ckE"> <label class="form-check-label ms-1" for="ckE">Extensiones</label></div>
                                            <div class="mb-2 custom-checkbox"><input class="form-check-input" type="checkbox" name="extras[]" value="Mobiliario" id="ckM"> <label class="form-check-label ms-1" for="ckM">Mobiliario Extra</label></div>
                                            <div class="mb-0 custom-checkbox"><input class="form-check-input" type="checkbox" name="extras[]" value="Microfono" id="ckMic"> <label class="form-check-label ms-1" for="ckMic">Micrófono</label></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 d-flex justify-content-between align-items-center border-top pt-4">
                            <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none small" onclick="regresarAlCatalogo()">Cancelar Reservación</button>
                            <button type="submit" class="btn btn-enviar-sira px-5 py-3 rounded-pill fw-bold shadow-lg d-flex align-items-center gap-2 fs-6">
                                Enviar Solicitud SIRA <i class="bi bi-send-fill ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* VARIABLES DE COLOR - SIRA DESIGN SYSTEM */
    :root {
        --sira-purple-primary: #5B3D66;
        --sira-purple-dark: #3a2741;
        --sira-green-success: #2e7d32; 
    }

    /* 1. INTERACTIVIDAD DEL CALENDARIO */
    .fc-daygrid-day:hover { background-color: rgba(91, 61, 102, 0.08) !important; cursor: pointer; transition: 0.2s ease; }

    /* Estilos para el día seleccionado en Morado UTM */
    .fc-day-selected, .fc-daygrid-day.bg-primary.bg-opacity-10 {
        background-color: rgba(91, 61, 102, 0.15) !important;
        border: 2px solid var(--sira-purple-primary) !important;
        z-index: 5;
    }

    .fc-daygrid-day-number { color: var(--sira-purple-primary) !important; font-weight: 800 !important; text-decoration: none !important; }
    .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 800 !important; color: var(--sira-purple-dark); text-transform: uppercase; }

    /* Botonera de navegación del Calendario */
    .fc-prev-button.fc-button, .fc-next-button.fc-button {
        background-color: var(--sira-purple-dark) !important;
        border-color: var(--sira-purple-dark) !important;
    }
    .fc-prev-button.fc-button:hover, .fc-next-button.fc-button:hover { background-color: var(--sira-purple-primary) !important; }

    /* 2. BOTONES DINÁMICOS */
    #btnConfirmarHorario { background-color: var(--sira-purple-primary) !important; border: none !important; color: white !important; transition: 0.3s; }

    #btnConfirmarHorario:disabled {
        background-color: #eee !important;
        color: #bbb !important;
        cursor: not-allowed !important;
        opacity: 0.7;
    }

    .btn-enviar-sira {
        background: linear-gradient(135deg, var(--sira-purple-primary) 0%, var(--sira-purple-dark) 100%) !important;
        color: white !important;
        border: none !important;
    }

    .btn-enviar-sira:hover { transform: translateY(-3px); shadow: 0 8px 20px rgba(91, 61, 102, 0.3) !important; }

    /* 3. INPUTS Y ESTILOS UTM CLEAN */
    .label-sira-pro { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #888; display: block; letter-spacing: 0.5px; }

    .input-sira-pro, .input-group-sira-pro {
        background-color: #f8f9fa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 12px !important;
        padding: 12px 18px !important;
        font-size: 0.95rem !important;
        transition: all 0.3s ease;
    }

    .input-sira-pro:focus, .input-group-sira-pro:focus-within {
        background-color: #fff !important;
        border-color: var(--sira-purple-primary) !important;
        box-shadow: 0 0 0 4px rgba(91, 61, 102, 0.05) !important;
    }

    /* 4. COMPONENTES DE VALIDACIÓN VISUAL */
    .sira-checks-pro-success .form-check-input:checked {
        background-color: var(--sira-green-success) !important;
        border-color: var(--sira-green-success) !important;
    }

    .btn-horario {
        border: 1px solid var(--sira-purple-primary) !important;
        color: var(--sira-purple-primary) !important;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 8px 12px !important;
        transition: 0.2s;
    }

    .btn-horario.activo { background-color: var(--sira-purple-primary) !important; color: white !important; }
</style>