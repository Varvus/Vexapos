<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Mexico_City');

header('Content-Type: application/json');

include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

// Validar que los datos existen
if (!isset($_POST['cve_usuario']) || !isset($_POST['productos'])) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit;
}

// Asignar variables
$cve_usuario = intval($_POST['cve_usuario']);
$cve_cliente = isset($_POST['cve_cliente']) ? intval($_POST['cve_cliente']) : 1;

// Decodificar productos
$productos = json_decode($_POST['productos'], true);
$fecha = date('Y-m-d H:i:s');

if (!is_array($productos) || empty($productos)) {
    echo json_encode(["success" => false, "mensaje" => "Lista de productos inválida"]);
    exit;
}

// Obtener nuevo cve_pedido
$sql = "SELECT IFNULL(MAX(cve_pedido), 0) + 1 AS nuevo FROM pedido WHERE cve_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cve_pedido = $row['nuevo'];

// Insertar encabezado de pedido
$sql = "INSERT INTO pedido (cve_usuario, cve_pedido, cve_estatus, fec_crea, fec_mod, total, cve_cliente)
        VALUES (?, ?, 1, ?, ?, 0.00, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissi", $cve_usuario, $cve_pedido, $fecha, $fecha, $cve_cliente);
$stmt->execute();

$total_pedido = 0.00;

foreach ($productos as $prod) {
    $cve_producto = intval($prod['cve_producto']);
    $cantidad = intval($prod['cantidad']);

    // Obtener precio actual del producto
    $sql = "SELECT precio FROM producto WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cve_usuario, $cve_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$row = $result->fetch_assoc())
        continue;

    $precio = floatval($row['precio']);
    $total = round($precio * $cantidad, 2);

    // Insertar detalle
    $sql = "INSERT INTO pedido_det (cve_usuario, cve_pedido, cve_producto, cantidad, fec_crea, fec_mod, total)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisssd", $cve_usuario, $cve_pedido, $cve_producto, $cantidad, $fecha, $fecha, $total);
    $stmt->execute();

    $total_pedido += $total;
}

// Actualizar total en encabezado
$sql = "UPDATE pedido SET total = ?, fec_mod = ? WHERE cve_usuario = ? AND cve_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dsii", $total_pedido, $fecha, $cve_usuario, $cve_pedido);
$stmt->execute();

echo json_encode(["success" => true, "cve_pedido" => $cve_pedido]);
