<?php

include "php/error-reporting.php";

include __DIR__ . "/connect.php";

echo "<pre>";
print_r($_POST);
echo "</pre>";
exit;

$cve_usuario = $_POST["cve_usuario"];
$cve_producto = $_POST["cve_producto"];
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

// Verificar si ya existe el producto
$sql_check = "SELECT COUNT(*) as total FROM producto WHERE cve_usuario = ? AND cve_producto = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $cve_usuario, $cve_producto);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();

if ($row_check['total'] > 0) {
    // Actualizar
    $sql = "UPDATE producto 
            SET nombre = ?, descripcion = ?, activo = ?, inventario = ?, aplica_inventario = ?, fec_mod = NOW()
            WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiiii", $nombre, $descripcion, $activo, $inventario, $aplica_inventario, $cve_usuario, $cve_producto);
} else {
    // Insertar
    $sql = "INSERT INTO producto (cve_usuario, cve_producto, nombre, descripcion, activo, inventario, aplica_inventario, fec_crea, fec_mod)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissiii", $cve_usuario, $cve_producto, $nombre, $descripcion, $activo, $inventario, $aplica_inventario);
}

if ($stmt->execute()) {
    header("Location: /admin-producto.php");
} else {
    echo "Error al guardar el producto: " . $stmt->error;
}
?>