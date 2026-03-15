<?php
/**
 * MÓDULO DE AUTENTICACIÓN Y CONTROL DE SESIONES
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Procesa el inicio de sesión comparando credenciales con la base de datos.
 * Seguridad: Utiliza password_verify() para validar contraseñas cifradas (Hash).
 */
session_start();
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpieza de datos: trim() elimina espacios y mysqli_real_escape_string evita inyección SQL
    $matricula = trim(mysqli_real_escape_string($conexion, $_POST['matricula']));
    $password_ingresado = trim($_POST['password']);

    // Búsqueda del usuario por su identificador único (matrícula)
    $query = "SELECT id_usuario, nombre, perfil, password FROM usuarios WHERE matricula='$matricula'";
    $resultado = mysqli_query($conexion, $query);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        /**
         * Verificación de Contraseña:
         * Compara el texto plano ingresado con el hash almacenado en la base de datos.
         */
        if (password_verify($password_ingresado, $usuario['password'])) {

            // Registro de variables de sesión para persistencia en el sistema
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['perfil'] = $usuario['perfil'];

            /**
             * Redireccionamiento basado en Roles:
             * Administradores y Subdirectores -> Panel Administrativo
             * Alumnos y otros perfiles -> Panel de Usuario
             */
            if ($usuario['perfil'] == 'administrador' || $usuario['perfil'] == 'subdirector') {
                header("Location: ../panel_admin.php");
            } else {
                header("Location: ../panel_usuario.php");
            }
            exit();
        } else {
            // Error de contraseña
            echo "<script>alert('La contraseña no coincide con el registro.'); window.location='../index.php';</script>";
        }
    } else {
        // Error de matrícula inexistente
        echo "<script>alert('La matrícula $matricula no existe.'); window.location='../index.php';</script>";
    }
}
?>