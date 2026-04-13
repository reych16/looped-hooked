<?php
session_start();
include("backend/config/conexion.php");

// ✅ Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['usuario_id'];

// ✅ Preparar y ejecutar la consulta
$stmt = $conexion->prepare("
    SELECT u.*, a.especialidad, a.costo_materiales, a.costo_mano_obra,
           a.costo_herramientas, a.costo_empaque, a.disponible
    FROM usuarios u
    LEFT JOIN artistas a ON u.id = a.id
    WHERE u.id = ?
");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Validar que el usuario exista
if (!$usuario) {
    echo "Error: usuario no encontrado";
    exit();
}

// Si es artista, obtenemos sus productos
$productos = [];
if ($usuario['rol'] === 'artista') {
    $stmtProd = $conexion->prepare("SELECT * FROM productos WHERE artista_id = ?");
    $stmtProd->execute([$id]);
    $productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="index-body">

    <header class="site-header">
        <div class="header-left">
            <a href="index.php">
                <img src="img/Looped&HookedLogo.png" alt="Logo Looped & Hooked" class="site-logo">
            </a>
        </div>

        <div class="header-right">
            <a href="index.php" class="header-link">Inicio</a>
            <a href="favoritos.php" class="header-link">Favoritos</a>
            <a href="carrito.php" class="header-link">Carrito</a>

            <?php if (isset($_SESSION['username'])): ?>

                <a href="backend/logout.php" class="header-link">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php" class="header-link">Mi cuenta</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="contenido">

        <div class="perfil-card">
            <h2>Información del usuario</h2>

            <div class="foto-perfil">
                <?php if (!empty($usuario['foto_perfil'])): ?>
                    <img src="<?php echo $usuario['foto_perfil']; ?>" alt="Foto de perfil">
                <?php else: ?>
                    <img src="img/default-user.png" alt="Sin foto">
                <?php endif; ?>
            </div>

            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['username']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['correo']); ?></p>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre_completo']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono']); ?></p>

            <button onclick="mostrarFormulario()">Editar perfil</button>

            <?php if ($usuario['rol'] === 'artista'): ?>
                <button onclick="mostrarArtista()">Editar información del artista</button>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['errores'])): ?>
            <div style="color:red;">
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['errores']); ?>
        <?php endif; ?>

        <!-- FORMULARIO -->
        <div id="formEditar" style="display:<?php echo isset($_SESSION['errores']) ? 'block' : 'none'; ?>;" class="perfil-card">
            <h2>Editar datos</h2>

            <form action="backend/actualizar_perfil.php" method="POST" enctype="multipart/form-data" class="form-perfil">

                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Nombre de usuario</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
                </div>

                <div class="form-group">
                    <label>Foto de perfil</label>
                    <input type="file" name="foto_perfil" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Contraseña actual</label>
                    <input type="password" name="password_actual" required>
                </div>

                <button type="submit">Guardar cambios</button>
            </form>
        </div>

        <?php if ($usuario['rol'] === 'artista'): ?>
            <div id="formArtista" style="display:none;" class="perfil-card">
                <h2>Editar información del artista</h2>

                <form action="backend/actualizar_perfil.php" method="POST" class="form-perfil">

                    <div class="form-group">
                        <label>Especialidad</label>
                        <input type="text" name="especialidad"
                            value="<?php echo htmlspecialchars($usuario['especialidad'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Costo de materiales</label>
                        <input type="number" step="0.01" name="costo_materiales"
                            value="<?php echo $usuario['costo_materiales'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label>Costo de mano de obra</label>
                        <input type="number" step="0.01" name="costo_mano_obra"
                            value="<?php echo $usuario['costo_mano_obra'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label>Costo de herramientas</label>
                        <input type="number" step="0.01" name="costo_herramientas"
                            value="<?php echo $usuario['costo_herramientas'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label>Costo de empaque</label>
                        <input type="number" step="0.01" name="costo_empaque"
                            value="<?php echo $usuario['costo_empaque'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="disponible"
                                <?php echo ($usuario['disponible'] ?? 1) ? 'checked' : ''; ?>>
                            Disponible
                        </label>
                    </div>

                    <button type="submit">Guardar información</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($usuario['rol'] === 'artista'): ?>
            <div class="perfil-card productos-section">
                <h2>Mis productos</h2>
                <div class="contenedor-boton">
                    <a href="nuevo_post.php" class="btn-agregar">Agregar nuevo producto</a>
                </div>

                <?php if (!empty($productos)): ?>
                    <div class="productos-grid">
                        <?php foreach ($productos as $prod): ?>
                            <div class="producto-card">

                                <?php
                                $stmtImg = $conexion->prepare("SELECT * FROM imagenes_producto WHERE producto_id = ? AND es_principal = 1");
                                $stmtImg->execute([$prod['id']]);
                                $imagen = $stmtImg->fetch(PDO::FETCH_ASSOC);
                                ?>

                                <?php if ($imagen): ?>
                                    <div class="img-container">
                                        <img src="<?php echo $imagen['ruta']; ?>" alt="<?php echo $prod['nombre']; ?>">
                                    </div>
                                <?php endif; ?>

                                <h3><?php echo $prod['nombre']; ?></h3>
                                <p><?php echo $prod['descripcion']; ?></p>
                                <p class="precio">₡<?php echo number_format($prod['precio_total'], 2); ?></p>

                                <div class="acciones-producto">
                                    <a href="editar_producto.php?id=<?php echo $prod['id']; ?>" class="btn-editar">Editar</a>
                                    <a href="backend/eliminar_producto.php?id=<?php echo $prod['id']; ?>" class="btn-eliminar"
                                        onclick="return confirm('¿Eliminar este producto?');">Eliminar</a>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No tienes productos aún.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <script>
        function mostrarFormulario() {
            document.getElementById("formEditar").style.display = "block";
            if (document.getElementById("formArtista")) {
                document.getElementById("formArtista").style.display = "none";
            }
        }

        function mostrarArtista() {
            document.getElementById("formArtista").style.display = "block";
            document.getElementById("formEditar").style.display = "none";
        }
    </script>

</body>

</html>