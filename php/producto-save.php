<?php
include __DIR__ . "/error-reporting.php";
include __DIR__ . "/connect.php";

$cve_usuario = $_POST["cve_usuario"];
$cve_producto = $_POST["cve_producto"];
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$precio = $_POST["precio"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

// Procesar imagen si se subió
$imagen = $_POST["imagen"] ?? ""; // Valor por defecto si no se usa input file
if (isset($_FILES["imagen_archivo"]) && $_FILES["imagen_archivo"]["error"] === UPLOAD_ERR_OK) {
    $nombre_archivo = basename($_FILES["imagen_archivo"]["name"]);
    $ruta_destino = __DIR__ . "/../img/producto/" . $nombre_archivo;

    if (move_uploaded_file($_FILES["imagen_archivo"]["tmp_name"], $ruta_destino)) {
        $imagen = $nombre_archivo;
    }
}

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
    $sql = "INSERT INTO producto (
        cve_usuario, cve_producto, nombre, descripcion, precio, imagen,
        activo, inventario, aplica_inventario, fec_crea, fec_mod
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdsiii", $cve_usuario, $cve_producto, $nombre, $descripcion, $precio, $imagen, $activo, $inventario, $aplica_inventario);
    $stmt->execute();

} else {
    // Actualizar producto existente
    $sql = "UPDATE producto SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, activo = ?, inventario = ?, aplica_inventario = ?, fec_mod = NOW()
            WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsiisii", $nombre, $descripcion, $precio, $imagen, $activo, $inventario, $aplica_inventario, $cve_usuario, $cve_producto);
    $stmt->execute();
}

header("Location: /admin-producto.php");
exit;
?>