<?php

include("conexion.php");

header("Content-Type: application/json");

$sqlRepartidores = "
    SELECT
        id_repartidor,
        nombreCompleto
    FROM repartidores
";

$repartidores = mysqli_query($conexion, $sqlRepartidores);

if (!$repartidores) {
    echo json_encode([
        "error" => "Error en repartidores: " . mysqli_error($conexion)
    ]);
    exit;
}

$sqlPedidos = "
    SELECT
        pedidos.id_pedido,
        clientes.nombreCompleto AS cliente,
        servicios.nombre AS servicio,
        GROUP_CONCAT(
            DISTINCT detalle_pedido.prenda
            SEPARATOR ', '
        ) AS prenda,
        pedidos.estado,
        pedidos.descuento,
        pedidos.observaciones,
        pedidos.direccion,
        pedidos.fecha_pedido,
        MAX(repartidores.nombreCompleto) AS repartidor

    FROM pedidos

    INNER JOIN clientes
        ON pedidos.id_cliente = clientes.id_cliente

    LEFT JOIN servicios
        ON pedidos.id_servicio = servicios.id_servicio

    LEFT JOIN detalle_pedido
        ON pedidos.id_pedido = detalle_pedido.id_pedido

    LEFT JOIN repartos
        ON pedidos.id_pedido = repartos.id_pedido
        AND repartos.tipo = 'Entrega'

    LEFT JOIN repartidores
        ON repartos.id_repartidor = repartidores.id_repartidor

    GROUP BY
        pedidos.id_pedido,
        clientes.nombreCompleto,
        servicios.nombre,
        pedidos.estado,
        pedidos.descuento,
        pedidos.observaciones,
        pedidos.direccion,
        pedidos.fecha_pedido

    ORDER BY pedidos.id_pedido DESC
";

$pedidos = mysqli_query($conexion, $sqlPedidos);

if (!$pedidos) {
    echo json_encode(["error" => "Error en pedidos: " . mysqli_error($conexion)]);
    exit;
}

$datos = [
    "repartidores" => [],
    "pedidos" => []
];

while ($repartidor = mysqli_fetch_assoc($repartidores)) {
    $datos["repartidores"][] = $repartidor;
}

while ($pedido = mysqli_fetch_assoc($pedidos)) {
    $datos["pedidos"][] = $pedido;
}

echo json_encode($datos);

mysqli_close($conexion);

?>