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
    die("Orden no válida.");
}

// Obtener la orden y verificar que pertenezca al cliente
$stmt = $conexion->prepare("
    SELECT ordenes.*, usuarios.username AS artista_nombre
    FROM ordenes
    LEFT JOIN usuarios ON ordenes.artista_id = usuarios.id
    WHERE ordenes.id = ? AND ordenes.cliente_id = ?
");
$stmt->execute([$orden_id, $usuario_id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("No tienes permiso para ver esta orden.");
}

// Obtener detalle de productos
$stmtDetalle = $conexion->prepare("
    SELECT detalle_orden.*, productos.nombre
    FROM detalle_orden
    LEFT JOIN productos ON detalle_orden.producto_id = productos.id
    WHERE detalle_orden.orden_id = ?
");
$stmtDetalle->execute([$orden_id]);
$detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Detalle del pedido</title>
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
            <a href="perfil.php" class="header-link">Mi perfil</a>
            <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
        </div>
    </header>

    <section class="products-section">
        <div class="section-header">
            <h2>Detalle del pedido #<?php echo $orden['id']; ?></h2>
            <p>Aquí puedes revisar toda la información de tu compra.</p>
        </div>

        <div class="pedido-card">
            <div class="pedido-header">
                <h3>Orden #<?php echo $orden['id']; ?></h3>
                <p class="pedido-total">₡<?php echo number_format($orden['total'], 2); ?></p>
            </div>

            <div class="pedido-info">
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
            </div>

            <div class="pedido-info">
                <h3>Productos incluidos</h3>

                <?php if (!empty($detalles)): ?>
                    <?php foreach ($detalles as $detalle): ?>
                        <div class="detalle-linea">
                            <p><strong>Producto:</strong> <?php echo htmlspecialchars($detalle['nombre'] ?? 'Producto no disponible'); ?></p>
                            <p><strong>Cantidad:</strong> <?php echo $detalle['cantidad']; ?></p>
                            <p><strong>Precio unitario:</strong> ₡<?php echo number_format($detalle['precio_unitario'], 2); ?></p>
                            <p><strong>Subtotal:</strong> ₡<?php echo number_format($detalle['subtotal'], 2); ?></p>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay productos asociados a esta orden.</p>
                <?php endif; ?>
            </div>

            <div class="pedido-acciones">
                <?php if ($orden['estado'] === 'pendiente_pago'): ?>
                    <a href="subir_comprobante.php?id=<?php echo $orden['id']; ?>" class="btn-accion btn-verificar">
                        Subir comprobante
                    </a>
                <?php endif; ?>

                <?php if (in_array($orden['estado'], ['pago_enviado', 'pago_verificado', 'en_elaboracion', 'finalizado'])): ?>
                    <a href="backend/generar_factura.php?id=<?php echo $orden['id']; ?>" target="_blank" class="btn-accion btn-comprobante">
                        Descargar factura PDF
                    </a>
                <?php endif; ?>

                <?php if (in_array($orden['estado'], ['pendiente_pago', 'pago_rechazado', 'finalizado'])): ?>
                    <form action="backend/eliminar_orden.php" method="POST"
                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta orden?');">
                        <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                        <button type="submit" class="btn-accion btn-rechazar">
                            Eliminar orden
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>

</html>