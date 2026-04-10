<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * API CONTROLADOR: CONSULTA GLOBAL Y MOTOR DE ANALÍTICA (ADMIN)
 * * @package     Controladores_API
 * @subpackage  Gestion_Solicitudes
 * @version     1.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este endpoint es el motor del Dashboard administrativo. Implementa un 
 * algoritmo de procesamiento post-consulta que clasifica cada solicitud 
 * pendiente según su proximidad temporal, generando un semáforo de 
 * prioridades (Urgente/Demorada/A Tiempo) en tiempo real.
 * * OPTIMIZACIÓN:
 * - Ciclo Único: Realiza el conteo estadístico y la clasificación visual 
 * en una sola iteración para maximizar el rendimiento del servidor.
 */

/**
 * CONFIGURACIÓN DE SALIDA
 */
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE CONEXIÓN INSTITUCIONAL
 */
include '../../config/db_local.php';

try {
    /**
     * 1. INICIALIZACIÓN DE ACUMULADORES (ANALYTICS)
     * Estructura de datos para el llenado dinámico de las Cards informativas.
     */
    $stats = [
        'urgentes'   => 0, // Eventos a 3 días o menos
        'demoradas'  => 0, // Eventos entre 4 y 7 días
        'atiempo'    => 0, // Eventos con más de una semana de margen
        'aceptadas'  => 0,
        'rechazadas' => 0
    ];

    /**
     * 2. CONSOLIDACIÓN DE DATOS (DATA MINING)
     * Ejecuta una triple unión para obtener detalles del solicitante y el espacio.
     */
    $sql_lista = "SELECT s.*, u.nombre as nombre_usuario, a.nombre_espacio 
                  FROM solicitudes s
                  JOIN usuarios u ON s.id_usuario = u.id_usuario
                  JOIN auditorio a ON s.id_auditorio = a.id_auditorio
                  ORDER BY s.fecha_evento ASC";
                  
    $res_lista = mysqli_query($conexion, $sql_lista);
    $solicitudes = [];

    /**
     * 3. MOTOR DE CLASIFICACIÓN DINÁMICA
     * Procesa cada registro para inyectar metadatos de prioridad visual.
     */
    while ($row = mysqli_fetch_assoc($res_lista)) {
        $fecha_evento = new DateTime($row['fecha_evento']);
        $hoy = new DateTime();
        
        // Cálculo de delta de tiempo real (Diferencia de días)
        $diff = $hoy->diff($fecha_evento)->days;
        $estado = strtoupper(trim($row['estado']));

        if ($estado === 'PENDIENTE') {
            /**
             * LÓGICA DE PRIORIZACIÓN INSTITUCIONAL
             * Clasifica el registro para el renderizado de etiquetas en el Frontend.
             */
            if ($diff <= 3) {
                $row['prioridad_visual'] = 'URGENTE'; 
                $stats['urgentes']++;
            } elseif ($diff <= 7) {
                $row['prioridad_visual'] = 'DEMORADA'; 
                $stats['demoradas']++; 
            } else {
                $row['prioridad_visual'] = 'A TIEMPO';
                $stats['atiempo']++;
            }
        } else {
            // Sincronización de estados finales con el diseño de la tabla
            $row['prioridad_visual'] = $estado;
            
            if ($estado === 'ACEPTADA') $stats['aceptadas']++;
            if ($estado === 'RECHAZADA') $stats['rechazadas']++;
        }
        
        $solicitudes[] = $row;
    }

    /**
     * 4. RESPUESTA UNIFICADA
     * Entrega el paquete completo de datos y métricas para el Dashboard.
     */
    echo json_encode([
        "success" => true,
        "stats" => $stats,
        "solicitudes" => $solicitudes
    ]);

} catch (Exception $e) {
    /**
     * GESTIÓN DE EXCEPCIONES TÉCNICAS
     */
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

/**
 * FINALIZACIÓN DEL SCRIPT
 */
exit;