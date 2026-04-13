<?php
session_start();
include("backend/config/conexion.php");

// 🔒 Validar sesión y rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'artista') {
    header("Location: login.html");
    exit();
}

$artista_id = $_SESSION['usuario_id'];

// 📌 Obtener categorías
$stmt = $conexion->prepare("SELECT * FROM categorias");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📌 Obtener costos del artista
$stmtCostos = $conexion->prepare("
    SELECT costo_materiales, costo_mano_obra, costo_herramientas, costo_empaque
    FROM artistas
    WHERE id = ?
");
$stmtCostos->execute([$artista_id]);
$costos = $stmtCostos->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body class="index-body">

    <div class="perfil-card">
        <h2>Crear nuevo producto</h2>

        <form action="backend/guardar_post.php" method="POST" enctype="multipart/form-data" class="form-perfil">

            <div class="form-group">
                <label>Nombre del producto</label>
                <input type="text" name="nombre" required>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" required></textarea>
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id" required>
                    <option value="">Seleccione una categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo $cat['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tiempo de elaboración (horas)</label>
                <input type="number" name="tiempo_elaboracion" id="tiempo" required>
            </div>

            <div class="form-group">
                <label>Precio total (calculado)</label>
                <input type="number" id="precio_total" readonly>
            </div>

            <div class="form-group">
                <label>Imagen del producto</label>
                <input type="file" name="imagen" accept="image/*" required>
            </div>

            <button type="submit">Publicar producto</button>
        </form>
    </div>

    <script>
        // Costos del artista desde PHP
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