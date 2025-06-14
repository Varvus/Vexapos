<!DOCTYPE html>
<html>

<head>
    <?php include "initials.php"; ?>
    <title>VEXAPOS: Admin-Dashboard</title>
    <style>
        .card-icon {
            font-size: 2rem;
        }
    </style>
</head>

<body>
    <?php include "admin-menu.php"; ?>

    <?php
    include __DIR__ . "/php/connect.php";
    include __DIR__ . "/php/verifica-usuario.php";

    // Total de productos activos
    $stmt = $conn->prepare("SELECT COUNT(*) FROM producto WHERE cve_usuario = ? AND activo = 1");
    $stmt->bind_param("i", $cve_usuario);
    $stmt->execute();
    $stmt->bind_result($total_productos);
    $stmt->fetch();
    $stmt->close();

    // Pedidos del mes
    $stmt = $conn->prepare("SELECT IFNULL(SUM(total), 0) FROM pedido WHERE cve_usuario = ? AND MONTH(fec_crea) = MONTH(CURDATE()) AND YEAR(fec_crea) = YEAR(CURDATE())");
    $stmt->bind_param("i", $cve_usuario);
    $stmt->execute();
    $stmt->bind_result($total_mes);
    $stmt->fetch();
    $stmt->close();

    // Producto más vendido
    $stmt = $conn->prepare("SELECT p.nombre, SUM(pd.cantidad) AS total_vendido FROM pedido_det pd JOIN producto p ON p.cve_usuario = pd.cve_usuario AND p.cve_producto = pd.cve_producto JOIN pedido pe ON pe.cve_usuario = pd.cve_usuario AND pe.cve_pedido = pd.cve_pedido WHERE pd.cve_usuario = ? AND MONTH(pe.fec_crea) = MONTH(CURDATE()) GROUP BY p.nombre ORDER BY total_vendido DESC LIMIT 1");
    $stmt->bind_param("i", $cve_usuario);
    $stmt->execute();
    $stmt->bind_result($producto_top, $total_top);
    $stmt->fetch();
    $stmt->close();

    if (!$producto_top) {
        $producto_top = "Sin ventas";
        $total_top = 0;
    }

    // Ventas por hora (hoy)
    $stmt = $conn->prepare("SELECT HOUR(fec_crea) AS hora, SUM(total) AS total FROM pedido WHERE cve_usuario = ? AND DATE(fec_crea) = CURDATE() GROUP BY hora");
    $stmt->bind_param("i", $cve_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $horas = [];
    $ventas = [];
    while ($row = $result->fetch_assoc()) {
        $horas[] = $row['hora'] . ":00";
        $ventas[] = $row['total'];
    }
    $stmt->close();
    ?>

    <div class="container py-4">
        <h4>Dashboard</h4>
        <hr>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-box card-icon text-primary"></i>
                        <h5 class="mt-2">Productos</h5>
                        <p class="fs-4 fw-bold mb-0"><?= $total_productos ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar card-icon text-success"></i>
                        <h5 class="mt-2">Ventas del mes</h5>
                        <p class="fs-4 fw-bold mb-0">$<?= number_format($total_mes, 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-star-fill card-icon text-warning"></i>
                        <h5 class="mt-2">Top del mes</h5>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($producto_top) ?> (<?= $total_top ?>)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Ventas por hora (hoy)</h5>
                <canvas id="ventasHoraChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('ventasHoraChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($horas) ?>,
                datasets: [{
                    label: 'Ventas ($)',
                    data: <?= json_encode($ventas) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <?php include "footer.php"; ?>
</body>

</html>
