<?php
$cve_usuario = $_GET["cve_usuario"];
$cve_usuario = 1;

if ($cve_usuario == "") {
    echo '<div class="alert alert-danger">ACCESO DENEGADO</div>';
    exit;
}
?>