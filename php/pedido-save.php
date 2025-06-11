<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";
header("Content-Type: application/json");

// Muestra errores en desarrollo:
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['productos'], $data['total']) || empty($data['productos'])) {
        throw new Exception("Datos incompletos.");
    }

    $productos = $data['productos'];
    $total = $data['total'];
    $fec = date("Y-m-d H:i:s");

    // Obtener nuevo cve_pedido para el usuario
    $stmt = $conn->prepare("SELECT IFNULL(MAX(cve_pedido), 0) + 1 FROM pedido WHERE cve_usuario = ?");
    $stmt->bind_param("i", $cve_usuario);
    $stmt->execute();
    $stmt->bind_result($cve_pedido);
    $stmt->fetch();
    $stmt->close();

    // Insertar pedido
    $cve_estatus = 1; // Ejemplo: 1 = Completado
    $cve_cliente = null;
    $stmt = $conn->prepare("INSERT INTO pedido (cve_usuario, cve_pedido, cve_estatus, fec_crea, fec_mod, total, cve_cliente)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissdi", $cve_usuario, $cve_pedido, $cve_estatus, $fec, $fec, $total, $cve_cliente);
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar el pedido: " . $stmt->error);
    }
    $stmt->close();

    // Insertar detalle
    $stmt = $conn->prepare("INSERT INTO pedido_det (cve_usuario, cve_pedido, cve_producto, cantidad, fec_crea, fec_mod, total)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($productos as $p) {
        $cve_producto = $p["id"];
        $cantidad = $p["cantidad"];
        $precio = $p["precio"];
        $total_producto = $precio * $cantidad;
        $stmt->bind_param("iiidssd", $cve_usuario, $cve_pedido, $cve_producto, $cantidad, $fec, $fec, $total_producto);
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar detalle: " . $stmt->error);
        }
    }
    $stmt->close();

    echo json_encode([
        "success" => true,
        "cve_pedido" => $cve_pedido
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ]);
}
