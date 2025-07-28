<?php
include __DIR__ . "/connect.php";
include __DIR__ . "/verifica-usuario.php";

// Obtener productos activos con imagen
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

    #btn-ver-pedido {
        z-index: 1055;
    }
</style>

<h5>Venta</h5>
<hr>
<p>Seleccione un producto:</p>

<div class="row">
    <div class="col-12">
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php foreach ($productos as $p): ?>
                <div class="col">
                    <div class="card seleccionar-producto h-100" data-cve="<?= $p['cve_producto'] ?>"
                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>" data-precio="<?= $p['precio'] ?>">

                        <img data-src="/img/producto/<?= htmlspecialchars($p['imagen']) ?>" class="card-img-top lazy-img" 
                            alt="<?= htmlspecialchars($p['nombre']) ?>"> 

                        <div class="card-body text-center">
                            <h6 class="card-title"><?= htmlspecialchars($p['nombre']) ?></h6>
                            <p class="card-text fw-bold mb-0">$<?= number_format($p['precio'], 2) ?></p>
                            <div class="cantidad-container d-none">
                                <input type="number" class="form-control mt-2 cantidad-input" min="1" value="1">
                                <button class="btn btn-sm btn-primary w-100 mt-2 btn-agregar">Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Floating Button -->
<button type="button" id="btn-ver-pedido" class="btn btn-primary position-fixed bottom-0 end-0 m-3 d-none"
    data-bs-toggle="offcanvas" data-bs-target="#offcanvasPedido">
    <i class="bi bi-cart4"></i>
</button>

<!-- Offcanvas Pedido -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasPedido">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Resumen de pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div id="tabla-pedido" class="table-responsive d-none">
            <table class="table table-bordered">
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
                <div class="col-6">
                    <label>Efectivo recibido</label>
                    <input type="number" id="efectivo" class="form-control" min="0" step="0.01">
                </div>
                <div class="col-6">
                    <label>Cambio</label>
                    <div class="form-control bg-light" id="cambio">$0.00</div>
                </div>
            </div>

            <button class="btn btn-success w-100" id="btn-cobrar">Cobrar</button>
        </div>
    </div>
</div>

<script>
    let pedido = [];

    function renderPedido() {
        const tbody = document.getElementById("detalle");
        const tabla = document.getElementById("tabla-pedido");
        const btnVerPedido = document.getElementById("btn-ver-pedido");

        tbody.innerHTML = "";

        if (pedido.length === 0) {
            tabla.classList.add("d-none");
            btnVerPedido.classList.add("d-none");
            document.getElementById("total").textContent = "0.00";
            document.getElementById("cambio").textContent = "$0.00";
            return;
        } else {
            tabla.classList.remove("d-none");
            btnVerPedido.classList.remove("d-none");
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

    document.querySelectorAll(".seleccionar-producto").forEach(card => {
        card.addEventListener("click", () => {
            document.querySelectorAll(".seleccionar-producto").forEach(c => c.classList.remove("selected"));
            card.classList.add("selected");

            // Mostrar el input de cantidad dentro de la tarjeta seleccionada
            document.querySelectorAll(".cantidad-container").forEach(c => c.classList.add("d-none"));
            card.querySelector(".cantidad-container").classList.remove("d-none");
        });
    });

    document.querySelectorAll(".btn-agregar").forEach(button => {
        button.addEventListener("click", () => {
            const card = button.closest(".seleccionar-producto");
            const cantidad = parseInt(card.querySelector(".cantidad-input").value);
            const nombre = card.dataset.nombre;
            const precio = parseFloat(card.dataset.precio);
            const cve_producto = parseInt(card.dataset.cve);

            if (cantidad <= 0) return;

            pedido.push({ cve_producto, nombre, precio, cantidad });

            renderPedido();

            // Reset selección visual
            card.classList.remove("selected");
            card.querySelector(".cantidad-container").classList.add("d-none");
        });
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

        const btnCobrar = document.getElementById("btn-cobrar");
        btnCobrar.disabled = true;

        const formData = new FormData();
        formData.append("cve_usuario", <?= $cve_usuario ?>);
        formData.append("cve_cliente", 1); // Temporal
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
                bootstrap.Offcanvas.getOrCreateInstance(document.getElementById("offcanvasPedido")).hide();

                //Abrir e imprimir ticket sin mostrarlo al usuario
                const ticketWindow = window.open("php/ticket.php?cve_pedido=" + data.cve_pedido, "_blank", "width=400,height=600");
                ticketWindow.onload = () => {
                    ticketWindow.print();
                    ticketWindow.onafterprint = () => ticketWindow.close();
                };
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

    document.addEventListener("DOMContentLoaded", () => {
    const lazyImages = document.querySelectorAll('img.lazy-img');
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove("lazy-img");
                observer.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => observer.observe(img));
});

</script>