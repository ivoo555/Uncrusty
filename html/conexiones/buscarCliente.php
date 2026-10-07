<?php

include("conexion.php");

header("Content-Type: application/json; charset=utf-8");

mysqli_set_charset($conexion, "utf8mb4");

if (!isset($_GET["nombreCompleto"])) {

    echo json_encode([
        "error" => "No se recibió el nombre."
    ]);

    exit;
}

$nombreCompleto = trim($_GET["nombreCompleto"]);



$sqlCliente = "
    SELECT
        id_cliente,
        DNI,
        email,
        nombreCompleto
    FROM clientes
    WHERE nombreCompleto = ?
";

$stmtCliente = $conexion->prepare($sqlCliente);

if (!$stmtCliente) {

    echo json_encode([
        "error" => "Error al preparar la consulta del cliente."
    ]);

    exit;
}

$stmtCliente->bind_param(
    "s",
    $nombreCompleto
);

$stmtCliente->execute();

$resultadoCliente = $stmtCliente->get_result();

$cliente = $resultadoCliente->fetch_assoc();

$stmtCliente->close();

if (!$cliente) {

    echo json_encode([
        "error" => "No se encontró ningún cliente con ese nombre."
    ]);

    exit;
}



$sqlPedidos = "
    SELECT
        p.id_pedido,
        p.fecha_pedido,
        p.estado,
        p.direccion,
        p.id_descuento,
        p.observaciones,

        COALESCE(cu.valor_descuento, 0)
        AS porcentaje_descuento

    FROM pedidos p

    LEFT JOIN cupones cu
        ON p.id_descuento = cu.id_cupon

    WHERE p.id_cliente = ?

    ORDER BY p.fecha_pedido DESC
";

$stmtPedidos = $conexion->prepare($sqlPedidos);

if (!$stmtPedidos) {

    echo json_encode([
        "error" => "Error al preparar la consulta de pedidos."
    ]);

    exit;
}

$stmtPedidos->bind_param(
    "i",
    $cliente["id_cliente"]
);

$stmtPedidos->execute();

$resultadoPedidos = $stmtPedidos->get_result();

$pedidos = [];



while ($pedido = $resultadoPedidos->fetch_assoc()) {

    $idPedido = $pedido["id_pedido"];

    $detalles = [];

    $subtotalPedido = 0;



    $sqlDetalles = "
        SELECT
            dp.prenda,
            dp.cantidad,

            s.nombre AS servicio,
            s.precio AS precio_unitario

        FROM detalle_pedido dp

        INNER JOIN servicios s
            ON dp.id_servicio = s.id_servicio

        WHERE dp.id_pedido = ?
    ";

    $stmtDetalle = $conexion->prepare($sqlDetalles);

    if (!$stmtDetalle) {

        continue;
    }

    $stmtDetalle->bind_param(
        "i",
        $idPedido
    );

    $stmtDetalle->execute();

    $resultadoDetalles = $stmtDetalle->get_result();



    while ($detalle = $resultadoDetalles->fetch_assoc()) {

        $cantidad = intval(
            $detalle["cantidad"]
        );

        $precioUnitario = floatval(
            $detalle["precio_unitario"]
        );



        $subtotalDetalle =
            $precioUnitario * $cantidad;


    
        $subtotalPedido +=
            $subtotalDetalle;


        $detalles[] = [

            "prenda" =>
                $detalle["prenda"],

            "servicio" =>
                $detalle["servicio"],

            "cantidad" =>
                $cantidad,

            "precio_unitario" =>
                $precioUnitario,

            "subtotal" =>
                $subtotalDetalle
        ];
    }

    $stmtDetalle->close();



    $porcentajeDescuento = floatval(
        $pedido["porcentaje_descuento"]
    );


    $descuentoCalculado =
        $subtotalPedido *
        ($porcentajeDescuento / 100);



    $totalPedido =
        $subtotalPedido -
        $descuentoCalculado;



    $pedidos[] = [

        "id_pedido" =>
            $pedido["id_pedido"],

        "fecha_pedido" =>
            $pedido["fecha_pedido"],

        "estado" =>
            $pedido["estado"],

        "direccion" =>
            $pedido["direccion"],

        "id_descuento" =>
            $pedido["id_descuento"],

        "porcentaje_descuento" =>
            $porcentajeDescuento,

        "observaciones" =>
            $pedido["observaciones"],

        "subtotal" =>
            $subtotalPedido,

        "descuento_calculado" =>
            $descuentoCalculado,

        "total" =>
            $totalPedido,

        "detalles" =>
            $detalles
    ];
}

$stmtPedidos->close();



echo json_encode([

    "cliente" => [

        "id_cliente" =>
            $cliente["id_cliente"],

        "nombreCompleto" =>
            $cliente["nombreCompleto"],

        "DNI" =>
            $cliente["DNI"],

        "email" =>
            $cliente["email"]
    ],

    "pedidos" =>
        $pedidos,

    "pedidos_totales" =>
        count($pedidos)

], JSON_UNESCAPED_UNICODE);


$conexion->close();

?>
