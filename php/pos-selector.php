<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

// Obtener productos del usuario
$sql = "SELECT cve_producto, nombre, precio FROM producto WHERE cve_usuario = ? AND activo = 1 ORDER BY nombre";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}
?>

<div class="card p-3 my-3">
    <h4>Agregar Productos</h4>
    <div id="pos-items"></div>

    <div class="d-flex gap-2 mt-3">
        <select class="form-control" id="producto-select">
            <option value="">-- Selecciona un producto --</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= $p["cve_producto"] ?>" data-precio="<?= $p["precio"] ?>">
                    <?= htmlspecialchars($p["nombre"]) ?> ($<?= number_format($p["precio"], 2) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" id="cantidad-input" class="form-control" placeholder="Cantidad" min="1" value="1">
        <button type="button" class="btn btn-primary" onclick="agregarProducto()">Agregar</button>
    </div>

    <div class="mt-3">
        <strong>Total: $<span id="total">0.00</span></strong>
    </div>

    <div class="mt-3">
        <label for="efectivo">Efectivo recibido:</label>
        <input type="number" class="form-control" id="efectivo" oninput="calcularCambio()">
        <div class="mt-2">
            <strong>Cambio: $<span id="cambio">0.00</span></strong>
        </div>
    </div>
</div>

<script>
    let items = [];
    function agregarProducto() {
        const select = document.getElementById("producto-select");
        const cantidad = parseInt(document.getElementById("cantidad-input").value);
        const productoId = select.value;
        const productoNombre = select.options[select.selectedIndex].text;
        const precio = parseFloat(select.options[select.selectedIndex].dataset.precio);

        if (!productoId || cantidad < 1) return;

        items.push({ id: productoId, nombre: productoNombre, cantidad, precio });

        renderItems();
        calcularTotal();
        document.getElementById("cantidad-input").value = 1;
        select.value = "";
    }

    function renderItems() {
        const container = document.getElementById("pos-items");
        container.innerHTML = "";
        items.forEach((item, i) => {
            const div = document.createElement("div");
            div.className = "d-flex justify-content-between border-bottom py-1";
            div.innerHTML = `
            <div>${item.nombre} x ${item.cantidad}</div>
            <div>$${(item.precio * item.cantidad).toFixed(2)}</div>
        `;
            container.appendChild(div);
        });
    }

    function calcularTotal() {
        const total = items.reduce((sum, item) => sum + item.precio * item.cantidad, 0);
        document.getElementById("total").textContent = total.toFixed(2);
        calcularCambio();
    }

    function calcularCambio() {
        const total = parseFloat(document.getElementById("total").textContent);
        const efectivo = parseFloat(document.getElementById("efectivo").value) || 0;
        const cambio = efectivo - total;
        document.getElementById("cambio").textContent = cambio >= 0 ? cambio.toFixed(2) : "0.00";
    }
</script>