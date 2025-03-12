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

        <h2>Agregar / Editar Producto</h2>
        <hr>

        <?php
        // Obtener el siguiente cve_producto para el usuario
        $sql = "SELECT COALESCE(MAX(cve_producto), 0) + 1 AS next_cve_producto FROM producto WHERE cve_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cve_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $next_cve_producto = $row['next_cve_producto'];

        // Variables para edición
        $edit_mode = false;
        $edit_producto = ["cve_producto" => $next_cve_producto, "nombre" => "", "descripcion" => "", "activo" => 1, "inventario" => 0, "aplica_inventario" => 0];

        if (isset($_GET["edit"])) {
            $edit_mode = true;
            $cve_producto_edit = $_GET["edit"];

            $sql = "SELECT * FROM producto WHERE cve_usuario = ? AND cve_producto = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $cve_usuario, $cve_producto_edit);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $edit_producto = $result->fetch_assoc();
            }
        }
        ?>

        <!-- Formulario para agregar / editar producto -->
        <form method="POST" action="php/producto-save.php">
            <input type="hidden" name="cve_usuario" value="<?= $cve_usuario ?>">
            <input type="hidden" name="cve_producto" value="<?= $edit_producto['cve_producto'] ?>">

            <div>
                <label class="form-label">*Nombre de Producto</label>
                <input type="text" class="form-control" name="nombre" value="<?= $edit_producto['nombre'] ?>" required>
            </div>

            <div>
                <label class="form-label">Descripción del Producto</label>
                <textarea class="form-control" name="descripcion" rows="3"><?= $edit_producto['descripcion'] ?></textarea>
            </div>

            <div>
                <label class="form-label">Activo</label>
                <select class="form-control" name="activo">
                    <option value="1" <?= $edit_producto['activo'] ? 'selected' : '' ?>>Sí</option>
                    <option value="0" <?= !$edit_producto['activo'] ? 'selected' : '' ?>>No</option>
                </select>
            </div>

            <div>
                <label class="form-label">Inventario</label>
                <input type="number" class="form-control" name="inventario" value="<?= $edit_producto['inventario'] ?>">
            </div>

            <div>
                <label class="form-label">Aplica Inventario</label>
                <select class="form-control" name="aplica_inventario">
                    <option value="1" <?= $edit_producto['aplica_inventario'] ? 'selected' : '' ?>>Sí</option>
                    <option value="0" <?= !$edit_producto['aplica_inventario'] ? 'selected' : '' ?>>No</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><?= $edit_mode ? 'Actualizar' : 'Agregar' ?> Producto</button>
        </form>

        <h2>Productos</h2>
        <hr>

        <?php
        // Obtener la lista de productos
        $sql = "SELECT * FROM producto WHERE cve_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cve_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<table class='table'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Activo</th>
                            <th>Inventario</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['cve_producto']}</td>
                        <td>{$row['nombre']}</td>
                        <td>{$row['descripcion']}</td>
                        <td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>
                        <td>{$row['inventario']}</td>
                        <td>
                            <a href='?edit={$row['cve_producto']}' class='btn btn-warning'>Editar</a>
                        </td>
                    </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo '<div class="alert alert-warning">No se encontraron productos para este usuario.</div>';
        }
        ?>

        <?php include "footer.php"; ?>
        <?php include "php/connect-close.php"; ?>

    </div>

</body>

</html>
