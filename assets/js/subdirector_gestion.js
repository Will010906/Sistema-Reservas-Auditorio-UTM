$(document).ready(function () {
    // Inicializar DataTable con idioma español
    const table = $('#tablaSubdirector').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pageLength: 10,
        dom: 'rtip' // Sin buscador general para mantenerlo limpio
    });

    // Botón para Reporte PDF de Carrera
    $('#btnPDFCarrera').on('click', function() {
        const area = "<?php echo $mi_area; ?>";
        // Abrimos el reporte pasando el área como filtro
        window.open(`modules/generar_reporte_carrera.php?area=${encodeURIComponent(area)}`, '_blank');
    });
});