<?php
include __DIR__ . "/connection.php";
include __DIR__ . "/verifica-usuario.php";

$cve_usuario = $_POST['cve_usuario'];
$cve_cliente = $_POST['cve_cliente'];
$productos = $_POST['productos']; // array de arrays: [ ['cve_producto'=>X, 'cantidad'=>Y], ... ]
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
$stmt->bind_param("issssi", $cve_usuario, $cve_pedido, $fecha, $fecha, $cve_cliente);
$stmt->execute();

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
    if (!$row = $result->fetch_assoc())
        continue;

    $precio = (float) $row['precio'];
    $total = round($precio * $cantidad, 2);

    // Insertar detalle
    $sql = "INSERT INTO pedido_det (cve_usuario, cve_pedido, cve_producto, cantidad, fec_crea, fec_mod, total)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisssd", $cve_usuario, $cve_pedido, $cve_producto, $cantidad, $fecha, $fecha, $total);
    $stmt->execute();

    $total_pedido += $total;
}

// Actualizar total en pedido
$sql = "UPDATE pedido SET total = ?, fec_mod = ? WHERE cve_usuario = ? AND cve_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dsii", $total_pedido, $fecha, $cve_usuario, $cve_pedido);
$stmt->execute();

echo json_encode(["success" => true, "cve_pedido" => $cve_pedido]);
