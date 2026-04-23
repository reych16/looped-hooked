<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrito_id = isset($_POST['carrito_id']) ? (int) $_POST['carrito_id'] : 0;
    $accion = $_POST['accion'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];

    if ($carrito_id > 0 && in_array($accion, ['sumar', 'restar'])) {
        $stmt = $conexion->prepare("SELECT cantidad FROM carrito WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$carrito_id, $usuario_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $cantidadActual = (int) $item['cantidad'];

            if ($accion === 'sumar') {
                $nuevaCantidad = $cantidadActual + 1;

                $stmtUpdate = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?");
                $stmtUpdate->execute([$nuevaCantidad, $carrito_id, $usuario_id]);

            } elseif ($accion === 'restar') {
                if ($cantidadActual > 1) {
                    $nuevaCantidad = $cantidadActual - 1;

                    $stmtUpdate = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?");
                    $stmtUpdate->execute([$nuevaCantidad, $carrito_id, $usuario_id]);
                } else {
                    $stmtDelete = $conexion->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
                    $stmtDelete->execute([$carrito_id, $usuario_id]);
                }
            }
        }
    }

    header("Location: ../carrito.php");
    exit();
}
?>