<div class="modal fade" id="bsModalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background-color: #fff;">
            
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, var(--sira-purple-dark) 0%, var(--sira-purple-primary) 100%); border: none;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text fs-5"></i>
                    <div>
                        <small class="text-uppercase opacity-75 fw-bold" style="letter-spacing: 1px; font-size: 0.6rem;">Gestión de Reservación UTM</small>
                        <h5 class="modal-title fs-5 fw-bold mb-0" id="detFolio">FOL-046</h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6 border-end">
                        <div class="mb-3">
                            <span class="badge mb-1" style="background-color: var(--sira-purple-primary); font-size: 0.6rem;">EVENTO</span>
                            <h4 class="fw-bold text-dark mb-0" id="detTituloEv" style="font-size: 1.4rem; line-height: 1.2;">---</h4>
                        </div>

                        <div class="p-3 rounded-3 bg-light mb-3 border-start border-4" style="border-color: var(--sira-purple-primary) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0" id="detUsuarioNombre" style="font-size: 0.9rem;">---</h6>
                                <a id="btnWhatsApp" href="#" target="_blank" class="text-success ms-2">
                                    <i class="bi bi-whatsapp fs-5"></i>
                                </a>
                            </div>
                            <small id="detCarrera" class="text-primary fw-bold d-block mb-2" style="font-size: 0.7rem;">---</small>
                            <div class="d-flex flex-wrap gap-2 text-muted" style="font-size: 0.65rem;">
                                <span><i class="bi bi-id-card"></i> <span id="detMatricula">---</span></span>
                                <span><i class="bi bi-envelope"></i> <span id="detCorreo">---</span></span>
                            </div>
                        </div>

                        <label class="text-muted small fw-bold text-uppercase mb-2 d-block" style="font-size: 0.6rem;">Espacio Asignado</label>
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white border shadow-sm">
                            <div class="bg-primary-subtle text-primary rounded p-2">
                                <i class="bi bi-building fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 fw-bold small text-dark" id="detAuditorio">---</p>
                                <small id="detAsistentes" class="text-muted" style="font-size: 0.65rem;">---</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase mb-2 d-block" style="font-size: 0.6rem;">Fecha y Horario</label>
                        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3" style="background-color: #f3e5f5;">
                            <i class="bi bi-calendar-check fs-4 text-primary"></i>
                            <div>
                                <p class="mb-0 fw-bold small text-dark" id="detFechaEvento">--/--/----</p>
                                <p class="mb-0 text-muted" id="detHorario" style="font-size: 0.7rem;">--:-- a --:--</p>
                            </div>
                        </div>

                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block" style="font-size: 0.6rem;">Descripción</label>
                        <div id="detDescription" class="small text-muted p-2 bg-light rounded mb-3" style="font-size: 0.75rem; max-height: 80px; overflow-y: auto;">
                            ---
                        </div>

                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block" style="font-size: 0.6rem;">Requerimientos</label>
                        <div id="detEquipamiento" class="d-flex flex-wrap gap-1"></div>
                    </div>
                </div>

                <div id="seccionBitacoraAdmin" class="mt-3 pt-3 border-top" style="display: none;">
                    <div class="alert bg-danger-subtle border-0 p-2 mb-3 d-flex align-items-center gap-2" style="border-radius: 10px;">
                        <i class="bi bi-info-circle text-danger fs-6"></i>
                        <small id="detBitacoraTexto" class="text-dark" style="font-size: 0.75rem;">---</small>
                    </div>

                    <div class="row g-2">
                        <div class="col-12">
                            <textarea id="motivoRechazo" class="form-control border-0 bg-light p-2 shadow-sm" rows="1"
                                placeholder="Opcional: Motivo de rechazo o instrucciones..." style="border-radius: 10px; font-size: 0.8rem;"></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-success flex-fill fw-bold py-2 shadow-sm border-0 d-flex align-items-center justify-content-center gap-2"
                                onclick="procesarSolicitud('ACEPTADA')" style="font-size: 0.85rem; border-radius: 10px;">
                                <i class="bi bi-check-circle"></i> Confirmar
                            </button>
                            <button class="btn btn-warning flex-fill fw-bold py-2 shadow-sm text-white border-0 d-flex align-items-center justify-content-center gap-2"
                                onclick="prepararReasignacion()" style="font-size: 0.85rem; border-radius: 10px;">
                                <i class="bi bi-arrow-left-right"></i> Reasignar
                            </button>
                            <button class="btn btn-danger flex-fill fw-bold py-2 shadow-sm border-0 d-flex align-items-center justify-content-center gap-2"
                                onclick="procesarSolicitud('RECHAZADA')" style="font-size: 0.85rem; border-radius: 10px;">
                                <i class="bi bi-x-circle"></i> Rechazar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 pb-3 justify-content-center">
                <button class="btn btn-link text-danger text-decoration-none fw-bold p-0 d-flex align-items-center gap-1"
                    onclick="eliminarSolicitudDesdeModal()" style="font-size: 0.65rem; opacity: 0.6;">
                    <i class="bi bi-trash3-fill"></i> Eliminar Registro
                </button>
            </div>
        </div>
    </div>
</div>