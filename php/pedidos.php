<?php

header("Content-Type: application/json; charset=UTF-8");

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexion"
    ]);
    exit;
}

$id_cliente = $_GET["idusuario"] ?? null;

if (!$id_cliente) {
    session_start();

    $id_cliente = $_SESSION["id"] ?? null;

    if (!$id_cliente) {
        $id_cliente = $_SESSION["idusuario"] ?? null;
    }
}

if (!$id_cliente) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Usuario no encontrado"
    ]);
    exit;
}

$id_cliente = intval($id_cliente);

$sql = "SELECT
            p.id_pedido,
            p.fecha_pedido,
            p.direccion_entrega,
            p.estado,
            p.observaciones,
            p.id_servicio,
            r.fecha_programada,
            r.direccion AS direccion_reparto,
            r.estado AS estado_reparto
        FROM pedidos p
        LEFT JOIN repartos r
            ON p.id_pedido = r.id_pedido
        WHERE p.id_cliente = ?
        AND p.estado <> 'Entregado'
        ORDER BY p.id_pedido DESC";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error en la consulta: " . $conexion->error
    ]);
    exit;
}

$stmt->bind_param("i", $id_cliente);

if (!$stmt->execute()) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al ejecutar: " . $stmt->error
    ]);
    exit;
}

$resultado = $stmt->get_result();

$pedidos = [];

while ($fila = $resultado->fetch_assoc()) {
    $pedidos[] = $fila;
}

echo json_encode([
    "ok" => true,
    "pedidos" => $pedidos
]);

$stmt->close();
$conexion->close();

?>
