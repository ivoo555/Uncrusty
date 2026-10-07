<?php

error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");

if (!isset($_GET['id_pedido'])) {

    echo json_encode([
        "error" => "No se especificó el ID del pedido."
    ]);

    exit;
}

$id_pedido = intval($_GET['id_pedido']);


// ======================================================
// OBTENER DATOS DEL PEDIDO
// ======================================================

$sqlPedido = "
    SELECT 
        pedidos.id_pedido,
        pedidos.fecha_pedido,
        pedidos.direccion,
        pedidos.estado,
        pedidos.id_descuento,

        cupones.codigo,
        cupones.valor_descuento,

        clientes.nombreCompleto,
        clientes.DNI

    FROM pedidos

    INNER JOIN clientes
        ON pedidos.id_cliente = clientes.id_cliente

    LEFT JOIN cupones
        ON pedidos.id_descuento = cupones.id_cupon

    WHERE pedidos.id_pedido = ?
";

$stmtPedido = $conexion->prepare($sqlPedido);

if (!$stmtPedido) {

    echo json_encode([
        "error" => "Error al preparar la consulta del pedido."
    ]);

    exit;
}

$stmtPedido->bind_param(
    "i",
    $id_pedido
);

$stmtPedido->execute();

$pedido = $stmtPedido
    ->get_result()
    ->fetch_assoc();

if (!$pedido) {

    echo json_encode([
        "error" => "No se encontró el pedido."
    ]);

    exit;
}


// ======================================================
// OBTENER DETALLES Y PRECIO DESDE SERVICIOS
// ======================================================

$sqlDetalle = "
    SELECT
        detalle_pedido.id_detalle,
        detalle_pedido.id_servicio,
        detalle_pedido.prenda,
        detalle_pedido.cantidad,

        servicios.nombre AS servicio,
        servicios.precio AS precio_unitario

    FROM detalle_pedido

    INNER JOIN servicios
        ON detalle_pedido.id_servicio =
           servicios.id_servicio

    WHERE detalle_pedido.id_pedido = ?
";

$stmtDetalle = $conexion->prepare($sqlDetalle);

if (!$stmtDetalle) {

    echo json_encode([
        "error" => "Error al preparar los detalles del pedido."
    ]);

    exit;
}

$stmtDetalle->bind_param(
    "i",
    $id_pedido
);

$stmtDetalle->execute();

$resultadoDetalle = $stmtDetalle->get_result();

$detalles = [];

$subtotal_calculado = 0;


// ======================================================
// CALCULAR SUBTOTAL
// ======================================================

while ($detalle = $resultadoDetalle->fetch_assoc()) {

    $precio_unitario =
        floatval($detalle['precio_unitario']);

    $cantidad =
        intval($detalle['cantidad']);

    // PRECIO × CANTIDAD
    $subtotal =
        $precio_unitario * $cantidad;

    // Guardamos el precio calculado
    $detalle['precio_unitario'] =
        $precio_unitario;

    // Guardamos el subtotal calculado
    $detalle['subtotal'] =
        $subtotal;

    $detalles[] = $detalle;

    // Sumamos el subtotal
    $subtotal_calculado += $subtotal;
}


// ======================================================
// DESCUENTO
// ======================================================

$porcentaje_descuento =
    floatval(
        $pedido['valor_descuento'] ?? 0
    );

$descuento_calculado =
    $subtotal_calculado *
    ($porcentaje_descuento / 100);


// ======================================================
// ENVÍO
// ======================================================

$costo_envio = 0;


// ======================================================
// TOTAL
// ======================================================

$total_calculado =
    $subtotal_calculado
    - $descuento_calculado
    + $costo_envio;


// ======================================================
// AGREGAR TOTALES AL PEDIDO
// ======================================================

$pedido['subtotal'] =
    $subtotal_calculado;

$pedido['porcentaje_descuento'] =
    $porcentaje_descuento;

$pedido['descuento_calculado'] =
    $descuento_calculado;

$pedido['costo_envio'] =
    $costo_envio;

$pedido['total'] =
    $total_calculado;


// ======================================================
// RESPUESTA
// ======================================================

$datos = [
    "pedido" => $pedido,
    "detalles" => $detalles
];


if (ob_get_length()) {
    ob_clean();
}

echo json_encode(
    $datos,
    JSON_UNESCAPED_UNICODE
);


$stmtPedido->close();

$stmtDetalle->close();

$conexion->close();

exit;

?>