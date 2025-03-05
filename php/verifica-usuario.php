<?php
$cve_usuario = $_GET["wk_usua"]; // Este valor puede ser dinámico dependiendo de tu implementación

if ($cve_usuario == ""){
    echo '<div class="alert alert-danger">ACCESO DENEGADO</div>';
    return;
}
?>