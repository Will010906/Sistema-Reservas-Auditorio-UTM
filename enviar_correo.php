<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar la librería (Ajusta la ruta si tu carpeta se llama distinto)
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURACIÓN DEL SERVIDOR SMTP ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sira.utmorelia@gmail.com'; // TU GMAIL AQUÍ
        $mail->Password   = 'hlkx fwlh uezn fler'; // TU CONTRASEÑA DE APLICACIÓN DE 16 LETRAS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- DESTINATARIOS ---
        $mail->setFrom('sira.utmorelia@gmail.com', 'Sistema SIRA');
        $mail->addAddress('sira.utmorelia@gmail.com'); // A QUIÉN LE LLEGA

        // --- CONTENIDO DEL CORREO ---
        $mail->isHTML(true); // Enviar como HTML
        $mail->Subject = 'Nuevo mensaje desde SIRA-UTM';
        
        // Diseñamos el cuerpo del correo con un poco de estilo
        $cuerpo = "<h1>Nuevo mensaje de contacto</h1>";
        $cuerpo .= "<p><strong>Nombre:</strong> " . $_POST['nombre'] . "</p>";
        $cuerpo .= "<p><strong>Email:</strong> " . $_POST['email'] . "</p>";
        $cuerpo .= "<p><strong>Teléfono:</strong> " . $_POST['phone'] . "</p>";
        $cuerpo .= "<p><strong>Mensaje:</strong><br>" . nl2br($_POST['message']) . "</p>";

        $mail->Body = $cuerpo;

        $mail->send();
        // Aqui lo que hacemos es redirigir a la pagina principal
        echo "<script>alert('¡Mensaje enviado con PHPMailer!'); window.location.href='index.php';</script>";

    } catch (Exception $e) {
        echo "Error al enviar: {$mail->ErrorInfo}";
    }
}
?>