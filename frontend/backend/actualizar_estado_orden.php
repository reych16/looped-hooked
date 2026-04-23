<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'artista') {
    header("Location: ../login.html");
    exit();
}

$artista_id = $_SESSION['usuario_id'];
$orden_id = $_POST['orden_id'] ?? null;
$accion = $_POST['accion'] ?? '';

if (!$orden_id || !$accion) {
    die("Datos incompletos.");
}

// Verificar que la orden pertenezca al artista
$stmt = $conexion->prepare("
    SELECT *
    FROM ordenes
    WHERE id = ? AND artista_id = ?
");
$stmt->execute([$orden_id, $artista_id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("No tienes permiso para modificar esta orden.");
}

switch ($accion) {
    case 'verificar_pago':
        $stmtUpdate = $conexion->prepare("
            UPDATE ordenes
            SET estado = 'pago_verificado', estado_pago = 'verificado'
            WHERE id = ? AND artista_id = ?
        ");
        $stmtUpdate->execute([$orden_id, $artista_id]);
        break;

    case 'rechazar_pago':
        $stmtUpdate = $conexion->prepare("
            UPDATE ordenes
            SET estado = 'pago_rechazado', estado_pago = 'rechazado'
            WHERE id = ? AND artista_id = ?
        ");
        $stmtUpdate->execute([$orden_id, $artista_id]);
        break;

    case 'en_elaboracion':
        $stmtUpdate = $conexion->prepare("
            UPDATE ordenes
            SET estado = 'en_elaboracion'
            WHERE id = ? AND artista_id = ? AND estado_pago = 'verificado'
        ");
        $stmtUpdate->execute([$orden_id, $artista_id]);
        break;

    case 'finalizar':
        $stmtUpdate = $conexion->prepare("
            UPDATE ordenes
            SET estado = 'finalizado'
            WHERE id = ? AND artista_id = ? AND estado_pago = 'verificado'
        ");
        $stmtUpdate->execute([$orden_id, $artista_id]);
        break;

    default:
        die("Acción no válida.");
}

header("Location: ../pedidos_artista.php");
exit();
?>