<?php
include __DIR__ . "/php/connect.php";
include __DIR__ . "/php/verifica-usuario.php";

// Total productos activos
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM producto WHERE cve_usuario = ? AND activo = 1");
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_productos = $result['total'];

// Total ventas del mes
$stmt = $conn->prepare("SELECT SUM(total) AS total FROM pedido WHERE cve_usuario = ? AND MONTH(fec_crea) = MONTH(CURRENT_DATE()) AND YEAR(fec_crea) = YEAR(CURRENT_DATE())");
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_ventas_mes = $result['total'] ?? 0;

// Producto más vendido del mes
$stmt = $conn->prepare("SELECT p.nombre, SUM(d.cantidad) AS total FROM pedido_det d JOIN producto p ON p.cve_producto = d.cve_producto WHERE d.cve_usuario = ? AND MONTH(d.fec_crea) = MONTH(CURRENT_DATE()) AND YEAR(d.fec_crea) = YEAR(CURRENT_DATE()) GROUP BY d.cve_producto ORDER BY total DESC LIMIT 1");
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$producto_mas_vendido = $result ? $result['nombre'] . " ({$result['total']} vendidos)" : "Sin ventas registradas";

// Ventas por hora del día actual
$stmt = $conn->prepare("SELECT HOUR(fec_crea) AS hora, SUM(total) AS total FROM pedido WHERE cve_usuario = ? AND DATE(fec_crea) = CURDATE() GROUP BY HOUR(fec_crea)");
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$horas = array_fill(0, 24, 0);
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $horas[(int)$row['hora']] = (float)$row['total'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <?php include "initials.php"; ?>
    <title>VEXAPOS: Admin-Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include "admin-menu.php"; ?>

    <div class="container py-4">
        <h5 class="mb-3">Dashboard</h5>

        <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
            <div class="col">
                <div class="card text-bg-primary shadow h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-box-seam display-6 me-3"></i>
                        <div>
                            <div class="fw-bold fs-5"><?= $total_productos ?></div>
                            <div>Productos Activos</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card text-bg-success shadow h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-cash-coin display-6 me-3"></i>
                        <div>
                            <div class="fw-bold fs-5">$<?= number_format($total_ventas_mes, 2) ?></div>
                            <div>Ventas del Mes</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card text-bg-warning shadow h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-trophy display-6 me-3"></i>
                        <div>
                            <div class="fw-bold fs-6"><?= $producto_mas_vendido ?></div>
                            <div>Más Vendido</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header bg-white fw-bold">Ventas por Hora (Hoy)</div>
            <div class="card-body">
                <canvas id="grafica-horas" height="120"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('grafica-horas');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [...Array(24).keys()].map(h => `${h}:00`),
                datasets: [{
                    label: 'Ventas $',
                    data: <?= json_encode(array_values($horas)) ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>

    <?php include "footer.php"; ?>
</body>
</html>
