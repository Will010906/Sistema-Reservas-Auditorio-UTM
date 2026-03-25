<?php
/**
 * VISTA: MIS RESERVACIONES - SIRA UTM
 * Actualizado: Carga dinámica vía API y validación de Token.
 */
include("config/db_local.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>
        // SEGURIDAD JWT: Si no hay token, el alumno no ve nada
        const token = localStorage.getItem('token');
        if (!token) { window.location.href = 'index.php?error=expired'; }
    </script>
</head>
<body>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tablaMisReservas">
            <thead>
                <tr class="text-muted x-small fw-bold text-uppercase border-bottom">
                    <th class="ps-4">Folio</th>
                    <th>Título del Evento</th>
                    <th>Auditorio</th>
                    <th>Fecha</th>
                    <th class="text-center">Estatus</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="contenedorMisReservas">
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Cargando tus solicitudes...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/usuario_reservas.js"></script>
</body>
</html>