<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

header("Content-Type: application/json");

// Validar datos recibidos
if (!isset($_POST["productos"]) || !is_array($_POST["productos"]) || count($_POST["productos"]) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "No se recibieron productos válidos."
    ]);
    exit;
}

// Preparar datos
$productos = $_POST["productos"];
$total = 0;
foreach ($productos as $item) {
    if (!isset($item["cve_producto"], $item["cantidad"])) {
        continue;
    }

    // Consultar precio actual del producto
    $stmt = $conn->prepare("SELECT precio FROM producto WHERE cve_usuario = ? AND cve_producto = ?");
    $stmt->bind_param("ii", $cve_usuario, $item["cve_producto"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();

    if ($producto) {
        $total += $producto["precio"] * $item["cantidad"];
    }
}

// Obtener nuevo cve_pedido
$sql = "SELECT COALESCE(MAX(cve_pedido), 0) + 1 AS next_pedido FROM pedido WHERE cve_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cve_pedido = $row["next_pedido"];

// Insertar pedido
$sql = "INSERT INTO pedido (cve_usuario, cve_pedido, cve_estatus, fec_crea, fec_mod, total)
        VALUES (?, ?, 1, NOW(), NOW(), ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iid", $cve_usuario, $cve_pedido, $total);
$stmt->execute();

// Insertar productos en pedido_det
$sql_det = "INSERT INTO pedido_det (cve_usuario, cve_pedido, cve_producto, cantidad, fec_crea, fec_mod, total)
            VALUES (?, ?, ?, ?, NOW(), NOW(), ?)";
$stmt_det = $conn->prepare($sql_det);

foreach ($productos as $item) {
    $cve_producto = $item["cve_producto"];
    $cantidad = $item["cantidad"];

    // Obtener precio actual
    $stmt = $conn->prepare("SELECT precio FROM producto WHERE cve_usuario = ? AND cve_producto = ?");
    $stmt->bind_param("ii", $cve_usuario, $cve_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();

    if ($producto) {
        $precio_unitario = $producto["precio"];
        $total_item = $precio_unitario * $cantidad;

        $stmt_det->bind_param("iiiid", $cve_usuario, $cve_pedido, $cve_producto, $cantidad, $total_item);
        $stmt_det->execute();
    }
}

// Respuesta JSON
echo json_encode([
    "success" => true,
    "cve_pedido" => $cve_pedido,
    "total" => $total
]);
exit;
?>