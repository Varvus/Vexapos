<?php
include __DIR__ . "/connect.php";

$horas_a_ajustar = -6; // UTC a México (UTC-6), cambia si es distinto

// Tablas y campos a actualizar
$tablas = [
    "pedido" => ["fec_crea", "fec_mod"],
    "pedido_det" => ["fec_crea", "fec_mod"]
];

foreach ($tablas as $tabla => $campos) {
    foreach ($campos as $campo) {
        $sql = "UPDATE $tabla SET $campo = DATE_ADD($campo, INTERVAL ? HOUR)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $horas_a_ajustar);
        $stmt->execute();

        echo "Actualizadas fechas en $tabla.$campo<br>";
    }
}

echo "Fechas corregidas exitosamente.";
?>
