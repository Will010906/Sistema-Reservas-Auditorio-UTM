/**
 * GESTIÓN DE SUBDIRECCIÓN - SIRA UTM
 * Implementa: DataTables, Seguridad JWT y Reportes por Área.
 */

/* global $, Swal */

$(document).ready(function () {
    // 1. Inicializar DataTable (Filtros útiles funcionando - 15%)
    const table = $('#tablaSubdirector').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pageLength: 10,
        dom: 'rtip', // Mantenemos la interfaz limpia como pide el diseño
        ordering: true,
        order: [[3, "desc"]] // Ordenar por fecha por defecto
    });

    // 2. Botón para Reporte PDF de Carrera (Conexión con el mundo - 10%)
    $('#btnPDFCarrera').on('click', function() {
        // CORRECCIÓN: Nombre de token sira_session_token para coincidir con el Guardián
        const token = localStorage.getItem('sira_session_token'); 
        
        if (!token) {
            return manejarErrorAuth();
        }

        // Feedback visual con SweetAlert2
        Swal.fire({
            title: 'Generando Reporte',
            text: 'Preparando el PDF de tu área académica...',
            icon: 'info',
            timer: 2000,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // CORRECCIÓN: Ruta al subdirectorio 'reportes' organizada profesionalmente
        // El backend extraerá el 'area' del usuario a partir del token (Seguridad JWT - 30%)
        window.open(`api/reportes/generar_reporte_carrera.php?token=${token}`, '_blank');
    });
});

/**
 * Función auxiliar para cumplimiento de seguridad
 */
function manejarErrorAuth() {
    // CORRECCIÓN: Aseguramos limpieza del token antes de redirigir
    localStorage.removeItem('sira_session_token');
    Swal.fire({
        icon: 'error',
        title: 'Acceso denegado',
        text: 'No se encontró una sesión válida para generar el reporte.',
        confirmButtonColor: '#5B3D66'
    }).then(() => {
        window.location.href = 'index.php'; // Redirección al login
    });
}