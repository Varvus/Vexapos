<?php
include __DIR__ . "/php/error-reporting.php"; // Mostrar errores
include __DIR__ . "/connect.php"; // Conexión a la base de datos

// Debug: Mostrar los datos que están siendo enviados
echo "<pre>";
print_r($_POST);
echo "</pre>";
exit;

$cve_usuario = 1; // Usuario fijo, como pediste
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

// Verificar si los datos existen
if (empty($nombre) || empty($descripcion)) {
    echo "Los campos nombre y descripción son obligatorios.";
    exit;
}

// Obtener siguiente cve_producto para ese usuario
$sql = "SELECT COALESCE(MAX(cve_producto), 0) + 1 AS next_cve_producto FROM producto WHERE cve_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cve_producto = $row['next_cve_producto']; // Esto debería dar el siguiente valor de cve_producto

// Debug: Verifica el cve_producto generado
echo "Siguiente cve_producto: " . $cve_producto;

// Insertar nuevo producto
$sql = "INSERT INTO producto (cve_usuario, cve_producto, nombre, descripcion, activo, inventario, aplica_inventario, fec_crea, fec_mod)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissiii", $cve_usuario, $cve_producto, $nombre, $descripcion, $activo, $inventario, $aplica_inventario);

if ($stmt->execute()) {
    // Si la ejecución es exitosa, redirigir
    header("Location: /admin-producto.php");
    exit;
} else {
    // Si hay error en la ejecución, muestra el error
    echo "Error al insertar el producto: " . $stmt->error;
    exit;
}
?>
