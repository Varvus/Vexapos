<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

$cve_pedido = $_GET['cve_pedido'] ?? 0;

// Obtener pedido
$stmt = $conn->prepare("
    SELECT p.cve_pedido, p.fec_crea, p.total, u.nombre AS tienda
    FROM pedido p
    JOIN usuario u ON u.cve_usuario = p.cve_usuario
    WHERE p.cve_usuario = ? AND p.cve_pedido = ?
");
$stmt->bind_param("ii", $cve_usuario, $cve_pedido);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    echo "Pedido no encontrado.";
    exit;
}

// Obtener detalles
$stmt = $conn->prepare("
    SELECT pr.nombre, d.cantidad, d.total / d.cantidad AS precio_unitario, d.total
    FROM pedido_det d
    JOIN producto pr ON pr.cve_producto = d.cve_producto
    WHERE d.cve_usuario = ? AND d.cve_pedido = ?
");
$stmt->bind_param("ii", $cve_usuario, $cve_pedido);
$stmt->execute();
$detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= $pedido['cve_pedido'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: monospace;
            background: #fff;
            padding: 20px;
            max-width: 350px;
            margin: auto;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .border-top {
            border-top: 1px dashed #000;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h5 class="text-center"><?= htmlspecialchars($pedido['tienda']) ?></h5>
    <p class="text-center">Ticket #<?= $pedido['cve_pedido'] ?></p>
    <p class="text-center"><?= date('d/m/Y H:i', strtotime($pedido['fec_crea'])) ?></p>
    <hr class="border-top">

    <?php foreach ($detalles as $item): ?>
        <div>
            <div><?= htmlspecialchars($item['nombre']) ?></div>
            <div class="d-flex justify-content-between">
                <small><?= $item['cantidad'] ?> x $<?= number_format($item['precio_unitario'], 2) ?></small>
                <small>$<?= number_format($item['total'], 2) ?></small>
            </div>
        </div>
    <?php endforeach; ?>

    <hr class="border-top">
    <p class="text-end fw-bold">Total: $<?= number_format($pedido['total'], 2) ?></p>

    <p class="text-center small mt-3">Gracias por su compra</p>
    <p class="text-center small text-muted">Generado por VEXAPOS</p>
</body>

</html>