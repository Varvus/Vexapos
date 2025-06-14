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
                        <img src="img/producto/<?= htmlspecialchars($p['imagen']) ?>" class="card-img-top"
                            alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <div class="card-body text-center">
                            <h6 class="card-title"><?= htmlspecialchars($p['nombre']) ?></h6>
                            <p class="card-text fw-bold">$<?= number_format($p['precio'], 2) ?></p>
                            <div class="cantidad-container d-none">
                                <input type="
