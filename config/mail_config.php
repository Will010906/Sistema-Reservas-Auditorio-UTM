<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * CAPA DE SERVICIOS: CONFIGURACIÓN DE PROTOCOLO SMTP (MENSAJERÍA)
 * * @package     Config
 * @subpackage  Mail_Services
 * @version     1.0.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Define las constantes globales para la integración de PHPMailer o el motor 
 * de correo del sistema. Utiliza el protocolo SMTP (Simple Mail Transfer Protocol) 
 * bajo cifrado TLS para garantizar la entrega segura de folios y tokens.
 * * PARÁMETROS DE COMUNICACIÓN:
 * - Host: Servidor de retransmisión de Google (Gmail).
 * - Port: Puerto 587 (Standard para TLS).
 * - Auth: Autenticación mediante contraseñas de aplicación (App Passwords).
 */

// CONFIGURACIÓN DEL SERVIDOR DE SALIDA (THE "CARTERO")
define('SMTP_HOST', 'smtp.gmail.com');          // Nodo de retransmisión SMTP
define('SMTP_USER', 'alcantarwil@gmail.com');   // Credencial de identidad del sistema
define('SMTP_PASS', 'hmqd cqjv tyjh qlbr');      // Clave de acceso cifrada para aplicaciones
define('SMTP_PORT', 587);                       // Puerto de enlace seguro TLS

// IDENTIDAD DEL REMITENTE INSTITUCIONAL
define('SMTP_FROM', 'alcantarwil@gmail.com');   // Dirección de retorno de mensajería
define('SMTP_FROM_NAME', 'SIRA UTM - Soporte'); // Alias visual para el solicitante

/**
 * NOTA TÉCNICA PARA DESPLIEGUE:
 * Al migrar al servidor de producción de la UTM, estos valores deberán 
 * actualizarse por el Host y las credenciales del servidor de correo 
 * oficial de la Universidad.
 */