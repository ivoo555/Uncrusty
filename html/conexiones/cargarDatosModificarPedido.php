<?php

include("conexion.php");

header("Content-Type: application/json");

$id_pedido = $_GET["id_pedido"] ?? "";

if ($id_pedido == "") {
    echo json_encode([
        "error" => "No se recibió el ID del pedido."
    ]);
    exit;
}

$id_pedido = intval($id_pedido);

$sqlPedido = "
    SELECT
        p.id_pedido,
        p.id_servicio,
        p.direccion,
        p.descuento,
        p.observaciones,
        dp.id_detalle,
        dp.prenda,
        dp.cantidad
    FROM pedidos p
    LEFT JOIN detalle_pedido dp
        ON p.id_pedido = dp.id_pedido
    WHERE p.id_pedido = ?
    ORDER BY dp.id_detalle
    LIMIT 1
";

$stmtPedido = $conexion->prepare($sqlPedido);

$stmtPedido->bind_param(
    "i",
    $id_pedido
);

$stmtPedido->execute();

$resultadoPedido = $stmtPedido->get_result();

if ($resultadoPedido->num_rows == 0) {

    echo json_encode([
        "error" => "No se encontró el pedido."
    ]);

    exit;
}

$pedido = $resultadoPedido->fetch_assoc();

$stmtPedido->close();


$sqlServicios = "
    SELECT
        id_servicio,
        nombre
    FROM servicios
    WHERE estado = 'Activo'
    ORDER BY nombre
";

$resultadoServicios = mysqli_query(
    $conexion,
    $sqlServicios
);

$servicios = [];

while ($servicio = mysqli_fetch_assoc($resultadoServicios)) {
    $servicios[] = $servicio;
}


echo json_encode([
    "pedido" => $pedido,
    "servicios" => $servicios
], JSON_UNESCAPED_UNICODE);

mysqli_close($conexion);

?>