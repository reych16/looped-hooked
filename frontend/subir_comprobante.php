<?php
session_start();
include("backend/config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$orden_id = $_GET['id'] ?? null;

if (!$orden_id) {
    die("Orden no válida");
}

$stmt = $conexion->prepare("
    SELECT *
    FROM ordenes
    WHERE id = ? AND cliente_id = ?
");
$stmt->execute([$orden_id, $usuario_id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("No tienes permiso para ver esta orden.");
}

function formatearEstado($estado)
{
    $estados = [
        'pendiente_pago' => 'Pendiente de pago',
        'pago_enviado' => 'Pago enviado',
        'pago_verificado' => 'Pago verificado',
        'pago_rechazado' => 'Pago rechazado',
        'en_elaboracion' => 'En elaboración',
        'finalizado' => 'Finalizado'
    ];

    return $estados[$estado] ?? $estado;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Subir comprobante</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="index-body">

    <header class="site-header">
        <div class="header-left">
            <a href="index.php">
                <img src="img/Looped&HookedLogo.png" class="site-logo" alt="Logo">
            </a>
        </div>

        <div class="header-right">
            <a href="index.php" class="header-link">Inicio</a>
            <a href="favoritos.php" class="header-link">Favoritos</a>
            <a href="carrito.php" class="header-link">Carrito</a>
            <a href="mis_pedidos.php" class="header-link">Mis pedidos</a>
            <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
        </div>
    </header>

    <div class="perfil-card">
        <h2>Subir comprobante de pago</h2>

        <p><strong>Orden:</strong> #<?php echo $orden['id']; ?></p>
        <p><strong>Total:</strong> ₡<?php echo number_format($orden['total'], 2); ?></p>
        <p>
            <strong>Estado actual:</strong>
            <span class="estado-badge estado-<?php echo $orden['estado']; ?>">
                <?php echo formatearEstado($orden['estado']); ?>
            </span>
        </p>

        <form action="backend/procesar_comprobante.php" method="POST" enctype="multipart/form-data" class="form-perfil">
            <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">

            <div class="form-group">
                <label>Selecciona el comprobante (imagen o PDF)</label>
                <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>

            <button type="submit">Enviar comprobante</button>
        </form>
    </div>

</body>

</html>