<?php
// Primero intentar obtener el usuario de alguna fuente disponible
/*if (isset($cve_usuario)) {
    // Ya está definido, no hacer nada
} elseif (isset($_POST['cve_usuario'])) {
    $cve_usuario = intval($_POST['cve_usuario']);
} elseif (isset($_GET['cve_usuario'])) {
    $cve_usuario = intval($_GET['cve_usuario']);
} else {
    $cve_usuario = 0; // No definido
}*/

session_start();
if (!isset($_SESSION['cve_usuario'])) {
    header("Location: login.php");
    exit;
}
$cve_usuario = $_SESSION['cve_usuario'];


/*$cve_usuario = 1;

// Validar que sea válido
if ($cve_usuario <= 0) {
    echo '<div class="alert alert-danger">ACCESO DENEGADO</div>';
    exit;
}*/
?>