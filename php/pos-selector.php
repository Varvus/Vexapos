<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

// Obtener productos
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

<div class="card p-3 mb-3">
    <h5>Venta rápida</h5>
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label>Producto</label>
            <select class="form-select" id="producto">
                <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['cve_producto'] ?>" data-precio="<?= $p['precio'] ?>">
                        <?= htmlspecialchars($p['nombre']) ?> ($<?= number_format($p['precio'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Cantidad</label>
            <input type="number" id="cantidad" class="form-control" value="1" min="1">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" id="btn-agregar">Agregar</button>
        </div>
    </div>

    <hr>

    <div id="tabla-pedido" class="table-responsive d-none">
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="detalle"></tbody>
        </table>

        <div class="mb-2">
            <strong>Total: $<span id="total">0.00</span></strong>
        </div>

        <div class="row g-2 align-items-end mb-2">
            <div class="col-md-4">
                <label>Efectivo recibido</label>
                <input type="number" id="efectivo" class="form-control" min="0" step="0.01">
            </div>
            <div class="col-md-4">
                <label>Cambio</label>
                <div class="form-control bg-light" id="cambio">$0.00</div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-success w-100" id="btn-cobrar">Cobrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let pedido = [];

    function renderPedido() {
        const tbody = document.getElementById("detalle");
        const tabla = document.getElementById("tabla-pedido");
        tbody.innerHTML = "";

        if (pedido.length === 0) {
            tabla.classList.add("d-none");
            document.getElementById("total").textContent = "0.00";
            document.getElementById("cambio").textContent = "$0.00";
            return;
        }

        let total = 0;
        pedido.forEach((item, index) => {
            const subtotal = item.cantidad * item.precio;
            total += subtotal;

            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td>${item.nombre}</td>
            <td>${item.cantidad}</td>
            <td>$${item.precio.toFixed(2)}</td>
            <td>$${subtotal.toFixed(2)}</td>
            <td><button class="btn btn-sm btn-danger" onclick="eliminar(${index})">X</button></td>
        `;
            tbody.appendChild(tr);
        });

        tabla.classList.remove("d-none");
        document.getElementById("total").textContent = total.toFixed(2);

        const efectivo = parseFloat(document.getElementById("efectivo").value);
        if (!isNaN(efectivo)) {
            const cambio = efectivo - total;
            document.getElementById("cambio").textContent = cambio >= 0 ? "$" + cambio.toFixed(2) : "$0.00";
        }
    }

    function eliminar(index) {
        pedido.splice(index, 1);
        renderPedido();
    }

    document.getElementById("btn-agregar").addEventListener("click", () => {
        const select = document.getElementById("producto");
        const cantidad = parseInt(document.getElementById("cantidad").value);
        const id = parseInt(select.value);
        const nombre = select.options[select.selectedIndex].text;
        const precio = parseFloat(select.options[select.selectedIndex].dataset.precio);

        if (cantidad > 0) {
            pedido.push({ id, nombre, precio, cantidad });
            renderPedido();
        }
    });

    document.getElementById("efectivo").addEventListener("input", () => {
        renderPedido();
    });

    document.getElementById("btn-cobrar").addEventListener("click", async () => {
        if (pedido.length === 0) return;

        const total = pedido.reduce((sum, p) => sum + p.precio * p.cantidad, 0);
        const efectivo = parseFloat(document.getElementById("efectivo").value || "0");

        if (efectivo < total) {
            alert("El efectivo no es suficiente.");
            return;
        }

        const res = await fetch("php/pedido-save.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                cve_usuario: <?= $cve_usuario ?>,
                productos: pedido,
                total: total
            })
        });

        const data = await res.json();
        if (data.success) {
            alert("Venta registrada. Pedido #" + data.cve_pedido);
            pedido = [];
            document.getElementById("efectivo").value = "";
            renderPedido();
        } else {
            alert("Error al guardar el pedido." + (data.mensaje || "Error desconocido")););
        }
    });
</script>