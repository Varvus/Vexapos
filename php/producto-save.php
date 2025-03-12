<?php
include "php/connect.php";

$cve_usuario = $_POST["cve_usuario"];
$cve_producto = $_POST["cve_producto"];
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

// Verificar si el producto ya existe
$sql = "SELECT * FROM producto WHERE cve_usuario = ? AND cve_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cve_usuario, $cve_producto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Si el producto ya existe, actualizarlo
    $sql = "UPDATE producto SET nombre = ?, descripcion = ?, activo = ?, inventario = ?, aplica_inventario = ?, fec_mod = NOW() WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiiii", $nombre, $descripcion, $activo, $inventario, $aplica_inventario, $cve_usuario, $cve_producto);
} else {
    // Si el producto no existe, insertarlo
    $sql = "INSERT INTO producto (cve_usuario, cve_producto, nombre, descripcion, activo, inventario, aplica_inventario, fec_crea, fec_mod) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissiii", $cve_usuario, $cve_producto, $nombre, $descripcion, $activo, $inventario, $aplica_inventario);
}

$stmt->execute();
header("Location: productos.php?cve_usuario=" . $cve_usuario);
?>
