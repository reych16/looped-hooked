<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'artista') {
    header("Location: ../login.html");
    exit();
}

$artista_id = $_SESSION['usuario_id'];
$orden_id = $_POST['orden_id'] ?? null;

if (!$orden_id) {
    die("Orden no válida.");
}

$stmt = $conexion->prepare("
    SELECT *
    FROM ordenes
    WHERE id = ? AND artista_id = ?
");
$stmt->execute([$orden_id, $artista_id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("No tienes permiso para eliminar esta orden.");
}

$estadosPermitidos = ['pendiente_pago', 'pago_rechazado', 'finalizado'];

if (!in_array($orden['estado'], $estadosPermitidos)) {
    die("No se puede eliminar esta orden en su estado actual.");
}

$stmtUpdate = $conexion->prepare("
    UPDATE ordenes
    SET visible_artista = 0
    WHERE id = ? AND artista_id = ?
");
$stmtUpdate->execute([$orden_id, $artista_id]);

header("Location: ../pedidos_artista.php?eliminada=ok");
exit();
?>