<?php
include "php/connect.php"; // Conexión a la base de datos
include "php/verifica-usuario.php";
?>

<!DOCTYPE html>
<html>

<head>
    <?php include "initials.php"; ?>
    <title>VEXAPOS: Admin: Producto</title>
</head>

<body>

    <?php include "admin-menu.php"; ?>

    <div class="container">

        <h2 class="d-inline-block">Productos</h2>
        <a class="d-inline-block badge badge text-bg-primary text-decoration-none"
            href="admin-producto-form.php?cve_usuario=<?= $cve_usuario ?>">
            Agregar</a>
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
                            <th>Precio</th>
                            <th>Imagen</th>
                            <th>Activo</th>
                            <th>Inventario</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>";

            while ($row = $result->fetch_assoc()) {
                $imagen_url = $_SERVER['DOCUMENT_ROOT'] . "/img/producto/" . $row['imagen'];  // Ruta completa de la imagen
        
                // Verificar si el archivo de imagen existe
                if (file_exists($imagen_url) && $row['imagen'] != "") {
                    $imagen_tag = "<img src='/img/producto/" . $row['imagen'] . "' alt='{$row['nombre']}' style='max-width: 100px;'>";
                } else {
                    $imagen_tag = "";  // No mostrar nada si la imagen no existe
                }

                echo "<tr>
                        <td>{$row['cve_producto']}</td>
                        <td>{$row['nombre']}</td>
                        <td>{$row['descripcion']}</td>
                        <td>$" . number_format($row['precio'], 2) . "</td>
                        <td>{$imagen_tag}</td> <!-- Mostrar imagen si existe -->
                        <td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>
                        <td>{$row['inventario']}</td>
                        <td>
                            <a href='admin-producto-form.php?edit={$row['cve_producto']}' class='btn btn-warning'>Editar</a>
                        </td>
                    </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo '<div class="alert alert-warning">No se encontraron productos para este usuario.</div>';
        }
        ?>

        <?php include __DIR__ . "/footer.php"; ?>
    </div>

</body>

</html>