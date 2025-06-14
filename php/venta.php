<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

$sql = "SELECT cve_producto, nombre, precio, imagen FROM producto WHERE cve_usuario = ? AND activo = 1 ORDER BY nombre LIMIT 100";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cve_usuario);
$stmt->execute();
$result = $stmt->get_result();
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}
?>

<style>
    .seleccionar-producto {
        cursor: pointer;
        transition: 0.3s ease;
    }

    .seleccionar-producto.selected {
        border: 3px solid #007bff;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.7);
    }

    .card-img-top {
        height: 150px;
        object-fit: cover;
    }

    .cantidad-card {
        transition: opacity 0.2s ease;
    }

    #buscador {
        margin-bottom: 1rem;
    }
</style>

<h5>Venta</h5>
<hr>
<p>Seleccione un producto:</p>

<input type="text" id="buscador" class="form-control" placeholder="Buscar producto...">

<div class="row">
    <div class="col-12 col-md-7">
        <div class="row row-cols-2 row-cols-md-4 g-3" id="contenedor-productos">
            <?php foreach ($productos as $p): ?>
                <div class="col">
                    <div class="card seleccionar-producto h-100" data-cve="<?= $p['cve_producto'] ?>"
                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>" data-precio="<?= $p['precio'] ?>">
                        <img src="img/producto/<?= htmlspecialchars($p['imagen']) ?>" class="card-img-top"
                            alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <div class="card-body text-center">
                            <h6 class="card-title"><?= htmlspecialchars($p['nombre']) ?></h6>
                            <p class="card-text fw-bold">$<?= number_format($p['precio'], 2) ?></p>

                            <div class="cantidad-card d-none mt-2">
                                <input type="number" class="form-control cantidad-input mb-1" min="1" value="1">
                                <button class="btn btn-sm btn-primary btn-agregar-card w-100">Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-12 col-md-5">
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

    // Selección de producto
    document.querySelectorAll(".seleccionar-producto").forEach(card => {
        card.addEventListener("click", e => {
            e.stopPropagation(); // No propagar para evitar que el document click lo desmarque

            // Quitar selección de todos
            document.querySelectorAll(".seleccionar-producto").forEach(c => {
                c.classList.remove("selected");
                c.querySelector(".cantidad-card").classList.add("d-none");
            });

            // Seleccionar este
            card.classList.add("selected");
            card.querySelector(".cantidad-card").classList.remove("d-none");
        });
    });

    // Agregar desde cada card
    document.querySelectorAll(".btn-agregar-card").forEach(btn => {
        btn.addEventListener("click", e => {
            e.stopPropagation();

            const card = btn.closest(".seleccionar-producto");
            const cantidad = parseInt(card.querySelector(".cantidad-input").value || "1");

            if (cantidad <= 0) return;

            const producto = {
                cve_producto: parseInt(card.dataset.cve),
                nombre: card.dataset.nombre,
                precio: parseFloat(card.dataset.precio),
                cantidad
            };

            pedido.push(producto);
            renderPedido();

            // Limpiar selección
            card.classList.remove("selected");
            card.querySelector(".cantidad-card").classList.add("d-none");
        });
    });

    // Click fuera de productos → deselecciona
    document.addEventListener("click", () => {
        document.querySelectorAll(".seleccionar-producto").forEach(c => {
            c.classList.remove("selected");
            c.querySelector(".cantidad-card").classList.add("d-none");
        });
    });

    // Cambio en efectivo
    document.getElementById("efectivo").addEventListener("input", renderPedido);

    // Botón cobrar
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
        formData.append("cve_cliente", 1);
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

    // Buscador
    document.getElementById("buscador").addEventListener("input", function () {
        const valor = this.value.toLowerCase();
        document.querySelectorAll(".seleccionar-producto").forEach(card => {
            const nombre = card.dataset.nombre.toLowerCase();
            card.parentElement.style.display = nombre.includes(valor) ? "" : "none";
        });
    });
</script>
