<?php
/**
 * MÓDULO DE GENERACIÓN DE REPORTES PDF
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Transforma los datos de la base de datos en un documento PDF profesional.
 * Librería utilizada: FPDF.
 */
require('../libs/fpdf/fpdf.php'); 
include('../config/db_local.php');

// 1. Captura de filtros dinámicos desde la URL
$inicio  = $_GET['inicio']  ?? '';
$fin     = $_GET['fin']     ?? '';
$estatus = $_GET['estatus'] ?? ''; 

/**
 * Clase extendida de FPDF para personalizar encabezados y pies de página institucionales.
 */
class PDF extends FPDF {
    function Header() {
        // Título Institucional
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('UNIVERSIDAD TECNOLÓGICA DE MORELIA'), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 10, utf8_decode('SISTEMA DE RESERVACIÓN DE AUDITORIOS'), 0, 1, 'C');
        $this->Ln(5);
        
        // Metadata del reporte
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 10, 'Fecha de impresion: ' . date('d/m/Y H:i'), 0, 1, 'R');
        $this->Line(10, 35, 200, 35); // Línea divisoria decorativa
        $this->Ln(5);
    }

    function Footer() {
        // Posicionamiento a 1.5 cm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// 2. Construcción de consulta SQL dinámica según filtros activos
$where = "WHERE s.fecha_evento BETWEEN '$inicio' AND '$fin'";

if (!empty($estatus)) {
    // Procesamiento de lista de estatus para cláusula IN de SQL
    $estatusArray = explode(',', $estatus);
    $estatusSQL = "'" . implode("','", $estatusArray) . "'";
    $where .= " AND (s.estado IN ($estatusSQL) OR s.prioridad IN ($estatusSQL))";
}

$query = "SELECT s.folio, s.titulo_event, s.fecha_evento, s.estado, s.prioridad, u.nombre as solicitante
          FROM solicitudes s
          JOIN usuarios u ON s.id_usuario = u.id_usuario
          $where
          ORDER BY s.fecha_evento ASC";

$resultado = mysqli_query($conexion, $query);

// 3. Renderizado del documento
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);

// Configuración de la cabecera de la tabla con el color verde oficial de la UTM
$pdf->SetFillColor(0, 104, 71); 
$pdf->SetTextColor(255, 255, 255); // Texto blanco para contraste
$pdf->Cell(25, 10, 'Folio', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Evento', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Solicitante', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Estatus', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0); // Regreso a texto negro para las filas

if (mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Determinación del texto de estatus (Prioridad si está pendiente)
        $estatus_texto = ($row['estado'] == 'Pendiente') ? $row['prioridad'] : $row['estado'];

        $pdf->Cell(25, 8, $row['folio'], 1, 0, 'C');
        $pdf->Cell(70, 8, utf8_decode($row['titulo_event']), 1);
        $pdf->Cell(30, 8, $row['fecha_evento'], 1, 0, 'C');
        $pdf->Cell(35, 8, utf8_decode($row['solicitante']), 1);
        $pdf->Cell(30, 8, strtoupper($estatus_texto), 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No hay registros en el rango seleccionado', 1, 1, 'C');
}

// Salida al navegador para previsualización
$pdf->Output('I', 'Reporte_Reservaciones_UTM.pdf');