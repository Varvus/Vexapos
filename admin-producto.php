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
        <h2>Agregar Producto</h2>
        <hr>

        <form method="POST" action="php/producto-save.php">
            <input type="hidden" name="cve_usuario" value="1"> 

            <div>
                <label class="form-label">*Nombre de Producto</label>
                <input type="text" class="form-control" name="nombre" required>
            </div>

            <div>
                <label class="form-label">Descripción del Producto</label>
                <textarea class="form-control" name="descripcion" rows="3"></textarea>
            </div>

            <div>
                <label class="form-label">Activo</label>
                <select class="form-control" name="activo">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div>
                <label class="form-label">Inventario</label>
                <input type="number" class="form-control" name="inventario" value="0">
            </div>

            <div>
                <label class="form-label">Aplica Inventario</label>
                <select class="form-control" name="aplica_inventario">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Agregar Producto</button>
        </form>

        <h2>Productos</h2>
        <hr>

        <?php
        $cve_usuario = 1;
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
                    </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo '<div class="alert alert-warning">No se encontraron productos para este usuario.</div>';
        }

        $stmt->close();
        ?>

        <?php include "footer.php"; ?>
        <?php include "php/connect-close.php"; ?>
    </div>
</body>

</html>

