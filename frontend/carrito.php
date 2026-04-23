<?php
session_start();
include("backend/config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexion->prepare("
    SELECT 
        c.id AS carrito_id,
        c.cantidad,
        p.id AS producto_id,
        p.nombre,
        p.descripcion,
        p.precio_total,
        i.ruta
    FROM carrito c
    INNER JOIN productos p ON c.producto_id = p.id
    LEFT JOIN imagenes_producto i ON p.id = i.producto_id AND i.es_principal = 1
    WHERE c.usuario_id = ?
");
$stmt->execute([$usuario_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi carrito</title>
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
            <h2>Mi carrito</h2>
            <p>Revisa los productos antes de comprar.</p>
            <?php if (isset($_GET['compra']) && $_GET['compra'] === 'ok'): ?>
                <p style="color: green; font-weight: bold;">
                    Compra realizada correctamente. Tu orden fue registrada con estado de pago pendiente.
                </p>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <?php
                    $subtotal = $item['cantidad'] * $item['precio_total'];
                    $total += $subtotal;
                    ?>
                    <article class="product-card">
                        <?php if ($item['ruta']): ?>
                            <img src="<?php echo $item['ruta']; ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                        <?php else: ?>
                            <img src="img/default.png" alt="Sin imagen">
                        <?php endif; ?>

                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($item['descripcion']); ?></p>
                            <p class="product-price">₡<?php echo number_format($item['precio_total'], 0); ?></p>

                            <div class="control-cantidad">
                                <form action="backend/actualizar_carrito.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="carrito_id" value="<?php echo $item['carrito_id']; ?>">
                                    <input type="hidden" name="accion" value="restar">
                                    <button type="submit">-</button>
                                </form>

                                <span><strong>Cantidad:</strong> <?php echo $item['cantidad']; ?></span>

                                <form action="backend/actualizar_carrito.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="carrito_id" value="<?php echo $item['carrito_id']; ?>">
                                    <input type="hidden" name="accion" value="sumar">
                                    <button type="submit">+</button>
                                </form>
                            </div>

                            <p><strong>Subtotal:</strong> ₡<?php echo number_format($subtotal, 0); ?></p>

                            <form action="backend/eliminar_carrito.php" method="POST"
                                onsubmit="return confirm('¿Estás seguro de que quieres eliminar todas las piezas de este producto del carrito?');">
                                <input type="hidden" name="carrito_id" value="<?php echo $item['carrito_id']; ?>">
                                <button type="submit" class="product-btn">Eliminar del carrito</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tienes productos en el carrito.</p>
            <?php endif; ?>
        </div>
        <?php if (!empty($items)): ?>
            <div style="margin-top:20px; text-align:center;">
                <h3>Total: ₡<?php echo number_format($total, 0); ?></h3>
                <form action="backend/procesar_compra.php" method="POST">
                    <button type="submit" class="product-btn">Comprar</button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</body>

</html>