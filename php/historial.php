<?php

header("Content-Type: application/json; charset=UTF-8");

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {
    echo json_encode(["error" => "Error de conexion"]);
    exit;
}

if (!isset($_GET["idusuario"])) {
    echo json_encode(["error" => "No se recibio idusuario"]);
    exit;
}

$idusuario = intval($_GET["idusuario"]);

$sql = "SELECT
            h.idPedido,
            p.id_servicio,
            p.fecha_pedido,
            p.total,
            h.estrellas
        FROM historial h
        INNER JOIN pedidos p
            ON h.idPedido = p.id_pedido
        WHERE p.id_cliente = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "error" => "Error en la consulta: " . $conexion->error
    ]);
    exit;
}

$stmt->bind_param("i", $idusuario);

if (!$stmt->execute()) {
    echo json_encode([
        "error" => "Error al ejecutar: " . $stmt->error
    ]);
    exit;
}

$stmt->bind_result(
    $idPedido,
    $id_servicio,
    $fecha_pedido,
    $total,
    $estrellas
);

$pedidos = [];

while ($stmt->fetch()) {
    $pedidos[] = [
        "idPedido" => $idPedido,
        "id_servicio" => $id_servicio,
        "fecha_pedido" => $fecha_pedido,
        "total" => $total,
        "estrellas" => $estrellas
    ];
}

echo json_encode($pedidos);

$stmt->close();
$conexion->close();

?>
