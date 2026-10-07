<?php

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");

$id_pedido = $_GET["id_pedido"] ?? "";

if ($id_pedido == "") {
    echo json_encode([
        "error" => true,
        "mensaje" => "No se especificó el pedido."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$id_pedido = intval($id_pedido);

$sql = "
    SELECT id_pedido, estado
    FROM pedidos
    WHERE id_pedido = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {

    echo json_encode([
        "error" => true,
        "mensaje" => "El pedido no existe."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$pedido = $resultado->fetch_assoc();

echo json_encode([
    "error" => false,
    "id_pedido" => $pedido["id_pedido"],
    "estado" => $pedido["estado"]
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conexion->close();

?>