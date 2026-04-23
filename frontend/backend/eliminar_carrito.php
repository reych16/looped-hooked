<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrito_id = isset($_POST['carrito_id']) ? (int) $_POST['carrito_id'] : 0;
    $usuario_id = $_SESSION['usuario_id'];

    if ($carrito_id > 0) {
        $stmt = $conexion->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$carrito_id, $usuario_id]);
    }

    header("Location: ../carrito.php");
    exit();
}
?>