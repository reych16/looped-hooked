<?php
session_start();
include(__DIR__ . "/config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frontend/login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$id = $_GET['id'];

// 🔎 Obtener imágenes para borrarlas del servidor
$stmtImg = $conexion->prepare("
    SELECT ruta FROM imagenes_producto WHERE producto_id = ?
");
$stmtImg->execute([$id]);
$imagenes = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

// 🗑️ Borrar archivos físicos
foreach ($imagenes as $img) {
    $ruta = "../frontend/" . $img['ruta'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}

// 🗑️ Borrar imágenes de BD
$stmt = $conexion->prepare("
    DELETE FROM imagenes_producto WHERE producto_id = ?
");
$stmt->execute([$id]);

// 🗑️ Borrar producto (solo si es del usuario)
$stmt = $conexion->prepare("
    DELETE FROM productos WHERE id = ? AND artista_id = ?
");
$stmt->execute([$id, $usuario_id]);

header("Location: ../frontend/perfil.php");
exit();
