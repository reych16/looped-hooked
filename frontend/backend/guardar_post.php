<?php
session_start();
include(__DIR__ . "/config/conexion.php");

// 🔒 Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

$artista_id = $_SESSION['usuario_id'];

// 📥 Datos del formulario
$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$categoria_id = $_POST['categoria_id'];
$tiempo = $_POST['tiempo_elaboracion'];

// 🔎 Obtener costos del artista
$stmtCostos = $conexion->prepare("
    SELECT costo_materiales, costo_mano_obra, costo_herramientas, costo_empaque
    FROM artistas
    WHERE id = ?
");
$stmtCostos->execute([$artista_id]);
$costos = $stmtCostos->fetch(PDO::FETCH_ASSOC);

// 🧮 Calcular precio
$precio =
    $costos['costo_materiales'] +
    ($costos['costo_mano_obra'] * $tiempo) +
    $costos['costo_herramientas'] +
    $costos['costo_empaque'];

// 🧾 Insertar producto
$stmt = $conexion->prepare("
    INSERT INTO productos 
    (artista_id, nombre, descripcion, categoria_id, tiempo_elaboracion, precio_total)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $artista_id,
    $nombre,
    $descripcion,
    $categoria_id,
    $tiempo,
    $precio
]);

$producto_id = $conexion->lastInsertId();

// 📸 Subir imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {

    $carpeta = "../img/productos/";

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreArchivo = time() . "_" . basename($_FILES['imagen']['name']);
    $ruta = $carpeta . $nombreArchivo;

    move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);

    $rutaBD = "img/productos/" . $nombreArchivo;

    $stmtImg = $conexion->prepare("
        INSERT INTO imagenes_producto (producto_id, ruta, es_principal)
        VALUES (?, ?, 1)
    ");

    $stmtImg->execute([$producto_id, $rutaBD]);
}

// 🔄 Redirigir
header("Location: ../perfil.php");
exit();
