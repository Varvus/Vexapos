<?php
include __DIR__ . "/php/error-reporting.php"; // Incluye los reportes de error
include __DIR__ . "/connect.php"; // Conexión a la base de datos

$cve_usuario = $_POST["cve_usuario"];
$cve_producto = $_POST["cve_producto"];
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

// Si el cve_producto está vacío, significa que es una inserción
if (empty($cve_producto)) {
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
} else {
    // Si cve_producto no está vacío, es una actualización
    $sql = "UPDATE producto SET nombre = ?, descripcion = ?, activo = ?, inventario = ?, aplica_inventario = ?, fec_mod = NOW() 
            WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiiis", $nombre, $descripcion, $activo, $inventario, $aplica_inventario, $cve_usuario, $cve_producto);
    $stmt->execute();
}

header("Location: /admin-producto.php"); // Redirige a la lista de productos
exit;
?>
