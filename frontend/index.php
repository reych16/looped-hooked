<?php
session_start();
?>
<?php
include("backend/config/conexion.php");

// 📦 Obtener productos (puedes limitar si quieres)
$stmt = $conexion->prepare("
    SELECT p.*, i.ruta 
    FROM productos p
    LEFT JOIN imagenes_producto i 
        ON p.id = i.producto_id AND i.es_principal = 1
    WHERE p.activo = 1
    ORDER BY p.id DESC
    LIMIT 6
");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
// 🔹 Obtener artistas reales
$stmtArtistas = $conexion->prepare("
    SELECT u.id, u.username, u.nombre_completo, a.especialidad
    FROM usuarios u
    INNER JOIN artistas a ON u.id = a.id
    WHERE u.estado = 'activo'
    LIMIT 6
");
$stmtArtistas->execute();

$artistas = $stmtArtistas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Looped & Hooked</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="index-body">

    <!-- HEADER -->
    <header class="site-header">
        <div class="header-left">
            <img src="img/Looped&HookedLogo.png" alt="Logo Looped & Hooked" class="site-logo">
        </div>

        <form class="header-center" action="catalogo.php" method="GET">

            <input
                type="text"
                name="busqueda"
                placeholder="Buscar productos artesanales..."
                class="search-input">

            <button type="submit" class="search-btn">Buscar</button>

        </form>

        <div class="header-right">
            <?php if (isset($_SESSION['username'])): ?>
                <a href="perfil.php" class="header-link">
                    Perfil de <?php echo $_SESSION['username']; ?>
                </a>
                <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.html" class="header-link">Mi cuenta</a>
            <?php endif; ?>
            <a href="#" class="header-link" onclick="verificarSesion('favorito')">
                Favoritos
            </a>
            <a href="#" class="header-link" onclick="verificarSesion('carrito')">
                Carrito
            </a>
        </div>
    </header>

    <!-- NAVBAR -->
    <nav class="main-nav">
        <a href="#Inicio">Inicio</a>
        <a href="#categorias">Categorías</a>
        <a href="#Productos">Productos</a>
        <a href="#artistas">Artistas</a>
        <a href="#nosotros">Nosotros</a>
        <a href="#contacto">Contacto</a>
    </nav>

    <!-- HERO -->
    <section id="Inicio" class="hero-section">
        <div class="hero-text">
            <span class="hero-badge">Hecho a mano con amor</span>
            <h1>Descubre piezas artesanales únicas</h1>
            <p>
                Encuentra productos tejidos a mano, hechos con creatividad,
                dedicación y detalles que hacen cada pieza especial.
            </p>
            <div class="hero-actions">
                <a href="catalogo.php" class="primary-btn">Explorar productos</a>
                <a href="registro.html" class="secondary-btn">Únete como artista</a>
            </div>
        </div>

        <div class="hero-image">
            <img src="img/Maceta.png" alt="Producto artesanal destacado">
        </div>
    </section>

    <!-- CATEGORÍAS -->
    <section id="categorias" class="categories-section">
        <div class="section-header">
            <h2>Categorías destacadas</h2>
            <p>Explora nuestras colecciones más buscadas.</p>
        </div>

        <div class="categories-grid">

            <a href="catalogo.php?categoria=1" class="category-card category-peach">
                <h3>Ropa</h3>
                <p>Diseños tejidos con personalidad.</p>
            </a>

            <a href="catalogo.php?categoria=2" class="category-card category-pink">
                <h3>Accesorios</h3>
                <p>Piezas pequeñas con mucho estilo.</p>
            </a>

            <a href="catalogo.php?categoria=3" class="category-card category-lilac">
                <h3>Amigurumis</h3>
                <p>Creaciones adorables hechas a mano.</p>
            </a>

            <a href="catalogo.php?categoria=4" class="category-card category-cream">
                <h3>Decoración</h3>
                <p>Detalles únicos para tu espacio.</p>
            </a>

            <a href="catalogo.php?categoria=5" class="category-card category-blue">
                <h3>Mascotas</h3>
                <p>Accesorios para tus peludos.</p>
            </a>

            <a href="catalogo.php?categoria=6" class="category-card category-yellow">
                <h3>Regalos</h3>
                <p>Opciones especiales para obsequiar.</p>
            </a>

            <a href="catalogo.php?categoria=7" class="category-card category-green">
                <h3>Plantas</h3>
                <p>Decoración natural tejida.</p>
            </a>

        </div>
    </section>

    <!-- PRODUCTOS DESTACADOS -->
    <section id="Productos" class="products-section">
        <div class="section-header">
            <h2>Productos destacados</h2>
            <p>Algunas de las creaciones favoritas de nuestra comunidad.</p>
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

    <!-- BANNER PROMOCIONAL -->
    <section class="promo-banner">
        <div class="promo-text">
            <h2>Hecho a mano, pensado para ti</h2>
            <p>
                Apoya el talento artesanal y descubre creaciones auténticas
                con historia, detalle y dedicación.
            </p>
            <a href="#" class="primary-btn">Comprar ahora</a>
        </div>
    </section>

    <!-- ARTISTAS -->
    <section id="artistas" class="artists-section">
        <div class="section-header">
            <h2>Artistas destacados</h2>
            <p>Conoce parte del talento detrás de cada creación.</p>
        </div>

        <div class="artists-grid">

            <?php if (!empty($artistas)): ?>
                <?php foreach ($artistas as $artista): ?>

                    <a href="ver_artista.php?id=<?php echo $artista['id']; ?>" class="artist-card">
                        <h3>
                            <?php echo htmlspecialchars($artista['username'] ?? $artista['username']); ?>
                        </h3>

                        <p>
                            Especialidad:
                            <?php echo htmlspecialchars($artista['especialidad'] ?? 'Artesanía'); ?>
                        </p>
        </div>

    <?php endforeach; ?>
<?php else: ?>
    <p>No hay artistas disponibles.</p>
<?php endif; ?>

</div>
    </section>

    <!-- VIDEO -->
    <section class="video-section">
        <div class="section-header">
            <h2>Conoce nuestro proceso creativo</h2>
            <p>Así se transforman las ideas en piezas artesanales.</p>
        </div>

        <div class="video-box">
            <video controls width="100%">
                <source src="img/VideoCrochet.mp4" type="video/mp4">
                Tu navegador no soporta video.
            </video>
        </div>
    </section>

    <!-- NOSOTROS -->
    <section id="nosotros" class="about-section">
        <div class="section-header">
            <h2>Sobre nosotros</h2>
        </div>

        <p class="about-text">
            Looped & Hooked es un marketplace dedicado a conectar personas que aman
            los productos tejidos a mano con artistas que convierten creatividad,
            dedicación y detalle en piezas únicas. Nuestro objetivo es impulsar el
            talento artesanal y ofrecer un espacio donde cada creación tenga valor,
            historia y estilo propio.
        </p>
    </section>

    <!-- FOOTER -->
    <footer id="contacto" class="site-footer">
        <div class="footer-brand">
            <img src="img/Looped&HookedLogo.png" alt="Logo Looped & Hooked" class="footer-logo">
            <p>Tejiendo creatividad, detalle y amor en cada pieza.</p>
        </div>

        <div class="footer-links">
            <h4>Enlaces</h4>
            <a href="#Inicio">Inicio</a>
            <a href="#Productos">Productos</a>
            <a href="#artistas">Artistas</a>
            <a href="#contacto">Contacto</a>
        </div>

        <div class="footer-social">
            <h4>Redes sociales</h4>
            <a href="#">Instagram</a>
            <a href="#">Facebook</a>
            <a href="#">WhatsApp</a>
        </div>
    </footer>

    <script>
        function verificarSesion(accion) {
            fetch('backend/verificar_sesion.php', {
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