<?php
session_start();
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

        <div class="header-center">
            <input type="text" placeholder="Buscar productos artesanales..." class="search-input">
            <button class="search-btn">Buscar</button>
        </div>

        <div class="header-right">
            <?php if (isset($_SESSION['username'])): ?>
                <a href="perfil.php" class="header-link">
                    Perfil de <?php echo $_SESSION['username']; ?>
                </a>
                <a href="../backend/logout.php" class="header-link">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.html" class="header-link">Mi cuenta</a>
            <?php endif; ?>
            <a href="#" class="header-link" onclick="verificarSesion('favorito')">
                Favoritos
            </a>
            <a href="#" class="header-link">Carrito</a>
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
                <a href="#" class="primary-btn">Explorar productos</a>
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
            <div class="category-card category-pink">
                <h3>Accesorios</h3>
                <p>Piezas pequeñas con mucho estilo.</p>
            </div>

            <div class="category-card category-lilac">
                <h3>Decoración</h3>
                <p>Detalles únicos para tu espacio.</p>
            </div>

            <div class="category-card category-peach">
                <h3>Ropa</h3>
                <p>Diseños tejidos con personalidad.</p>
            </div>

            <div class="category-card category-green">
                <h3>Regalos</h3>
                <p>Opciones especiales para obsequiar.</p>
            </div>

            <div class="category-card category-cream">
                <h3>Peluches</h3>
                <p>Creaciones adorables hechas a mano.</p>
            </div>
        </div>
    </section>

    <!-- PRODUCTOS DESTACADOS -->
    <section id="Productos" class="products-section">
        <div class="section-header">
            <h2>Productos destacados</h2>
            <p>Algunas de las creaciones favoritas de nuestra comunidad.</p>
        </div>

        <div class="products-grid">
            <article class="product-card">
                <img src="img/Maceta.png" alt="Maceta colgante">
                <div class="product-info">
                    <h3>Maceta con planta colgante</h3>
                    <p class="product-price">₡10 000</p>
                    <p class="product-meta">Material: Lana</p>
                    <button class="product-btn" onclick="verificarSesion('producto')">
                        Ver producto
                    </button>
                </div>
            </article>

            <article class="product-card">
                <img src="img/pokeball.png" alt="Monedero Pokeball">
                <div class="product-info">
                    <h3>Monedero Pokeball</h3>
                    <p class="product-price">₡8 000</p>
                    <p class="product-meta">Material: Lana</p>
                    <button class="product-btn" onclick="verificarSesion('producto')">
                        Ver producto
                    </button>
                </div>
            </article>

            <article class="product-card">
                <img src="img/Sueter.png" alt="Suéter con flores">
                <div class="product-info">
                    <h3>Suéter con flores</h3>
                    <p class="product-price">₡20 000</p>
                    <p class="product-meta">Material: Algodón</p>
                    <button class="product-btn" onclick="verificarSesion('producto')">
                        Ver producto
                    </button>
                </div>
            </article>
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
            <div class="artist-card">
                <h3>Ana López</h3>
                <p>Especialidad: Decoración tejida</p>
            </div>

            <div class="artist-card">
                <h3>María Gómez</h3>
                <p>Especialidad: Accesorios crochet</p>
            </div>

            <div class="artist-card">
                <h3>Sofía Vargas</h3>
                <p>Especialidad: Ropa artesanal</p>
            </div>
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
            fetch('../backend/verificar_sesion.php')
                .then(res => res.json())
                .then(data => {
                    if (data.logueado) {
                        if (accion === 'producto') {
                            alert("Aquí iría el producto");
                        }
                        if (accion === 'favorito') {
                            alert("Agregado a favoritos ❤️");
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