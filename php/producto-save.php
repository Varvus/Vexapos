<?php
include __DIR__ . "/error-reporting.php";
include __DIR__ . "/connect.php";

$cve_usuario = $_POST["cve_usuario"];
$cve_producto = $_POST["cve_producto"];
$nombre = $_POST["nombre"];
$descripcion = $_POST["descripcion"];
$precio = $_POST["precio"];
$activo = $_POST["activo"];
$inventario = $_POST["inventario"];
$aplica_inventario = $_POST["aplica_inventario"];

$imagen = $_POST["imagen"] ?? ""; // Imagen anterior por si no se cambia

// Si el cve_producto está vacío, significa que es una inserción
if (empty($cve_producto)) {
    $sql = "SELECT COALESCE(MAX(cve_producto), 0) + 1 AS next_cve_producto FROM producto WHERE cve_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cve_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cve_producto = $row['next_cve_producto'];
}

// Procesar imagen si se subió
if (isset($_FILES["imagen_archivo"]) && $_FILES["imagen_archivo"]["error"] === UPLOAD_ERR_OK) {
    $ext_permitidas = ["jpg", "jpeg", "png", "webp"];
    $nombre_original = $_FILES["imagen_archivo"]["name"];
    $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

    if (in_array($ext, $ext_permitidas)) {
        $nombre_base = "{$cve_usuario}-{$cve_producto}";
        $nombre_archivo = $nombre_base . ".webp";
        $ruta_thumb = __DIR__ . "/../img/producto/" . $nombre_archivo;

        // Cargar imagen
        $img_tmp = $_FILES["imagen_archivo"]["tmp_name"];
        switch ($ext) {
            case "jpeg":
            case "jpg":
                $original = imagecreatefromjpeg($img_tmp);
                break;
            case "png":
                $original = imagecreatefrompng($img_tmp);
                break;
            case "webp":
                $original = imagecreatefromwebp($img_tmp);
                break;
            default:
                die("Formato no soportado.");
        }

        // Crear thumbnail (300px de ancho)
        $ancho = imagesx($original);
        $alto = imagesy($original);
        $nuevoAncho = 300;
        $nuevoAlto = intval($alto * ($nuevoAncho / $ancho));
        $thumb = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagecopyresampled($thumb, $original, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
        imagewebp($thumb, $ruta_thumb, 70); // calidad del 70%

        imagedestroy($original);
        imagedestroy($thumb);

        $imagen = $nombre_archivo;
    } else {
        die("Extensión de archivo no permitida. Solo se permiten: jpg, jpeg, png, webp.");
    }
}

if (empty($_POST["cve_producto"])) {
    // Insertar nuevo producto
    $sql = "INSERT INTO producto (
        cve_usuario, cve_producto, nombre, descripcion, precio, imagen,
        activo, inventario, aplica_inventario, fec_crea, fec_mod
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdsiii", $cve_usuario, $cve_producto, $nombre, $descripcion, $precio, $imagen, $activo, $inventario, $aplica_inventario);
    $stmt->execute();
} else {
    // Actualizar producto existente
    $sql = "UPDATE producto SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, activo = ?, inventario = ?, aplica_inventario = ?, fec_mod = NOW()
            WHERE cve_usuario = ? AND cve_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsiisii", $nombre, $descripcion, $precio, $imagen, $activo, $inventario, $aplica_inventario, $cve_usuario, $cve_producto);
    $stmt->execute();
}

header("Location: /admin-producto.php");
exit;
