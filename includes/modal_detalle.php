<div class="modal fade" id="bsModalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header text-white" style="background-color: var(--sira-purple-dark); border-radius: 25px 25px 0 0;">
                <h5 class="modal-title fw-bold" id="detFolio">Detalle de Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <label class="text-muted small fw-bold text-uppercase">Evento</label>
                        <p class="fw-bold fs-5 mb-3" id="detTituloEv">---</p>
                        
                        <label class="text-muted small fw-bold text-uppercase">Solicitante</label>
                        <div class="d-flex align-items-center mb-3">
                            <span id="detUsuarioNombre" class="fw-600">---</span>
                            <a id="btnWhatsApp" href="#" target="_blank" class="btn btn-sm btn-outline-success border-0 ms-2 p-0">
                                <i class="bi bi-whatsapp" style="font-size: 1.1rem;"></i>
                            </a>
                        </div>

                        <label class="text-muted small fw-bold text-uppercase">Auditorio</label>
                        <p class="fw-bold text-primary mb-1" id="detAuditorio">---</p>
                        <div id="detAsistentes" class="small mb-3">---</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold text-uppercase">Fecha y Hora</label>
                        <p class="mb-1" id="detFechaEvento">--/--/----</p>
                        <p class="small text-muted mb-3" id="detHorario">--:-- - --:--</p>
                        
                        <label class="text-muted small fw-bold text-uppercase">Descripción</label>
                        <div class="p-2 bg-light rounded mb-3" id="detDescription" style="font-size: 0.85rem; min-height: 50px; border: 1px solid #eee;">---</div>
                        
                        <label class="text-muted small fw-bold text-uppercase">Equipamiento Especial</label>
                        <div id="detEquipamiento" class="d-flex flex-wrap gap-2 mt-1">
                            <span class="text-muted small italic">Cargando recursos...</span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row align-items-center">
                    <div class="col-md-7">
                        <textarea id="motivoRechazo" class="form-control border-0 bg-light shadow-sm" rows="2" 
                                  placeholder="Opcional: Motivo de rechazo o instrucciones..." 
                                  style="border-radius: 12px; font-size: 0.9rem;"></textarea>
                    </div>
                    <div class="col-md-5 d-flex gap-2">
                        <button class="btn btn-success flex-fill fw-bold rounded-pill py-2 shadow-sm" 
                                onclick="procesarSolicitud('ACEPTADA')">Aceptar</button>
                        
                        <button class="btn btn-danger flex-fill fw-bold rounded-pill py-2 shadow-sm" 
                                onclick="procesarSolicitud('RECHAZADA')">Rechazar</button>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pb-4 d-flex justify-content-center">
                <button class="btn btn-link text-danger text-decoration-none small fw-bold" 
                        onclick="eliminarSolicitudDesdeModal()">
                    <i class="bi bi-trash3 me-1"></i> Eliminar Solicitud Permanentemente
                </button>
            </div>
        </div>
    </div>
</div>