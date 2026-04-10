<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * CONTROLADOR: SOLICITUD DE RECUPERACIÓN DE CONTRASEÑA
 * * @package     Controladores_API
 * @subpackage  Seguridad_Correo
 * @version     1.1.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Este servicio gestiona el inicio del flujo de recuperación de cuenta (Forgot Password).
 * Realiza una validación de identidad, genera un identificador único temporal (Token)
 * con expiración programada y despacha un correo electrónico institucional mediante
 * el protocolo SMTP utilizando la librería PHPMailer.
 * * SEGURIDAD:
 * 1. Tokens Criptográficos: Generados con random_bytes para alta entropía.
 * 2. Sentencias Preparadas: Protección contra Inyección SQL en consultas de usuario.
 * 3. Bypass SSL Seguro: Configurado para compatibilidad con entornos de desarrollo locales (XAMPP).
 */

// Establecer cabecera para respuesta estrictamente en formato JSON
header('Content-Type: application/json');

/**
 * IMPORTACIÓN DE RECURSOS Y LIBRERÍAS
 */
include("../../config/db_local.php");     // Conexión a la Base de Datos
include("../../config/mail_config.php"); // Constantes de configuración SMTP

// Carga de clases de PHPMailer (Manual, siguiendo estructura de archivos local)
require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * 1. RECEPCIÓN DE DATOS DEL CLIENTE
 */
$data = json_decode(file_get_contents("php://input"), true);
$correo = $data['correo'] ?? '';

if (empty($correo)) {
    echo json_encode(["success" => false, "error" => "Por favor, ingresa tu correo institucional."]);
    exit();
}

try {
    /**
     * 2. VERIFICACIÓN DE IDENTIDAD INSTITUCIONAL
     * Consulta la existencia del correo para prevenir el envío a cuentas inexistentes.
     */
    $stmt = $conexion->prepare("SELECT nombre FROM usuarios WHERE correo_electronico = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $nombre = $usuario['nombre'];

        /**
         * 3. GENERACIÓN DE TOKEN DE SEGURIDAD
         * @var string $token  Cadena hexadecimal de 40 caracteres (seguridad criptográfica).
         * @var string $expira Marca de tiempo válida por 60 minutos.
         */
        $token = bin2hex(random_bytes(20));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        /**
         * 4. PERSISTENCIA DEL TOKEN
         * Almacena el token de recuperación vinculado a la cuenta del solicitante.
         */
        $update = $conexion->prepare("UPDATE usuarios SET reset_token = ?, token_expira = ? WHERE correo_electronico = ?");
        $update->bind_param("sss", $token, $expira, $correo);

        if ($update->execute()) {
            
            /**
             * 5. CONFIGURACIÓN DEL MOTOR DE CORREO (SMTP)
             */
            $mail = new PHPMailer(true);

            // Parámetros del Servidor SMTP
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            /**
             * CONFIGURACIÓN SSL PARA ENTORNOS LOCALES
             * Desactiva la verificación de pares para evitar fallos por falta de 
             * certificados CA en entornos de desarrollo local (XAMPP/WAMP).
             */
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Definición de Intervinientes (Remitente y Destinatario)
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($correo, $nombre);

            /**
             * 6. CONSTRUCCIÓN DEL MENSAJE (MAQUETACIÓN HTML)
             */
            $mail->isHTML(true);
            $mail->Subject = 'Restablecer Contraseña - SIRA UTM';
            
            // Definición de URL de acción (Ruta relativa al servidor local/remoto)
            $url = "http://localhost/RAUTM/restablecer.php?token=" . $token;

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 10px; padding: 20px; background-color: #ffffff;'>
                    <div style='text-align: center; border-bottom: 2px solid #5B3D66; padding-bottom: 10px;'>
                        <h2 style='color: #5B3D66;'>SIRA UTM</h2>
                    </div>
                    <p style='color: #333; font-size: 16px;'>Hola, <strong>$nombre</strong>,</p>
                    <p style='color: #555;'>Recibimos una solicitud para restablecer tu contraseña de acceso al sistema SIRA. Si no realizaste esta acción, ignora este mensaje.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$url' style='background-color: #5B3D66; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>RESTABLECER MI CONTRASEÑA</a>
                    </div>
                    <p style='color: #888; font-size: 12px; text-align: center;'>Este enlace es de uso único y expirará en un periodo de 60 minutos.</p>
                </div>";

            /**
             * 7. DESPACHO DEL EMAIL Y RESPUESTA FINAL
             */
            if ($mail->send()) {
                echo json_encode([
                    "success" => true, 
                    "message" => "El enlace de seguridad ha sido enviado a tu correo institucional con éxito."
                ]);
            }
        } else {
            throw new Exception("Fallo en la actualización de seguridad en la base de datos.");
        }
    } else {
        echo json_encode([
            "success" => false, 
            "error"   => "El correo electrónico ingresado no coincide con los registros institucionales de la UTM."
        ]);
    }
} catch (Exception $e) {
    /**
     * GESTIÓN DE EXCEPCIONES DE CORREO
     * Captura errores específicos de PHPMailer (ej. credenciales SMTP inválidas).
     */
    echo json_encode([
        "success" => false, 
        "error"   => "Error técnico al procesar el envío: " . ($mail->ErrorInfo ?? $e->getMessage())
    ]);
} finally {
    /**
     * LIMPIEZA DE CONEXIÓN
     */
    $conexion->close();
}