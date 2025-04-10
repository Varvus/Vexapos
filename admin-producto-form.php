<?php
include __DIR__ . "/php/connect.php"; // Conexión a la base de datos
include __DIR__ . "/admin-menu.php"; // Menú de administración
$cve_usuario = 1; // Usuario fijo, como pediste
include __DIR__ . "/php/verifica-usuario.php"; // Verificación de usuario

// Verificar si se pasa un cve_producto para edición
$edit_mode = false;
$edit_producto = [
    "cve_producto" => "",
    "nombre" => "",
    "descripcion" => "",
    "activo" => 1,
    "inventario" => 0,
    "aplica_inventario" => 0
];

if (isset($_GET["edit"])) {
    $edit_mode = true;
    $cve_producto_edit = $_GET["edit"];

    // Obtener los detalles del producto para editar
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

<!DOCTYPE html>
<html>

<head>
    <title>VEXAPOS: Admin: Agregar / Editar Producto</title>
</head>

<body>
    <div class="container">

        <h2><?= $edit_mode ? 'Editar Producto' : 'Agregar Producto' ?></h2>
        <hr>

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
                <textarea class="form-control" name="descripcion"
                    rows="3"><?= $edit_producto['descripcion'] ?></textarea>
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

            <button type="submit"
                class="btn btn-primary"><?= $edit_mode ? 'Actualizar Producto' : 'Agregar Producto' ?></button>
        </form>

        <?php include __DIR__ . "/footer.php"; // Pie de página ?>
    </div>
</body>

</html>