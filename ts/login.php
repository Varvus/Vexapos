<?php
session_start();
include __DIR__ . "/php/connect.php";

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    $stmt = $conn->prepare("SELECT cve_usuario, contrasena FROM usuario WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($user['contrasena'] === $contrasena) {
            $_SESSION['cve_usuario'] = $user['cve_usuario'];
            header("Location: admin-dashboard.php");
            exit;
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - VEXAPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card p-4 shadow" style="max-width: 400px; width: 100%;">
        <h4 class="mb-3 text-center">VEXAPOS</h4>
        <?php if ($mensaje): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Usuario</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" name="contrasena" id="contrasena" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
        </form>
    </div>

</body>

</html>