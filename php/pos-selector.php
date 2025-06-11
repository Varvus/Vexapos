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
            pedido.push({ cve_producto: id, nombre, precio, cantidad });
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