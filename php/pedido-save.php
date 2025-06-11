<?php
header('Content-Type: application/json');

set_exception_handler(function ($e) {
    echo json_encode(["success" => false, "mensaje" => "Excepción: " . $e->getMessage()]);
    exit;
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo json_encode(["success" => false, "mensaje" => "Error: $errstr en $errfile:$errline"]);
    exit;
});

$cve_usuario = $input['cve_usuario']; // ✅ Ya disponible antes del include

include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

// Leer JSON desde el cuerpo de la petición
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['cve_usuario']) || !isset($input['productos'])) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos o inválidos"]);
    exit;
}

$cve_usuario = $input['cve_usuario'];
$cve_cliente = $input['cve_cliente'] ?? 1;
$productos = $input['productos'];
$fecha = date('Y-m-d H:i:s');

// Obtener nuevo cve_pedido
$sql = "SELECT IFNULL(MAX(cve_pedido), 0) + 1 AS nuevo FROM pedido WHERE cve_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cve_pedido = $row['nuevo'];

// Insertar en tabla pedido (total temporal 0.00)
$sql = "INSERT INTO pedido (cve_usuario, cve_pedido, cve_estatus, fec_crea, fec_mod, total, cve_cliente)
        VALUES (?, ?, 1, ?, ?, 0.00, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissi", $cve_usuario, $cve_pedido, $fecha, $fecha, $cve_cliente);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "mensaje" => "Error al insertar pedido: " . $stmt->error]);
    exit;
}

$total_pedido = 0.00;

foreach ($productos as $prod) {
    $cve_producto = $prod['cve_producto'];
    $cantidad = $prod['cantidad'];

    // Obtener precio actual del producto
    $sql = "SELECT precio FROM producto WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cve_usuario, $cve_producto);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$row = $result->fetch_assoc()) {
        continue; // Producto no encontrado
    }

    $precio = (float) $row['precio'];
    $total = round($precio * $cantidad, 2);

    // Insertar detalle
    $sql = "INSERT INTO pedido_det (cve_usuario, cve_pedido, cve_producto, cantidad, fec_crea, fec_mod, total)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisssd", $cve_usuario, $cve_pedido, $cve_producto, $cantidad, $fecha, $fecha, $total);
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "mensaje" => "Error al insertar detalle: " . $stmt->error]);
        exit;
    }

    $total_pedido += $total;
}

// Actualizar total en pedido
$sql = "UPDATE pedido SET total = ?, fec_mod = ? WHERE cve_usuario = ? AND cve_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dsii", $total_pedido, $fecha, $cve_usuario, $cve_pedido);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "mensaje" => "Error al actualizar total: " . $stmt->error]);
    exit;
}

echo json_encode(["success" => true, "cve_pedido" => $cve_pedido]);
