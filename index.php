<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya hay sesión iniciada de técnico o admin, ir al panel de control
if (!empty($_SESSION['optimait_user'])) {
    header('Location: tecnico.php');
} else {
    // Pantalla principal: Login de acceso
    header('Location: login.php');
}
exit;
