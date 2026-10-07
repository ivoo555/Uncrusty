<?php

include("conexion.php");

header("Content-Type: application/json; charset=utf-8");

$id_pedido = $_POST["id_pedido"] ?? "";
$id_detalle = $_POST["id_detalle"] ?? "";
$id_servicio = $_POST["id_servicio"] ?? "";
$prenda = $_POST["prenda"] ?? "";
$cantidad = $_POST["cantidad"] ?? "";
$direccion = $_POST["direccion"] ?? "";
$observaciones = $_POST["observaciones"] ?? "";

if (
    $id_pedido == "" ||
    $id_detalle == "" ||
    $id_servicio == "" ||
    $prenda == "" ||
    $cantidad == "" ||
    $direccion == ""
) {
    echo json_encode([
        "error" => "Faltan datos obligatorios."
    ]);
    exit;
}

$id_pedido = intval($id_pedido);
$id_detalle = intval($id_detalle);
$id_servicio = intval($id_servicio);
$cantidad = intval($cantidad);

$sqlPrecio = "
    SELECT precio
    FROM servicios
    WHERE id_servicio = ?
";

$stmtPrecio = $conexion->prepare($sqlPrecio);

if (!$stmtPrecio) {
    echo json_encode([
        "error" => "Error al preparar la consulta del precio."
    ]);
    exit;
}

$stmtPrecio->bind_param(
    "i",
    $id_servicio
);

$stmtPrecio->execute();

$resultadoPrecio = $stmtPrecio->get_result();

if ($resultadoPrecio->num_rows == 0) {
    echo json_encode([
        "error" => "No se encontró el servicio."
    ]);
    exit;
}

$servicioDatos = $resultadoPrecio->fetch_assoc();

$precioUnitario = floatval($servicioDatos["precio"]);

$stmtPrecio->close();

$subtotal = $precioUnitario * $cantidad;

$conexion->begin_transaction();

$sqlPedido = "
    UPDATE pedidos
    SET
        id_servicio = ?,
        direccion = ?,
        observaciones = ?
    WHERE id_pedido = ?
";

$stmtPedido = $conexion->prepare($sqlPedido);

if (!$stmtPedido) {
    $conexion->rollback();

    echo json_encode([
        "error" => "Error al preparar la modificación del pedido."
    ]);
    exit;
}

$stmtPedido->bind_param(
    "issi",
    $id_servicio,
    $direccion,
    $observaciones,
    $id_pedido
);

if (!$stmtPedido->execute()) {
    $conexion->rollback();

    echo json_encode([
        "error" => "Error al modificar el pedido."
    ]);
    exit;
}

$stmtPedido->close();

$sqlDetalle = "
    UPDATE detalle_pedido
    SET
        id_servicio = ?,
        prenda = ?,
        cantidad = ?,
        precio_unitario = ?,
        subtotal = ?
    WHERE id_detalle = ?
    AND id_pedido = ?
";

$stmtDetalle = $conexion->prepare($sqlDetalle);

if (!$stmtDetalle) {
    $conexion->rollback();

    echo json_encode([
        "error" => "Error al preparar la modificación del detalle."
    ]);
    exit;
}

$stmtDetalle->bind_param(
    "isiddii",
    $id_servicio,
    $prenda,
    $cantidad,
    $precioUnitario,
    $subtotal,
    $id_detalle,
    $id_pedido
);

if (!$stmtDetalle->execute()) {
    $conexion->rollback();

    echo json_encode([
        "error" => "Error al modificar el detalle del pedido."
    ]);
    exit;
}

$stmtDetalle->close();

$conexion->commit();

echo json_encode([
    "mensaje" => "Pedido modificado correctamente."
], JSON_UNESCAPED_UNICODE);

mysqli_close($conexion);

?>