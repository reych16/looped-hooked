<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $producto_id = isset($_POST['producto_id']) ? (int) $_POST['producto_id'] : 0;
    $cantidad = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 1;

    if ($producto_id <= 0) {
        die("Producto inválido.");
    }

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    $stmt = $conexion->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$usuario_id, $producto_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $nuevaCantidad = $item['cantidad'] + $cantidad;
        $stmtUpdate = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
        $stmtUpdate->execute([$nuevaCantidad, $item['id']]);
    } else {
        $stmtInsert = $conexion->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
        $stmtInsert->execute([$usuario_id, $producto_id, $cantidad]);
    }

    header("Location: ../carrito.php");
    exit();
}
?>