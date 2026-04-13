<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frontend/login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$producto_id = $_GET['id'];

// ❌ Eliminar favorito
$stmt = $conexion->prepare("
    DELETE FROM favoritos 
    WHERE usuario_id = ? AND producto_id = ?
");

$stmt->execute([$usuario_id, $producto_id]);

header("Location: ../frontend/favoritos.php");
exit();
