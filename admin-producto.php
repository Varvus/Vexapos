<!DOCTYPE html>
<html>

    <head>

        <?php include "initials.php"; ?>
        <title>VEXAPOS: Admin: Producto</title>

    </head>
    <body>
        
        <?php include "php/connect.php"; ?>
        <?php include "admin-menu.php" ?>   
    
        <h2>Producto</h2>
        <hr>

        <?php
        $cve_usuario = $_GET["wk_usua"]; // Este valor puede ser dinámico dependiendo de tu implementación

        if ($cve_usuario == ""){
            echo '<div class="alert alert-danger">ACCESO DENEGADO</div>';
            return;
        }

        // Consulta SQL
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
                    p.cve_usuario = ?"; // Uso de parámetros preparados para evitar SQL injection
        
        // Preparar la consulta
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cve_usuario); // Vincula el valor de $cve_usuario al parámetro en la consulta
        $stmt->execute(); // Ejecuta la consulta

        // Obtener el resultado
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Si hay resultados, mostrar los productos en una tabla
            echo "<table>
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
                        </tr>
                    </thead>
                    <tbody>";
        
            // Mostrar los datos de cada producto
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['cve_producto'] . "</td>
                        <td>" . $row['nombre'] . "</td>
                        <td>" . $row['descripcion'] . "</td>
                        <td>" . $row['fec_crea'] . "</td>
                        <td>" . $row['fec_mod'] . "</td>
                        <td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>
                        <td>" . $row['inventario'] . "</td>
                        <td>" . ($row['aplica_inventario'] ? 'Sí' : 'No') . "</td>
                      </tr>";
            }
        
            echo "</tbody>
                </table>";
        } else {
            echo "<p>No se encontraron productos para el usuario con cve_usuario = $cve_usuario.</p>";
        }

        ?>


        <?php include "footer.php"; ?>
        <?php include "php/connect-close.php"; ?>

    </body>

</html>