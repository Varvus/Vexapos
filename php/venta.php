<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

// Obtener productos activos
$sql = "SELECT cve_producto, nombre, precio, imagen, extension FROM producto WHERE cve_usuario = ? AND activo = 1 ORDER BY nombre";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}
?>

<h5>Venta</h5>
<hr>
<p>Seleccione un producto:</p>

<div class="row row-cols-2 row-cols-md-4 g-3 mb-3">
    <?php foreach ($productos as $p): ?>
        <div class="col">
            <div class="card h-100 text-center producto-card" data-id="<?= $p['cve_producto'] ?>"
                data-nombre="<?= htmlspecialchars($p['nombre']) ?>" data-precio="<?= $p['precio'] ?>">
                <img src="imagenes/<?= $p['imagen'] ?>.<?= $p['extension'] ?>" class="card-img-top img-fluid"
                    alt="<?= htmlspecialchars($p['nombre']) ?>" style="max-height: 130px; object-fit: contain;">
                <div class="card-body p-2">
                    <h6 class="card-title mb-1"><?= htmlspecialchars($p['nombre']) ?></h6>
                    <p class="card-text text-success mb-0">$<?= number_format($p['precio'], 2) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
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

    document.querySelectorAll(".producto-card").forEach(card => {
        card.addEventListener("click", () => {
            const nombre = card.dataset.nombre;
            const id = parseInt(card.dataset.id);
            const precio = parseFloat(card.dataset.precio);

            let cantidad = prompt(`¿Cuántos quieres de "${nombre}"?`, "1");
            cantidad = parseInt(cantidad);
            if (isNaN(cantidad) || cantidad <= 0) return;

            pedido.push({ cve_producto: id, nombre, precio, cantidad });
            renderPedido();
        });
    });

    document.getElementById("efectivo").addEventListener("input", renderPedido);

    document.getElementById("btn-cobrar").addEventListener("click", async () => {
        if (pedido.length === 0) return;

        const total = pedido.reduce((sum, p) => sum + p.precio * p.cantidad, 0);
        const efectivo = parseFloat(document.getElementById("efectivo").value || "0");

        if (efectivo < total) {
            alert("El efectivo no es suficiente.");
            return;
        }

        const btnCobrar = document.getElementById("btn-cobrar");
        btnCobrar.disabled = true;

        const formData = new FormData();
        formData.append("cve_usuario", <?= $cve_usuario ?>);
        formData.append("cve_cliente", 1); // Cliente default
        formData.append("productos", JSON.stringify(pedido));
        formData.append("total", total.toFixed(2));

        try {
            const res = await fetch("php/pedido-save.php", {
                method: "POST",
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                alert("Venta registrada. Pedido #" + data.cve_pedido);
                pedido = [];
                document.getElementById("efectivo").value = "";
                renderPedido();
            } else {
                alert("Error al guardar el pedido. " + (data.mensaje || "Error desconocido"));
            }
        } catch (error) {
            alert("Error en la comunicación con el servidor.");
            console.error(error);
        } finally {
            btnCobrar.disabled = false;
        }
    });
</script>