<?php
session_start();
include("backend/config/conexion.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'artista') {
    header("Location: login.html");
    exit();
}

$artista_id = $_SESSION['usuario_id'];

$stmt = $conexion->prepare("
    SELECT ordenes.*, usuarios.username AS cliente_nombre
    FROM ordenes
    LEFT JOIN usuarios ON ordenes.cliente_id = usuarios.id
    WHERE ordenes.artista_id = ? AND ordenes.visible_artista = 1
    ORDER BY ordenes.fecha DESC
");
$stmt->execute([$artista_id]);
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
    <title>Pedidos recibidos</title>
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
            <a href="perfil.php" class="header-link">Mi perfil</a>
            <a href="pedidos_artista.php" class="header-link">Pedidos</a>
            <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
        </div>
    </header>

    <section class="products-section">
        <div class="section-header">
            <h2>Pedidos recibidos</h2>
            <p>Aquí puedes revisar los pedidos que han realizado tus clientes.</p>
            <?php if (isset($_GET['eliminada']) && $_GET['eliminada'] === 'ok'): ?>
                <p style="color: green; font-weight: bold;">
                    La orden fue eliminada correctamente.
                </p>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <article class="pedido-card">
                        <div class="pedido-header">
                            <h3>Orden #<?php echo $orden['id']; ?></h3>
                            <p class="pedido-total">₡<?php echo number_format($orden['total'], 2); ?></p>
                        </div>

                        <div class="pedido-info">
                            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($orden['cliente_nombre'] ?? 'No disponible'); ?></p>
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

                        <div class="pedido-acciones">
                            <?php if (!empty($orden['comprobante_pago'])): ?>
                                <a href="<?php echo htmlspecialchars($orden['comprobante_pago']); ?>" target="_blank" class="btn-accion btn-comprobante">
                                    Ver comprobante
                                </a>
                            <?php endif; ?>

                            <?php if ($orden['estado'] === 'pago_enviado'): ?>
                                <form action="backend/actualizar_estado_orden.php" method="POST">
                                    <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                                    <input type="hidden" name="accion" value="verificar_pago">
                                    <button type="submit" class="btn-accion btn-verificar">Verificar pago</button>
                                </form>

                                <form action="backend/actualizar_estado_orden.php" method="POST">
                                    <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                                    <input type="hidden" name="accion" value="rechazar_pago">
                                    <button type="submit" class="btn-accion btn-rechazar">Rechazar pago</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($orden['estado'] === 'pago_verificado'): ?>
                                <form action="backend/actualizar_estado_orden.php" method="POST">
                                    <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                                    <input type="hidden" name="accion" value="en_elaboracion">
                                    <button type="submit" class="btn-accion btn-elaboracion">Pasar a elaboración</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($orden['estado'] === 'en_elaboracion'): ?>
                                <form action="backend/actualizar_estado_orden.php" method="POST">
                                    <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                                    <input type="hidden" name="accion" value="finalizar">
                                    <button type="submit" class="btn-accion btn-finalizar">Finalizar orden</button>
                                </form>
                            <?php endif; ?>

                            <?php if (in_array($orden['estado'], ['pendiente_pago', 'pago_rechazado', 'finalizado'])): ?>
                                <form action="backend/eliminar_orden_artista.php" method="POST"
                                    onsubmit="return confirm('¿Deseas ocultar esta orden de tu vista?');">
                                    <input type="hidden" name="orden_id" value="<?php echo $orden['id']; ?>">
                                    <button type="submit" class="btn-accion btn-rechazar">Eliminar orden</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tienes pedidos recibidos todavía.</p>
            <?php endif; ?>
        </div>
    </section>

</body>

</html>