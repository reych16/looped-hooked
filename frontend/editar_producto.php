<?php
session_start();
include("backend/config/conexion.php");

// 🔒 Validar sesión
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'artista') {
    header("Location: login.html");
    exit();
}

$artista_id = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? null;

// 📌 Obtener producto
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ? AND artista_id = ?");
$stmt->execute([$id, $artista_id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "Producto no encontrado";
    exit();
}

// 📌 Categorías
$stmtCat = $conexion->prepare("SELECT * FROM categorias");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// 📌 Imagen actual
$stmtImg = $conexion->prepare("SELECT * FROM imagenes_producto WHERE producto_id = ? AND es_principal = 1");
$stmtImg->execute([$id]);
$imagen = $stmtImg->fetch(PDO::FETCH_ASSOC);

// 📌 Costos artista
$stmtCostos = $conexion->prepare("
    SELECT costo_materiales, costo_mano_obra, costo_herramientas, costo_empaque
    FROM artistas WHERE id = ?
");
$stmtCostos->execute([$artista_id]);
$costos = $stmtCostos->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="index-body">

    <div class="perfil-card">
        <h2>Editar producto</h2>

        <form action="backend/actualizar_producto.php" method="POST" enctype="multipart/form-data" class="form-perfil">

            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?php echo $producto['nombre']; ?>" required>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" required><?php echo $producto['descripcion']; ?></textarea>
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id" required>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($cat['id'] == $producto['categoria_id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tiempo de elaboración</label>
                <input type="number" name="tiempo_elaboracion" id="tiempo"
                    value="<?php echo $producto['tiempo_elaboracion']; ?>" required>
            </div>

            <div class="form-group">
                <label>Precio (automático)</label>
                <input type="number" id="precio_total" value="<?php echo $producto['precio_total']; ?>" readonly>
            </div>

            <!-- IMAGEN ACTUAL -->
            <?php if ($imagen): ?>
                <div class="form-group">
                    <label>Imagen actual</label><br>
                    <img src="<?php echo $imagen['ruta']; ?>" width="150">
                </div>
            <?php endif; ?>

            <!-- NUEVA IMAGEN -->
            <div class="form-group">
                <label>Cambiar imagen (opcional)</label>
                <input type="file" name="imagen" accept="image/*">
            </div>

            <button type="submit">Actualizar producto</button>
        </form>
    </div>

    <script>
        const materiales = <?php echo $costos['costo_materiales'] ?? 0; ?>;
        const mano = <?php echo $costos['costo_mano_obra'] ?? 0; ?>;
        const herramientas = <?php echo $costos['costo_herramientas'] ?? 0; ?>;
        const empaque = <?php echo $costos['costo_empaque'] ?? 0; ?>;

        function calcularPrecio() {
            const tiempo = parseFloat(document.getElementById("tiempo").value) || 0;
            const total = materiales + (mano * tiempo) + herramientas + empaque;
            document.getElementById("precio_total").value = total.toFixed(2);
        }

        document.getElementById("tiempo").addEventListener("input", calcularPrecio);
    </script>

</body>

</html>