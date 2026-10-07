<?php

header("Content-Type: application/json; charset=UTF-8");

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "uncrustybd"
);

if ($conexion->connect_error) {
    echo json_encode([
        "error" => "Error de conexión"
    ]);
    exit;
}

$conexion->set_charset("utf8mb4");


if (!isset($_GET["idusuario"])) {
    echo json_encode([
        "error" => "No se recibió idusuario"
    ]);
    exit;
}

$idusuario = intval($_GET["idusuario"]);




$sql = "
    SELECT
        p.id_pedido,
        dp.id_servicio,
        dp.prenda,
        p.fecha_pedido,
        SUM(dp.subtotal) AS total,
        h.estrellas

    FROM pedidos p

    INNER JOIN detalle_pedido dp
        ON p.id_pedido = dp.id_pedido

    LEFT JOIN historial h
        ON h.idPedido = p.id_pedido

    WHERE p.id_cliente = ?

    AND p.estado = 'Entregado'

    GROUP BY
        p.id_pedido,
        dp.id_servicio,
        dp.prenda,
        p.fecha_pedido,
        h.estrellas

    ORDER BY p.id_pedido DESC
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "error" => "Error en la consulta: " . $conexion->error
    ]);

    exit;
}


$stmt->bind_param(
    "i",
    $idusuario
);


if (!$stmt->execute()) {

    echo json_encode([
        "error" => "Error al ejecutar: " . $stmt->error
    ]);

    exit;
}


$stmt->bind_result(
    $idPedido,
    $id_servicio,
    $prenda,
    $fecha_pedido,
    $total,
    $estrellas
);


$pedidos = [];


while ($stmt->fetch()) {

    $pedidos[] = [

        "idPedido" => $idPedido,

        "id_servicio" => $id_servicio,

        "prenda" => $prenda,

        "fecha_pedido" => $fecha_pedido,

        "total" => $total,

        "estrellas" => $estrellas ?? 0

    ];
}


echo json_encode($pedidos);


$stmt->close();
$conexion->close();

?>
