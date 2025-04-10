<?php
include "php/connect.php"; // Conexión a la base de datos

$cve_usuario = 1;
include "php/verifica-usuario.php";
?>

<!DOCTYPE html>
<html>

<head>

    <?php include "initials.php"; ?>
    <title>VEXAPOS: Admin: Producto</title>

</head>

<body>
    <div class="container">

        <?php include "admin-menu.php"; ?>

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
                        <td><a href='?edit={$row['cve_producto']}' class='btn btn-warning'>Editar</a></td>
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

        <?php include "/footer.php"; // Pie de página ?>


    </div>
</body>

</html>