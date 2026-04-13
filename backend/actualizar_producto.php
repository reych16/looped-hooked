<?php
session_start();
include(__DIR__ . "/config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frontend/login.html");
    exit();
}

$artista_id = $_SESSION['usuario_id'];

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$categoria_id = $_POST['categoria_id'];
$tiempo = $_POST['tiempo_elaboracion'];

// 🔎 Costos artista
$stmtCostos = $conexion->prepare("
    SELECT costo_materiales, costo_mano_obra, costo_herramientas, costo_empaque
    FROM artistas WHERE id = ?
");
$stmtCostos->execute([$artista_id]);
$costos = $stmtCostos->fetch(PDO::FETCH_ASSOC);

// 🧮 Recalcular precio
$precio =
    $costos['costo_materiales'] +
    ($costos['costo_mano_obra'] * $tiempo) +
    $costos['costo_herramientas'] +
    $costos['costo_empaque'];

// ✏️ Actualizar producto
$stmt = $conexion->prepare("
    UPDATE productos
    SET nombre=?, descripcion=?, categoria_id=?, tiempo_elaboracion=?, precio_total=?
    WHERE id=? AND artista_id=?
");

$stmt->execute([
    $nombre,
    $descripcion,
    $categoria_id,
    $tiempo,
    $precio,
    $id,
    $artista_id
]);

// =======================
// 📸 ACTUALIZAR IMAGEN
// =======================
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {

    // 🔎 Obtener imagen actual
    $stmtImg = $conexion->prepare("SELECT * FROM imagenes_producto WHERE producto_id = ? AND es_principal = 1");
    $stmtImg->execute([$id]);
    $imagenActual = $stmtImg->fetch(PDO::FETCH_ASSOC);

    // ❌ Eliminar archivo viejo
    if ($imagenActual) {
        $rutaFisica = "../frontend/" . $imagenActual['ruta'];
        if (file_exists($rutaFisica)) {
            unlink($rutaFisica);
        }

        // eliminar registro
        $del = $conexion->prepare("DELETE FROM imagenes_producto WHERE id = ?");
        $del->execute([$imagenActual['id']]);
    }

    // 📁 Guardar nueva imagen
    $carpeta = "../frontend/img/productos/";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreArchivo = time() . "_" . basename($_FILES['imagen']['name']);
    $ruta = $carpeta . $nombreArchivo;

    move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);

    $rutaBD = "img/productos/" . $nombreArchivo;

    $stmtNew = $conexion->prepare("
        INSERT INTO imagenes_producto (producto_id, ruta, es_principal)
        VALUES (?, ?, 1)
    ");
    $stmtNew->execute([$id, $rutaBD]);
}

header("Location: ../frontend/perfil.php");
exit();
