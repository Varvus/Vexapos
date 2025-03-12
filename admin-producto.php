<!DOCTYPE html>
<html>

<head>
    <?php include "initials.php"; ?>
    <title>VEXAPOS: Admin: Producto</title>
</head>

<body>

    <?php include "php/connect.php"; ?>
    <?php include "admin-menu.php"; ?>

    <div class="container">

        <?php include "verifica-usuario.php"; ?>

        <h2><?php echo isset($_GET['edit']) ? 'Editar Producto' : 'Agregar Producto'; ?></h2>
        <hr>

        <?php
        $cve_usuario = $_GET["cve_usuario"] ?? null;
        $cve_producto = $_GET["edit"] ?? null;
        $nombre = "";
        $descripcion = "";

        // Si se está editando, obtener datos del producto
        if ($cve_producto) {
            $sql = "SELECT nombre, descripcion FROM producto WHERE cve_producto = ? AND cve_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $cve_producto, $cve_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $nombre = $row['nombre'];
                $descripcion = $row['descripcion'];
            }
            $stmt->close();
        }

        // Procesar el formulario de guardado
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre = $_POST["wk_nombre"];
            $descripcion = $_POST["wk_descripcion"];
            $activo = 1;
            $inventario = 0;
            $aplica_inventario = 0;

            if (isset($_POST["cve_producto"]) && $_POST["cve_producto"] != "") {
                // Actualizar producto existente
                $cve_producto = $_POST["cve_producto"];
                $sql = "UPDATE producto SET nombre = ?, descripcion = ?, fec_mod = NOW() WHERE cve_producto = ? AND cve_usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssii", $nombre, $descripcion, $cve_producto, $cve_usuario);
                $mensaje = $stmt->execute() ? "Producto actualizado correctamente." : "Error al actualizar el producto.";
            } else {
                // Insertar nuevo producto
                $sql = "INSERT INTO producto (cve_usuario, nombre, descripcion, fec_crea, fec_mod, activo, inventario, aplica_inventario) VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issiii", $cve_usuario, $nombre, $descripcion, $activo, $inventario, $aplica_inventario);
                $mensaje = $stmt->execute() ? "Producto agregado correctamente." : "Error al agregar el producto.";
            }

            $stmt->close();
            echo "<div class='alert alert-info'>$mensaje</div>";
        }
        ?>

        <!-- Formulario para agregar/modificar productos -->
        <form method="post">
            <input type="hidden" name="cve_producto" value="<?php echo htmlspecialchars($cve_producto); ?>">
            <div>
                <label for="wk_nombre" class="form-label">*Nombre de Producto</label>
                <input type="text" id="wk_nombre" class="form-control" name="wk_nombre" required value="<?php echo htmlspecialchars($nombre); ?>">
            </div>
            <div>
                <label for="wk_descripcion" class="form-label w-100">Descripción del Producto:</label>
                <textarea id="wk_descripcion" class="form-control" name="wk_descripcion" rows="3"><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>
            <br>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="admin-producto.php?cve_usuario=<?php echo $cve_usuario; ?>" class="btn btn-secondary">Cancelar</a>
        </form>

        <h2>Productos</h2>
        <hr>

        <?php
        // Mostrar productos
        $sql = "SELECT 
                    p.cve_producto, 
                    p.nombre, 
                    p.descripcion, 
                    p.fec_crea, 
                    p.fec_mod, 
                    p.activo, 
                    p.inventario, 
                    p.aplica_inventario
                FROM 
                    producto p
                WHERE 
                    p.cve_usuario = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cve_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<table class='table'>
                    <thead>
                        <tr>
                            <th>Cve Producto</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Fecha de Creación</th>
                            <th>Fecha de Modificación</th>
                            <th>Activo</th>
                            <th>Inventario</th>
                            <th>Aplica Inventario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['cve_producto']}</td>
                        <td>{$row['nombre']}</td>
                        <td>{$row['descripcion']}</td>
                        <td>{$row['fec_crea']}</td>
                        <td>{$row['fec_mod']}</td>
                        <td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>
                        <td>{$row['inventario']}</td>
                        <td>" . ($row['aplica_inventario'] ? 'Sí' : 'No') . "</td>
                        <td>
                            <a href='admin-producto.php?cve_usuario=$cve_usuario&edit={$row['cve_producto']}' class='btn btn-warning btn-sm'>Editar</a>
                        </td>
                    </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo '<div class="alert alert-warning">No se encontraron productos para el usuario con cve_usuario = ' . htmlspecialchars($cve_usuario) . '</div>';
        }

        $stmt->close();
        ?>

        <?php include "footer.php"; ?>
        <?php include "php/connect-close.php"; ?>

    </div>

</body>

</html>
