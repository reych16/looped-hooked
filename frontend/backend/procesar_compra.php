<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

$cliente_id = $_SESSION['usuario_id'];

// 1. Obtener productos del carrito
$stmt = $conexion->prepare("
    SELECT c.*, p.nombre, p.precio_total, p.artista_id
    FROM carrito c
    INNER JOIN productos p ON c.producto_id = p.id
    WHERE c.usuario_id = ?
");
$stmt->execute([$cliente_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    die("El carrito está vacío.");
}

// 2. Agrupar por artista
$ordenesPorArtista = [];

foreach ($items as $item) {
    $artista_id = $item['artista_id'];

    if (!isset($ordenesPorArtista[$artista_id])) {
        $ordenesPorArtista[$artista_id] = [];
    }

    $ordenesPorArtista[$artista_id][] = $item;
}

// 3. Crear órdenes por artista
foreach ($ordenesPorArtista as $artista_id => $productos) {

    $totalOrden = 0;

    foreach ($productos as $prod) {
        $totalOrden += $prod['precio_total'] * $prod['cantidad'];
    }

    // Insertar orden
    $stmtOrden = $conexion->prepare("
    INSERT INTO ordenes (cliente_id, artista_id, estado, estado_pago, total, fecha)
    VALUES (?, ?, 'pendiente_pago', 'pendiente', ?, NOW())
");
    $stmtOrden->execute([$cliente_id, $artista_id, $totalOrden]);

    $orden_id = $conexion->lastInsertId();

    // Insertar detalle
    foreach ($productos as $prod) {

        $subtotal = $prod['precio_total'] * $prod['cantidad'];

        $stmtDetalle = $conexion->prepare("
            INSERT INTO detalle_orden (orden_id, producto_id, artista_id, cantidad, precio_unitario, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtDetalle->execute([
            $orden_id,
            $prod['producto_id'],
            $artista_id,
            $prod['cantidad'],
            $prod['precio_total'],
            $subtotal
        ]);
    }

    // Crear notificación
    $stmtNotif = $conexion->prepare("
        INSERT INTO notificaciones (usuario_id, orden_id, titulo, mensaje)
        VALUES (?, ?, ?, ?)
    ");
    $stmtNotif->execute([
        $artista_id,
        $orden_id,
        "Nueva orden recibida",
        "Tienes una nueva orden con pago pendiente"
    ]);
}

// 4. Vaciar carrito
$stmtDelete = $conexion->prepare("DELETE FROM carrito WHERE usuario_id = ?");
$stmtDelete->execute([$cliente_id]);

header("Location: ../mis_pedidos.php?compra=ok");
exit();
