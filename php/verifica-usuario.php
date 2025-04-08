<?php
$cve_usuario = $_GET["cve_usuario"]; 

if ($cve_usuario == ""){
    echo '<div class="alert alert-danger">ACCESO DENEGADO</div>';
    return;
}
?>