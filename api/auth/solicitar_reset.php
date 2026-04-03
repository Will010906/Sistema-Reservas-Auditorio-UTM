<?php
// 1. Configuración de cabeceras (Solo debe haber una salida JSON)
header('Content-Type: application/json');

// 2. Importar conexión y PHPMailer
include("../../config/db_local.php");
include("../../config/mail_config.php");

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3. Obtener datos del frontend
$data = json_decode(file_get_contents("php://input"), true);
$correo = $data['correo'] ?? '';

if (empty($correo)) {
    echo json_encode(["success" => false, "error" => "Por favor, ingresa tu correo electrónico."]);
    exit();
}

try {
    // 4. Verificar existencia del usuario
    $stmt = $conexion->prepare("SELECT nombre FROM usuarios WHERE correo_electronico = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $nombre = $usuario['nombre'];

        // 5. Generar Token y Expiración (1 hora)
        $token = bin2hex(random_bytes(20));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // 6. Actualizar Base de Datos con el Token
        $update = $conexion->prepare("UPDATE usuarios SET reset_token = ?, token_expira = ? WHERE correo_electronico = ?");
        $update->bind_param("sss", $token, $expira, $correo);

        if ($update->execute()) {
            // 7. Configuración de Envío de Correo
            $mail = new PHPMailer(true);

            // Configuración SMTP para Gmail/Outlook institucional
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // Bypass para certificados SSL en XAMPP local
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Remitente y Destinatario
           $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($correo, $nombre);

            // Contenido del Email
            $mail->isHTML(true);
            $mail->Subject = 'Restablecer Contraseña - SIRA UTM';
            $url = "http://localhost/RAUTM/restablecer.php?token=" . $token;

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 10px; padding: 20px; background-color: #ffffff;'>
                    <div style='text-align: center; border-bottom: 2px solid #5B3D66; padding-bottom: 10px;'>
                        <h2 style='color: #5B3D66;'>SIRA UTM</h2>
                    </div>
                    <p style='color: #333; font-size: 16px;'>Hola, <strong>$nombre</strong>,</p>
                    <p style='color: #555;'>Recibimos una solicitud para restablecer tu contraseña. Si no fuiste tú, puedes ignorar este mensaje.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$url' style='background-color: #5B3D66; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>RESTABLECER MI CONTRASEÑA</a>
                    </div>
                    <p style='color: #888; font-size: 12px; text-align: center;'>Este enlace expirará en 60 minutos.</p>
                </div>";

            // 8. Enviar y responder
            if ($mail->send()) {
                echo json_encode(["success" => true, "message" => "El enlace ha sido enviado con éxito a tu correo institucional."]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Error al generar la solicitud de seguridad."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "El correo ingresado no coincide con ningún registro."]);
    }
} catch (Exception $e) {
    // Si falla PHPMailer, capturamos el error específico
    echo json_encode(["success" => false, "error" => "Error al enviar el correo: " . $mail->ErrorInfo]);
} finally {
    $conexion->close();
}
