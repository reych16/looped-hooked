<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$orden_id = $_POST['orden_id'] ?? null;

if (!$orden_id) {
    die("Orden no válida.");
}

$stmt = $conexion->prepare("
    SELECT *
    FROM ordenes
    WHERE id = ? AND cliente_id = ?
");
$stmt->execute([$orden_id, $usuario_id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("No tienes permiso para modificar esta orden.");
}

if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== 0) {
    die("Debes seleccionar un archivo válido.");
}

$carpeta = __DIR__ . "/../img/comprobantes/";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$nombreArchivo = time() . "_" . basename($_FILES['comprobante']['name']);
$rutaFisica = $carpeta . $nombreArchivo;

if (!move_uploaded_file($_FILES['comprobante']['tmp_name'], $rutaFisica)) {
    die("Error al guardar el comprobante.");
}

$rutaBD = "img/comprobantes/" . $nombreArchivo;

$stmtUpdate = $conexion->prepare("
    UPDATE ordenes
    SET comprobante_pago = ?, estado = 'pago_enviado', estado_pago = 'pendiente'
    WHERE id = ? AND cliente_id = ?
");
$stmtUpdate->execute([$rutaBD, $orden_id, $usuario_id]);

header("Location: ../mis_pedidos.php?comprobante=ok");
exit();
?>