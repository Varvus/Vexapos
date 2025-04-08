<?php

include "php/error-reporting.php";

include __DIR__ . "/connect.php";

echo "<pre>";
print_r($_POST);
echo "</pre>";
exit;

$cve_usuario = $_POST["cve_usuario"];
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

// Obtener el siguiente cve_producto para ese usuario
$sql = "SELECT COALESCE(MAX(cve_producto), 0) + 1 AS next_cve_producto FROM producto WHERE cve_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cve_producto = $row['next_cve_producto'];

// Insertar nuevo producto
$sql = "INSERT INTO producto (cve_usuario, cve_producto, nombre, descripcion, activo, inventario, aplica_inventario, fec_crea, fec_mod)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissiii", $cve_usuario, $cve_producto, $nombre, $descripcion, $activo, $inventario, $aplica_inventario);
$stmt->execute();

header("Location: /admin-producto.php");
exit;
?>
