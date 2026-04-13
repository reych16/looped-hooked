<?php
session_start();
include("backend/config/conexion.php");

// 🔒 Validar ID
if (!isset($_GET['id'])) {
    echo "Artista no encontrado";
    exit();
}

$id = $_GET['id'];

// 🔹 Obtener info del artista
$stmt = $conexion->prepare("
    SELECT u.id, u.username, u.nombre_completo, u.foto_perfil, a.especialidad
    FROM usuarios u
    INNER JOIN artistas a ON u.id = a.id
    WHERE u.id = ?
");
$stmt->execute([$id]);
$artista = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artista) {
    echo "Artista no encontrado";
    exit();
}

// 🔹 Obtener productos del artista
$stmtProd = $conexion->prepare("
    SELECT p.*, i.ruta
    FROM productos p
    LEFT JOIN imagenes_producto i 
        ON p.id = i.producto_id AND i.es_principal = 1
    WHERE p.artista_id = ? AND p.activo = 1
");
$stmtProd->execute([$id]);
$productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Perfil del artista</title>
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
            <a href="#" class="header-link" onclick="verificarSesion('favoritos')">
                Favoritos
            </a>
            <a href="#" class="header-link" onclick="verificarSesion('carrito')">
                Carrito
            </a>

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

    <!-- PERFIL DEL ARTISTA -->
    <div class="perfil-artista-card">

        <!-- INFO -->
        <div class="artista-header">

            <!-- FOTO -->
            <img
                src="<?php echo $artista['foto_perfil'] ? $artista['foto_perfil'] : 'img/default-user.png'; ?>"
                class="foto-artista">

            <div>
                <h2><?php echo htmlspecialchars($artista['username']); ?></h2>
                <p><strong>Especialidad:</strong>
                    <?php echo htmlspecialchars($artista['especialidad']); ?>
                </p>
            </div>

        </div>

        <!-- PRODUCTOS -->
        <h3>Productos del artista</h3>

        <div class="productos-grid">

            <?php if (!empty($productos)): ?>
                <?php foreach ($productos as $prod): ?>

                    <div class="producto-card">

                        <!-- Imagen -->
                        <?php if ($prod['ruta']): ?>
                            <img src="<?php echo $prod['ruta']; ?>">
                        <?php else: ?>
                            <img src="img/default.png">
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>

                        <p class="precio">
                            ₡<?php echo number_format($prod['precio_total'], 0); ?>
                        </p>

                        <a href="ver_producto.php?id=<?php echo $prod['id']; ?>" class="btn-ver">
                            Ver producto
                        </a>

                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p>Este artista aún no tiene productos.</p>
            <?php endif; ?>

        </div>

    </div>

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