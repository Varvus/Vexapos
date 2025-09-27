<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

$cve_pedido = $_GET['cve_pedido'] ?? 0;

$sql = "
    SELECT d.cve_producto, p.nombre, d.cantidad, d.total, p.precio
    FROM pedido_det d
    JOIN producto p ON p.cve_usuario = d.cve_usuario AND p.cve_producto = d.cve_producto
    WHERE d.cve_usuario = ? AND d.cve_pedido = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cve_usuario, $cve_pedido);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>No hay detalles para este pedido.</p>";
    exit;
}
?>

<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= $row['cantidad'] ?></td>
                <td>$<?= number_format($row['precio'], 2) ?></td>
                <td>$<?= number_format($row['total'], 2) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
