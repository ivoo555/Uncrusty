<?php

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");


$id_pedido = $_POST["id_pedido"] ?? "";
$estado = $_POST["estado"] ?? "";


if ($id_pedido == "" || $estado == "") {

    echo json_encode([
        "error" => true,
        "mensaje" => "Faltan datos del pedido."
    ]);

    exit;
}


$id_pedido = intval($id_pedido);

$estadosPermitidos = [
    "Pendiente",
    "En Recolección",
    "En Limpieza",
    "Listo para entrega",
    "Entregado"
];


if (!in_array($estado, $estadosPermitidos)) {

    echo json_encode([
        "error" => true,
        "mensaje" => "El estado seleccionado no es válido."
    ]);

    exit;
}

$sqlBuscar = "
    SELECT id_pedido, estado
    FROM pedidos
    WHERE id_pedido = ?
";


$stmtBuscar =
    $conexion->prepare($sqlBuscar);

$stmtBuscar->bind_param(
    "i",
    $id_pedido
);

$stmtBuscar->execute();

$resultado =
    $stmtBuscar->get_result();


if ($resultado->num_rows == 0) {

    echo json_encode([
        "error" => true,
        "mensaje" => "El pedido no existe."
    ]);

    exit;
}


$stmtBuscar->close();


$sqlActualizar = "
    UPDATE pedidos
    SET estado = ?
    WHERE id_pedido = ?
";


$stmtActualizar =
    $conexion->prepare($sqlActualizar);

$stmtActualizar->bind_param(
    "si",
    $estado,
    $id_pedido
);


if ($stmtActualizar->execute()) {

    echo json_encode([
        "error" => false,
        "mensaje" => "Estado del pedido actualizado correctamente.",
        "id_pedido" => $id_pedido,
        "estado" => $estado
    ]);

} else {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se pudo actualizar el estado del pedido."
    ]);

}


$stmtActualizar->close();

$conexion->close();

?>