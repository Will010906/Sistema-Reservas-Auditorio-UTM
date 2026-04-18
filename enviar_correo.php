<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * SIRA - SERVICIO DE ENVÍO DE CORREO
 * @author Wilmer (UTM)
 *
 */

// 1. Cargamos la configuración centralizada y la librería
require 'config/mail_config.php'; // Asegúrate de que la ruta sea correcta
// Reemplaza los 3 require por este solo:
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validamos que los campos no vengan vacíos (Seguridad básica)
    if(empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['message'])) {
        echo "<script>alert('Por favor, llena todos los campos obligatorios.'); window.history.back();</script>";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURACIÓN DEL SERVIDOR SMTP USANDO CONSTANTES ---
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST; 
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER; 
        $mail->Password   = SMTP_PASS; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8'; // Para que salgan bien los acentos

        // --- DESTINATARIOS ---
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress(SMTP_FROM); // Se envía al correo de soporte definido
        $mail->addReplyTo($_POST['email'], $_POST['nombre']); // Para responderle directo al usuario

        // --- CONTENIDO DEL CORREO (Diseño UTM) ---
        $mail->isHTML(true);
        $mail->Subject = 'Nuevo mensaje desde SIRA-UTM: ' . $_POST['nombre'];
        
        // Cuerpo con mejor estructura visual
        $cuerpo = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
            <h2 style='color: #5B3D66; border-bottom: 2px solid #5B3D66; padding-bottom: 10px;'>Nuevo Contacto SIRA</h2>
            <p><strong>Nombre del solicitante:</strong> " . htmlspecialchars($_POST['nombre']) . "</p>
            <p><strong>Correo electrónico:</strong> " . htmlspecialchars($_POST['email']) . "</p>
            <p><strong>Teléfono de contacto:</strong> " . htmlspecialchars($_POST['phone']) . "</p>
            <div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 10px;'>
                <strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($_POST['message'])) . "
            </div>
            <p style='font-size: 10px; color: #888; margin-top: 20px;'>Este es un mensaje automático generado por el Sistema SIRA - UTM 2026.</p>
        </div>";

        $mail->Body = $cuerpo;

        $mail->send();
        echo "<script>alert('¡Mensaje enviado con éxito! El equipo de soporte SIRA te contactará pronto.'); window.location.href='index.php';</script>";

    } catch (Exception $e) {
        // Log de error interno para el administrador
        error_log("Error de PHPMailer: {$mail->ErrorInfo}");
        echo "<script>alert('Error al enviar el mensaje. Intenta más tarde.'); window.history.back();</script>";
    }
}
?>