<?php
include __DIR__ . "/php/connect.php";
include __DIR__ . "/php/verifica-usuario.php";

// Ventas del día
$sql_ventas = "
    SELECT IFNULL(SUM(total), 0) AS total_dia 
    FROM pedido 
    WHERE cve_usuario = ? 
    AND DATE(fec_crea) = CURDATE()
";
$stmt = $conn->prepare($sql_ventas);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$total_ventas = $stmt->get_result()->fetch_assoc()['total_dia'];

// Productos activos
$sql_productos = "
    SELECT COUNT(*) AS total_productos 
    FROM producto 
    WHERE cve_usuario = ? AND activo = 1
";
$stmt = $conn->prepare($sql_productos);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$total_productos = $stmt->get_result()->fetch_assoc()['total_productos'];

// Clientes activos
$sql_clientes = "
    SELECT COUNT(*) AS total_clientes 
    FROM cliente 
    WHERE cve_usuario = ? AND activo = 1
";
$stmt = $conn->prepare($sql_clientes);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$total_clientes = $stmt->get_result()->fetch_assoc()['total_clientes'];

// Últimos 5 pedidos
$sql_pedidos = "
    SELECT p.cve_pedido, p.total, p.fec_crea, c.nombre AS cliente 
    FROM pedido p
    LEFT JOIN cliente c ON p.cve_usuario = c.cve_usuario AND p.cve_cliente = c.cve_cliente
    WHERE p.cve_usuario = ?
    ORDER BY p.fec_crea DESC
    LIMIT 5
";
$stmt = $conn->prepare($sql_pedidos);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$ultimos_pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <?php include "initials.php"; ?>
    <title>VEXAPOS: Admin-Dashboard</title>
</head>
<body>
    <?php include "admin-menu.php"; ?>

    <div class="container my-4">
        <h4 class="mb-4">Dashboard</h4>
        <div class="row g-3">

            <div class="col-md-4">
                <div class="card text-bg-primary shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-cash-coin"></i> Ventas del día</h5>
                        <p class="display-6 fw-bold">$<?= number_format($total_ventas, 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-bg-success shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-box-seam"></i> Productos activos</h5>
                        <p class="display-6 fw-bold"><?= $total_productos ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-bg-warning shadow">
                    <div class="card-body">
                        <h5><i class="bi bi-people"></i> Clientes activos</h5>
                        <p class="display-6 fw-bold"><?= $total_clientes ?></p>
                    </div>
                </div>
            </div>

        </div>

        <div class="my-4 d-flex justify-content-between align-items-center">
            <h5>Últimos pedidos</h5>
            <a href="admin-venta.php" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Nueva venta
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th># Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimos_pedidos as $p): ?>
                        <tr>
                            <td><?= $p['cve_pedido'] ?></td>
                            <td><?= htmlspecialchars($p['cliente'] ?: 'Sin cliente') ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($p['fec_crea'])) ?></td>
                            <td>$<?= number_format($p['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ultimos_pedidos)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay pedidos recientes</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
</html>
