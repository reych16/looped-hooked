<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["ok" => false]);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$producto_id = $_POST['producto_id'];

try {
    $stmt = $conexion->prepare("
        INSERT INTO favoritos (usuario_id, producto_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$usuario_id, $producto_id]);

    echo json_encode(["ok" => true]);

} catch (PDOException $e) {
    // ya existe (por UNIQUE)
    echo json_encode(["ok" => false]);
}