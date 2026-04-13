<?php
session_start();
include("backend/config/conexion.php");

// 🔒 Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 📦 Obtener favoritos del usuario
$stmt = $conexion->prepare("
    SELECT p.*, i.ruta
    FROM favoritos f
    INNER JOIN productos p ON f.producto_id = p.id
    LEFT JOIN imagenes_producto i 
        ON p.id = i.producto_id AND i.es_principal = 1
    WHERE f.usuario_id = ? AND p.activo = 1
    ORDER BY f.id DESC
");
$stmt->execute([$usuario_id]);

$favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis favoritos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="index-body">

    <!-- HEADER -->
    <header class="site-header">
        <div class="header-left">
            <a href="index.php">
                <img src="img/Looped&HookedLogo.png" class="site-logo">
            </a>
        </div>

        <div class="header-right">
            <a href="index.php" class="header-link">Inicio</a>
            <a href="catalogo.php" class="header-link">Catálogo</a>
            <a href="perfil.php" class="header-link">Perfil</a>
            <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
        </div>
    </header>

    <!-- FAVORITOS -->
    <section class="products-section">
        <div class="section-header">
            <h2>Mis favoritos ❤️</h2>
            <p>Productos que te han gustado.</p>
        </div>

        <div class="products-grid">

            <?php if (!empty($favoritos)): ?>
                <?php foreach ($favoritos as $prod): ?>

                    <article class="product-card">

                        <!-- Imagen -->
                        <?php if ($prod['ruta']): ?>
                            <img src="<?php echo $prod['ruta']; ?>" alt="<?php echo $prod['nombre']; ?>">
                        <?php else: ?>
                            <img src="img/default.png" alt="Sin imagen">
                        <?php endif; ?>

                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>

                            <p class="product-price">
                                ₡<?php echo number_format($prod['precio_total'], 0); ?>
                            </p>

                            <p class="product-meta">
                                Tiempo: <?php echo $prod['tiempo_elaboracion']; ?> hrs
                            </p>

                            <div class="acciones-producto">

                                <!-- Ver producto -->
                                <a href="ver_producto.php?id=<?php echo $prod['id']; ?>" class="product-btn">
                                    Ver producto
                                </a>

                                <!-- Quitar favorito -->
                                <a href="backend/quitar_favorito.php?id=<?php echo $prod['id']; ?>"
                                    class="btn-eliminar"
                                    onclick="return confirm('¿Quitar de favoritos?');">
                                    Quitar ❤️
                                </a>

                            </div>
                        </div>

                    </article>

                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">No tienes favoritos aún 😢</p>
            <?php endif; ?>

        </div>
    </section>

</body>

</html>