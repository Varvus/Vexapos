<?php

include __DIR__ . "/php/error-reporting.php"; // Incluye los reportes de error
include __DIR__ . "/connect.php"; // Conexión a la base de datos

echo "<pre>";
print_r($_POST); // Muestra el contenido de POST para depuración
echo "</pre>";

$cve_usuario = 1; // Como pediste, el usuario está fijo en 1
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

// Obtener siguiente cve_producto para ese usuario
$sql = "SELECT COALESCE(MAX(cve_producto), 0) + 1 AS next_cve_producto FROM producto WHERE cve_usuario = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Error al preparar la consulta: " . $conn->error;
    exit;
}

$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cve_producto = $row['next_cve_producto'];

// Depuración: Verifica el cve_producto
echo "cve_producto calculado: " . $cve_producto . "<br>";

// Insertar nuevo producto
$sql = "INSERT INTO producto (cve_usuario, cve_producto, nombre, descripcion, activo, inventario, aplica_inventario, fec_crea, fec_mod)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo "Error al preparar la consulta de inserción: " . $conn->error;
    exit;
}

$stmt->bind_param("iissiii", $cve_usuario, $cve_producto, $nombre, $descripcion, $activo, $inventario, $aplica_inventario);

if ($stmt->execute()) {
    echo "Producto insertado correctamente";
} else {
    echo "Error al ejecutar la consulta: " . $stmt->error;
}

header("Location: /admin-producto.php"); // redirige al listado
exit;

?>
