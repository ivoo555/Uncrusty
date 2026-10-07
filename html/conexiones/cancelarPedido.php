<?php

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");

$id_pedido = $_POST["id_pedido"] ?? "";

if ($id_pedido == "") {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se especificó el pedido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$id_pedido = intval($id_pedido);

$conexion->begin_transaction();

try {

    $sqlBuscar = "
        SELECT id_pedido
        FROM pedidos
        WHERE id_pedido = ?
    ";

    $stmtBuscar = $conexion->prepare($sqlBuscar);

    if (!$stmtBuscar) {
        throw new Exception("Error al preparar la consulta.");
    }

    $stmtBuscar->bind_param("i", $id_pedido);
    $stmtBuscar->execute();

    $resultado = $stmtBuscar->get_result();

    if ($resultado->num_rows == 0) {

        $stmtBuscar->close();

        throw new Exception("El pedido no existe.");
    }

    $stmtBuscar->close();


    $sqlReparto = "
        DELETE FROM repartos
        WHERE id_pedido = ?
    ";

    $stmtReparto = $conexion->prepare($sqlReparto);

    if (!$stmtReparto) {
        throw new Exception(
            "No se pudo preparar la eliminación del reparto."
        );
    }

    $stmtReparto->bind_param("i", $id_pedido);

    if (!$stmtReparto->execute()) {

        $stmtReparto->close();

        throw new Exception(
            "No se pudo eliminar el reparto del pedido."
        );
    }

    $stmtReparto->close();

    $sqlDetalle = "
        DELETE FROM detalle_pedido
        WHERE id_pedido = ?
    ";

    $stmtDetalle = $conexion->prepare($sqlDetalle);

    if (!$stmtDetalle) {
        throw new Exception(
            "No se pudo preparar la eliminación de los detalles."
        );
    }

    $stmtDetalle->bind_param("i", $id_pedido);

    if (!$stmtDetalle->execute()) {

        $stmtDetalle->close();

        throw new Exception(
            "No se pudieron eliminar los detalles del pedido."
        );
    }

    $stmtDetalle->close();

    $sqlFactura = "
        DELETE FROM facturas
        WHERE id_pedido = ?
    ";

    $stmtFactura = $conexion->prepare($sqlFactura);

    if (!$stmtFactura) {
        throw new Exception(
            "No se pudo preparar la eliminación de la factura."
        );
    }

    $stmtFactura->bind_param("i", $id_pedido);

    if (!$stmtFactura->execute()) {

        $stmtFactura->close();

        throw new Exception(
            "No se pudo eliminar la factura del pedido."
        );
    }

    $stmtFactura->close();

    $sqlPedido = "
        DELETE FROM pedidos
        WHERE id_pedido = ?
    ";

    $stmtPedido = $conexion->prepare($sqlPedido);

    if (!$stmtPedido) {
        throw new Exception(
            "No se pudo preparar la eliminación del pedido."
        );
    }

    $stmtPedido->bind_param("i", $id_pedido);

    if (!$stmtPedido->execute()) {

        $stmtPedido->close();

        throw new Exception(
            "No se pudo eliminar el pedido."
        );
    }

    $stmtPedido->close();


    $conexion->commit();

    echo json_encode([
        "error" => false,
        "mensaje" => "El pedido se eliminó correctamente.",
        "id_pedido" => $id_pedido
    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $e) {

    $conexion->rollback();

    echo json_encode([
        "error" => true,
        "mensaje" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}


$conexion->close();

?>