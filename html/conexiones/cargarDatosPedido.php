<?php

include("conexion.php");

header("Content-Type: application/json; charset=utf-8");

mysqli_set_charset($conexion, "utf8mb4");


// ==========================================
// OBTENER REPARTIDORES
// ==========================================

$sqlRepartidores = "
    SELECT
        id_repartidor,
        nombreCompleto
    FROM repartidores
";

$repartidores = mysqli_query(
    $conexion,
    $sqlRepartidores
);

if (!$repartidores) {

    echo json_encode([
        "error" => "Error en repartidores: " . mysqli_error($conexion)
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ==========================================
// OBTENER PEDIDOS
// ==========================================

$sqlPedidos = "
    SELECT
        pedidos.id_pedido,

        COALESCE(clientes.nombreCompleto, 'Sin cliente') AS cliente,

        GROUP_CONCAT(
            DISTINCT servicios.nombre
            SEPARATOR ', '
        ) AS servicio,


        pedidos.estado,
        pedidos.id_descuento,
        cupones.codigo AS codigo_cupon,
        cupones.valor_descuento AS porcentaje_descuento,
        pedidos.observaciones,
        pedidos.direccion,
        pedidos.fecha_pedido,

        MAX(repartidores.nombreCompleto) AS repartidor

    FROM pedidos

    LEFT JOIN clientes
        ON pedidos.id_cliente = clientes.id_cliente

    LEFT JOIN detalle_pedido
        ON pedidos.id_pedido = detalle_pedido.id_pedido

    LEFT JOIN servicios
        ON detalle_pedido.id_servicio = servicios.id_servicio

    LEFT JOIN cupones
        ON pedidos.id_descuento = cupones.id_cupon

    LEFT JOIN repartos
        ON pedidos.id_pedido = repartos.id_pedido
        AND repartos.tipo = 'Entrega'

    LEFT JOIN repartidores
        ON repartos.id_repartidor = repartidores.id_repartidor

    GROUP BY
        pedidos.id_pedido,
        clientes.nombreCompleto,
        pedidos.estado,
        pedidos.id_descuento,
        cupones.codigo,
        cupones.valor_descuento,
        pedidos.observaciones,
        pedidos.direccion,
        pedidos.fecha_pedido

    ORDER BY pedidos.id_pedido DESC
";


$pedidos = mysqli_query(
    $conexion,
    $sqlPedidos
);

if (!$pedidos) {

    echo json_encode([
        "error" => "Error en pedidos: " . mysqli_error($conexion)
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ==========================================
// ARMAR RESPUESTA
// ==========================================

$datos = [
    "repartidores" => [],
    "pedidos" => []
];


// Repartidores

while (
    $repartidor = mysqli_fetch_assoc($repartidores)
) {

    $datos["repartidores"][] = $repartidor;
}


// Pedidos

while (
    $pedido = mysqli_fetch_assoc($pedidos)
) {

    // Si no tiene servicio
    if (
        empty($pedido["servicio"])
    ) {

        $pedido["servicio"] =
            "Sin servicio";
    }




    // Si no tiene repartidor
    if (
        empty($pedido["repartidor"])
    ) {

        $pedido["repartidor"] =
            "Sin asignar";
    }


    $datos["pedidos"][] =
        $pedido;
}


// ==========================================
// RESPUESTA
// ==========================================

echo json_encode(
    $datos,
    JSON_UNESCAPED_UNICODE
);


mysqli_close($conexion);

?>