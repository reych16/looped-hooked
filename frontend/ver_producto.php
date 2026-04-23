<?php
session_start();
include("backend/config/conexion.php");

// 🔎 Validar ID
if (!isset($_GET['id'])) {
    echo "Producto no encontrado";
    exit();
}

$id = $_GET['id'];

// 📦 Obtener producto + imagen + artista
$stmt = $conexion->prepare("
    SELECT p.*, i.ruta, u.username,
        a.costo_materiales, a.costo_mano_obra,
        a.costo_herramientas, a.costo_empaque
    FROM productos p
    LEFT JOIN imagenes_producto i 
        ON p.id = i.producto_id AND i.es_principal = 1
    LEFT JOIN usuarios u 
        ON p.artista_id = u.id
    LEFT JOIN artistas a 
        ON u.id = a.id
    WHERE p.id = ? AND p.activo = 1
");

$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "Producto no encontrado";
    exit();
}

// Calcular desglose
$tiempo = $producto['tiempo_elaboracion'];

$materiales = $producto['costo_materiales'];
$mano = $producto['costo_mano_obra'] * $tiempo;
$herramientas = $producto['costo_herramientas'];
$empaque = $producto['costo_empaque'];
$total = $producto['precio_total'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($producto['nombre']); ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="index-body">

    <header class="site-header">
        <div class="header-left">
            <a href="index.php">
                <img src="img/Looped&HookedLogo.png" class="site-logo">
            </a>
        </div>

        <div class="header-right">
            <a href="index.php" class="header-link">Inicio</a>
            <a href="#" class="header-link" onclick="verificarSesion('favoritos')">
                Favoritos
            </a>
            <a href="carrito.php" class="header-link">Carrito</a>

            <?php if (isset($_SESSION['username'])): ?>
                <a href="perfil.php" class="header-link">
                    Perfil de <?php echo $_SESSION['username']; ?>
                </a>
                <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.html" class="header-link">Mi cuenta</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="producto-detalle-card">

        <div class="producto-detalle-contenido">

            <!-- IMAGEN -->
            <div class="producto-imagen">
                <img src="<?php echo $producto['ruta']; ?>" alt="<?php echo $producto['nombre']; ?>">
            </div>

            <!-- INFO -->
            <div class="producto-info-detalle">
                <h2><?php echo $producto['nombre']; ?></h2>

                <p class="precio">₡<?php echo number_format($producto['precio_total'], 0); ?></p>

                <p><strong>Artista:</strong> <?php echo $producto['username']; ?></p>

                <p><strong>Descripción:</strong></p>
                <p><?php echo $producto['descripcion']; ?></p>

                <p><strong>Tiempo de elaboración:</strong> <?php echo $producto['tiempo_elaboracion']; ?> horas</p>

                <h3>Desglose del precio</h3>

                <div class="desglose-precio">
                    <p><span>Materiales:</span> ₡<?php echo number_format($producto['costo_materiales'], 0); ?></p>
                    <p><span>Mano de obra:</span> ₡<?php echo number_format($producto['costo_mano_obra'], 0); ?> x <?php echo $producto['tiempo_elaboracion']; ?> hrs</p>
                    <p><span>Herramientas:</span> ₡<?php echo number_format($producto['costo_herramientas'], 0); ?></p>
                    <p><span>Empaque:</span> ₡<?php echo number_format($producto['costo_empaque'], 0); ?></p>

                    <hr>

                    <p class="total">
                        <span>Total:</span> ₡<?php echo number_format($producto['precio_total'], 0); ?>
                    </p>
                </div>

                <!-- BOTONES -->
                <div class="acciones-detalle">
                    <form action="backend/agregar_carrito.php" method="POST" class="form-carrito">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn-carrito">Agregar al carrito 🛒</button>
                    </form>
                    <button type="button" onclick="agregarFavorito(<?php echo $producto['id']; ?>)">
                        Agregar a favoritos ❤️
                    </button>
                    <a href="catalogo.php" class="btn-volver">← Volver</a>
                </div>
            </div>
        </div>
    </div>

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

        function agregarFavorito(idProducto) {
            fetch('backend/verificar_sesion.php')
                .then(res => res.json())
                .then(data => {
                    if (data.logueado) {

                        // Aquí luego puedes conectar con BD
                        alert("Agregado a favoritos ❤️");

                    } else {
                        alert("Debes iniciar sesión primero");
                        window.location.href = "login.html";
                    }
                });
        }

        function agregarFavorito(productoId) {
            fetch('backend/agregar_favorito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'producto_id=' + productoId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        alert("Agregado a favoritos ❤️");
                    } else {
                        alert("Ya está en favoritos o debes iniciar sesión");
                    }
                });
        }
    </script>
</body>
</html>