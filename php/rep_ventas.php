<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

// Consultar las últimas 50 ventas del usuario
$sql = "
    SELECT p.cve_pedido, p.fec_crea, p.total, c.nombre AS cliente
    FROM pedido p
    LEFT JOIN cliente c ON c.cve_usuario = p.cve_usuario AND c.cve_cliente = p.cve_cliente
    WHERE p.cve_usuario = ?
    ORDER BY p.fec_crea DESC
    LIMIT 50
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card p-3">
    <h5>Últimas ventas</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th># Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['cve_pedido'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['fec_crea'])) ?></td>
                        <td><?= htmlspecialchars($row['cliente'] ?: 'Público general') ?></td>
                        <td>$<?= number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>