<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=utf-8");
include("conexion.php");
mysqli_set_charset($conexion, "utf8mb4");

if (!isset($_GET['id_pedido'])) {
    echo json_encode(["error" => "No se especificó el ID del pedido."]);
    exit;
}

$id_pedido = intval($_GET['id_pedido']);

$sqlPedido = "SELECT 
                pedidos.id_pedido,
                pedidos.fecha_pedido,
                pedidos.direccion,
                pedidos.estado,
                pedidos.descuento,
                clientes.nombreCompleto,
                clientes.DNI
              FROM pedidos
              INNER JOIN clientes ON pedidos.id_cliente = clientes.id_cliente
              WHERE pedidos.id_pedido = ?";

$stmtPedido = $conexion->prepare($sqlPedido);
$stmtPedido->bind_param("i", $id_pedido);
$stmtPedido->execute();
$pedido = $stmtPedido->get_result()->fetch_assoc();

if (!$pedido) {
    echo json_encode(["error" => "No se encontró el pedido."]);
    exit;
}
$sqlDetalle = "SELECT
                detalle_pedido.prenda,
                detalle_pedido.cantidad,
                detalle_pedido.precio_unitario,
                detalle_pedido.subtotal,
                servicios.nombre AS servicio
               FROM detalle_pedido
               INNER JOIN servicios ON detalle_pedido.id_servicio = servicios.id_servicio
               WHERE detalle_pedido.id_pedido = ?";

$stmtDetalle = $conexion->prepare($sqlDetalle);
$stmtDetalle->bind_param("i", $id_pedido);
$stmtDetalle->execute();
$resultadoDetalle = $stmtDetalle->get_result();

$detalles = array();
$subtotal_calculado = 0;

while ($detalle = $resultadoDetalle->fetch_assoc()) {
    $detalles[] = $detalle;
    
    $subtotal_calculado += floatval($detalle['subtotal']);
}


$pedido['subtotal'] = $subtotal_calculado;
$pedido['costo_envio'] = 0; 
$pedido['total'] = $subtotal_calculado - floatval($pedido['descuento']) + $pedido['costo_envio'];

$datos = array(
    "pedido" => $pedido,
    "detalles" => $detalles
);

if (ob_get_length()) ob_clean();
echo json_encode($datos, JSON_UNESCAPED_UNICODE);

$stmtPedido->close();
$stmtDetalle->close();
$conexion->close();
exit;
?>