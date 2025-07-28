<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . "/connect.php"; // Tu conexión a MySQL

//$dir = __DIR__ . "/img/producto/";
$dir = __DIR__ . "/../img/producto/";

$thumbWidth = 300;
$calidadWebp = 70;

$sql = "SELECT cve_usuario, cve_producto, imagen FROM producto";
$result = $conn->query($sql);

if (!$result) {
    die("Error al consultar base de datos: " . $conn->error);
}

$extensiones_validas = ['jpg', 'jpeg', 'png', 'webp'];

while ($row = $result->fetch_assoc()) {
    $cve_usuario = $row['cve_usuario'];
    $cve_producto = $row['cve_producto'];
    $imagen_original = $row['imagen'];

    if (empty($imagen_original)) {
        echo "⚠️  Producto $cve_usuario-$cve_producto no tiene imagen registrada. Saltando...\n";
        continue;
    }

    $ruta_original = $dir . $imagen_original;

    if (!file_exists($ruta_original)) {
        echo "❌ NOOO se encontró imagen: $ruta_original\n";
        continue;
    }

    $ext = strtolower(pathinfo($imagen_original, PATHINFO_EXTENSION));
    if (!in_array($ext, $extensiones_validas)) {
        echo "⚠️  Extensión no válida: $imagen_original\n";
        continue;
    }

    // Crear imagen desde archivo
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $img = @imagecreatefromjpeg($ruta_original);
            break;
        case 'png':
            $img = @imagecreatefrompng($ruta_original);
            break;
        case 'webp':
            $img = @imagecreatefromwebp($ruta_original);
            break;
        default:
            continue 2;
    }

    if (!$img) {
        echo "❌ No se pudo abrir imagen: $ruta_original\n";
        continue;
    }

    // Redimensionar conservando transparencia si aplica
    $ancho = imagesx($img);
    $alto = imagesy($img);
    $nuevoAlto = intval($alto * ($thumbWidth / $ancho));
    $thumb = imagecreatetruecolor($thumbWidth, $nuevoAlto);

    // Manejo de transparencia
    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);

    // Copiar redimensionando
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbWidth, $nuevoAlto, $ancho, $alto);

    // Guardar como .webp con nombre correcto
    $nuevo_nombre = "$cve_usuario-$cve_producto.webp";
    $ruta_nueva = $dir . $nuevo_nombre;

    if (imagewebp($thumb, $ruta_nueva, $calidadWebp)) {
        echo "✅ Imagen optimizada: $nuevo_nombre\n";

        // Actualizar base de datos si el nombre cambió
        if ($imagen_original !== $nuevo_nombre) {
            $stmt = $conn->prepare("UPDATE producto SET imagen = ?, fec_mod = NOW() WHERE cve_usuario = ? AND cve_producto = ?");
            $stmt->bind_param("sii", $nuevo_nombre, $cve_usuario, $cve_producto);
            $stmt->execute();
            echo "📦 BD actualizada: $nuevo_nombre\n";
        }

        // Borrar original si es diferente
        if ($imagen_original !== $nuevo_nombre && file_exists($ruta_original)) {
            unlink($ruta_original);
            echo "🗑️  Borrado original: $imagen_original\n";
        }
    } else {
        echo "❌ Error al guardar thumbnail de: $imagen_original\n";
    }

    imagedestroy($img);
    imagedestroy($thumb);
}

echo "🏁 Conversión y actualización terminada.\n";
