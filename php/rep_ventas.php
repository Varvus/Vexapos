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
        <table class="table table-bordered table-hover table-sm" id="tabla-ventas">
            <thead class="table-light">
                <tr>
                    <th># Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="fila-pedido" data-id="<?= $row['cve_pedido'] ?>">
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

<!-- Modal para mostrar detalles -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle del pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="detalle-contenido">
          <div class="text-center">Cargando...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.fila-pedido').forEach(row => {
    row.addEventListener('click', async () => {
        const id = row.dataset.id;
        const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        document.getElementById('detalle-contenido').innerHTML = '<div class="text-center">Cargando...</div>';
        modal.show();

        try {
            const res = await fetch(`php/detalle-pedido.php?cve_pedido=${id}`);
            const html = await res.text();
            document.getElementById('detalle-contenido').innerHTML = html;
        } catch (err) {
            document.getElementById('detalle-contenido').innerHTML = '<div class="text-danger text-center">Error al cargar detalle.</div>';
        }
    });
});
</script>
