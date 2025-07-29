<?php
// ajustar_fechas.php

include __DIR__ . "/connect.php";

date_default_timezone_set('UTC'); // puedes cambiar esto a tu zona real si lo prefieres
$zona_actual = date_default_timezone_get();
$fecha_actual = date('Y-m-d H:i:s');

// Mostrar zona horaria actual
echo "<h3>Zona horaria actual del servidor: <b>$zona_actual</b></h3>";
echo "<p>Fecha/hora actual según PHP: <b>$fecha_actual</b></p>";

// Estimar diferencia horaria
$zona_real = 'America/Mexico_City'; // zona deseada
$dt_actual = new DateTime("now", new DateTimeZone($zona_actual));
$dt_deseada = new DateTime("now", new DateTimeZone($zona_real));

$offset = ($dt_deseada->getTimestamp() - $dt_actual->getTimestamp()) / 3600;

echo "<p>Diferencia estimada entre <b>$zona_actual</b> y <b>$zona_real</b>: <b>$offset horas</b></p>";

// Confirmar antes de aplicar
if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'si') {
    $tablas = [
        "pedido" => ["fec_crea", "fec_mod"],
        "pedido_det" => ["fec_crea", "fec_mod"]
    ];

    foreach ($tablas as $tabla => $campos) {
        foreach ($campos as $campo) {
            $sql = "UPDATE $tabla SET $campo = DATE_ADD($campo, INTERVAL ? HOUR)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("d", $offset);
            $stmt->execute();
            echo "<p>✅ Ajustadas fechas en <b>$tabla.$campo</b></p>";
        }
    }

    echo "<hr><b>Todas las fechas han sido actualizadas correctamente.</b>";
} else {
    echo "<hr>";
    echo "<p>⚠️ Esta acción modificará <b>todas las fechas</b> de la base de datos en las tablas <code>pedido</code> y <code>pedido_det</code>.</p>";
    echo "<p><a href='?confirmar=si'>✅ Haz clic aquí para confirmar y aplicar el cambio</a></p>";
    echo "<p>O simplemente <b>cierra esta página</b> si no estás seguro.</p>";
}
?>
