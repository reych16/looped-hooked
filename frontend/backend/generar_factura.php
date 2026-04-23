<?php
session_start();

require_once __DIR__ . "/../../vendor/autoload.php";
include("config/conexion.php");

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$orden_id = $_GET['id'] ?? null;

if (!$orden_id) {
    die("Orden no válida.");
}

// Verificar que la orden pertenezca al cliente logueado
$stmt = $conexion->prepare("
    SELECT ordenes.*, usuarios.username AS artista_nombre, clientes.username AS cliente_nombre
    FROM ordenes
    LEFT JOIN usuarios ON ordenes.artista_id = usuarios.id
    LEFT JOIN usuarios AS clientes ON ordenes.cliente_id = clientes.id
    WHERE ordenes.id = ? AND ordenes.cliente_id = ?
");
$stmt->execute([$orden_id, $usuario_id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die("No tienes permiso para generar esta factura.");
}

// Obtener detalle de productos
$stmtDetalle = $conexion->prepare("
    SELECT detalle_orden.*, productos.nombre
    FROM detalle_orden
    LEFT JOIN productos ON detalle_orden.producto_id = productos.id
    WHERE detalle_orden.orden_id = ?
");
$stmtDetalle->execute([$orden_id]);
$detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

function formatearEstado($estado) {
    $estados = [
        'pendiente_pago' => 'Pendiente de pago',
        'pago_enviado' => 'Pago enviado',
        'pago_verificado' => 'Pago verificado',
        'pago_rechazado' => 'Pago rechazado',
        'en_elaboracion' => 'En elaboración',
        'finalizado' => 'Finalizado'
    ];

    return $estados[$estado] ?? $estado;
}

function formatearEstadoPago($estado) {
    $estados = [
        'pendiente' => 'Pendiente',
        'verificado' => 'Verificado',
        'rechazado' => 'Rechazado'
    ];

    return $estados[$estado] ?? $estado;
}

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            margin: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #b266b3;
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #777;
        }

        .box {
            border: 1px solid #e3d6ea;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .box h2 {
            margin-top: 0;
            color: #9d5fd0;
            font-size: 20px;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #f1e4f7;
            color: #6e3a92;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #d57c96;
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }

        .estado {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            background: #f3e8b5;
            color: #7a5a00;
            font-weight: bold;
            font-size: 12px;
        }

        .estado-pago {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            background: #d8f0df;
            color: #1f6b35;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Looped & Hooked</h1>
        <p>Factura de pedido</p>
    </div>

    <div class="box">
        <h2>Información del pedido</h2>
        <div class="info-row"><span class="label">Orden:</span> #' . htmlspecialchars($orden['id']) . '</div>
        <div class="info-row"><span class="label">Cliente:</span> ' . htmlspecialchars($orden['cliente_nombre'] ?? 'No disponible') . '</div>
        <div class="info-row"><span class="label">Artista:</span> ' . htmlspecialchars($orden['artista_nombre'] ?? 'No disponible') . '</div>
        <div class="info-row"><span class="label">Fecha:</span> ' . htmlspecialchars($orden['fecha']) . '</div>
        <div class="info-row"><span class="label">Estado:</span> <span class="estado">' . htmlspecialchars(formatearEstado($orden['estado'])) . '</span></div>
        <div class="info-row"><span class="label">Estado del pago:</span> <span class="estado-pago">' . htmlspecialchars(formatearEstadoPago($orden['estado_pago'])) . '</span></div>
    </div>

    <div class="box">
        <h2>Detalle de productos</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>';

foreach ($detalles as $detalle) {
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($detalle['nombre'] ?? 'Producto no disponible') . '</td>
                    <td>' . htmlspecialchars($detalle['cantidad']) . '</td>
                    <td>₡' . number_format($detalle['precio_unitario'], 2) . '</td>
                    <td>₡' . number_format($detalle['subtotal'], 2) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>

        <div class="total">
            Total: ₡' . number_format($orden['total'], 2) . '
        </div>
    </div>

    <div class="footer">
        Documento generado automáticamente por Looped & Hooked
    </div>
</body>
</html>
';

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("factura_orden_" . $orden['id'] . ".pdf", ["Attachment" => false]);
exit();
?>