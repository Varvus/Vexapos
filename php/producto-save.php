<?php
include "php/connect.php";

$cve_usuario = $_POST["cve_usuario"];
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

if (!empty($_POST["cve_producto"])) {
    // Si hay cve_producto, es una actualización
    $cve_producto = $_POST["cve_producto"];

    $sql = "UPDATE producto 
            SET nombre = ?, descripcion = ?, activo = ?, inventario = ?, aplica_inventario = ?, fec_mod = NOW() 
            WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiiii", $nombre, $descripcion, $activo, $inventario, $aplica_inventario, $cve_usuario, $cve_producto);
} else {
    // Obtener el siguiente cve_producto
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
}

// Ejecutar la consulta
if ($stmt->execute()) {
    header("Location: /admin-producto.php?cve_usuario=" . $cve_usuario");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
?>
