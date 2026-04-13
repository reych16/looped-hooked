<?php
session_start();
include("../backend/config/conexion.php");

$categoria_id = $_GET['categoria'] ?? null;
$precio_max = $_GET['precio_max'] ?? null;
$busqueda = $_GET['busqueda'] ?? null;

// 📦 Query base
$sql = "
    SELECT p.*, i.ruta, c.nombre AS categoria_nombre
    FROM productos p
    LEFT JOIN imagenes_producto i 
        ON p.id = i.producto_id AND i.es_principal = 1
    LEFT JOIN categorias c
        ON p.categoria_id = c.id
    WHERE p.activo = 1
";

$params = [];

// 🔎 BUSCADOR
if ($busqueda) {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

// 🔎 Filtro por categoría
if ($categoria_id) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria_id;
}

// 💰 Filtro por precio máximo
if ($precio_max) {
    $sql .= " AND p.precio_total <= ?";
    $params[] = $precio_max;
}

$sql .= " ORDER BY p.id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📌 Obtener categorías para el filtro
$stmtCat = $conexion->prepare("SELECT * FROM categorias");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Catálogo - Looped & Hooked</title>
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
            <a href="#" class="header-link" onclick="verificarSesion('favorito')">
                Favoritos
            </a>
            <a href="#" class="header-link" onclick="verificarSesion('carrito')">
                Carrito
            </a>

            <?php if (isset($_SESSION['username'])): ?>
                <a href="perfil.php" class="header-link">
                    Perfil de <?php echo $_SESSION['username']; ?>
                </a>
                <a href="../backend/logout.php" class="header-link">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.html" class="header-link">Mi cuenta</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- CATÁLOGO -->
    <section class="products-section">
        <div class="section-header">

            <h2>Catálogo de productos</h2>
            <p>Explora todas las creaciones disponibles.</p>

            <?php if (!empty($busqueda)): ?>
                <p>
                    Resultados para:
                    <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                </p>
            <?php endif; ?>

        </div>

        <div class="filtros-box">

            <form method="GET" class="filtros-form">

                <!-- Categoría -->
                <select name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php if ($categoria_id == $cat['id']) echo 'selected'; ?>>
                            <?php echo $cat['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Precio -->
                <input
                    type="number"
                    name="precio_max"
                    placeholder="Precio máximo ₡"
                    value="<?php echo htmlspecialchars($precio_max ?? ''); ?>">

                <button type="submit">Filtrar</button>

                <!-- Limpiar filtros -->
                <a href="catalogo.php" class="btn-limpiar">Limpiar</a>

            </form>

        </div>

        <div class="products-grid">

            <?php if (!empty($productos)): ?>
                <?php foreach ($productos as $prod): ?>

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

                            <a href="ver_producto.php?id=<?php echo $prod['id']; ?>" class="product-btn">
                                Ver producto
                            </a>
                        </div>

                    </article>

                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay productos disponibles.</p>
            <?php endif; ?>

        </div>
    </section>

    <!-- SCRIPT (igual al index) -->
    <script>
        function verificarSesion(accion) {
            fetch('../backend/verificar_sesion.php', {
                    credentials: 'include'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.logueado) {

                        if (accion === 'favoritos') {
                            window.location.href = "favoritos.php";
                        } else if (accion === 'carrito') {
                            window.location.href = "carrito.php";
                        }

                    } else {
                        alert("Debes iniciar sesión primero");
                        window.location.href = "login.html";
                    }
                });
        }
    </script>

</body>

</html>