<?php
session_start();
include("backend/config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexion->prepare("
    SELECT ordenes.*, usuarios.username AS artista_nombre
    FROM ordenes
    LEFT JOIN usuarios ON ordenes.artista_id = usuarios.id
    WHERE ordenes.cliente_id = ? AND ordenes.visible_cliente = 1
    ORDER BY ordenes.fecha DESC
");
$stmt->execute([$usuario_id]);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

function formatearEstadoPago($estado)
{
    $estados = [
        'pendiente' => 'Pendiente',
        'verificado' => 'Verificado',
        'rechazado' => 'Rechazado'
    ];

    return $estados[$estado] ?? $estado;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis pedidos</title>
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
            <a href="perfil.php" class="header-link">Mi perfil</a>
            <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
        </div>
    </header>

    <section class="products-section">
        <div class="section-header">
            <h2>Mis pedidos</h2>
            <p>Aquí puedes ver el estado de tus compras.</p>
            <?php if (isset($_GET['eliminada']) && $_GET['eliminada'] === 'ok'): ?>
                <p style="color: green; font-weight: bold;">
                    La orden fue eliminada correctamente.
                </p>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <article class="product-card">
                        <div class="product-info">
                            <h3>Orden #<?php echo $orden['id']; ?></h3>
                            <p><strong>Artista:</strong> <?php echo htmlspecialchars($orden['artista_nombre'] ?? 'No disponible'); ?></p>
                            <p><strong>Fecha:</strong> <?php echo $orden['fecha']; ?></p>

                            <p>
                                <strong>Estado:</strong>
                                <span class="estado-badge estado-<?php echo $orden['estado']; ?>">
                                    <?php echo formatearEstado($orden['estado']); ?>
                                </span>
                            </p>

                            <p>
                                <strong>Estado del pago:</strong>
                                <span class="estado-pago-badge estado-pago-<?php echo $orden['estado_pago']; ?>">
                                    <?php echo formatearEstadoPago($orden['estado_pago']); ?>
                                </span>
                            </p>

                            <p class="product-price">₡<?php echo number_format($orden['total'], 2); ?></p>

                            <a href="ver_pedido.php?id=<?php echo $orden['id']; ?>" class="product-btn">
                                Ver pedido
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tienes pedidos registrados todavía.</p>
            <?php endif; ?>
        </div>
    </section>

</body>

</html>