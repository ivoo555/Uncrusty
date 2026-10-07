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
        "error" => "Error de conexion"
    ]);
    exit;
}

$conexion->set_charset("utf8mb4");



if (
    !isset($_POST["idPedido"]) ||
    !isset($_POST["estrellas"])
) {
    echo json_encode([
        "error" => "Faltan datos"
    ]);
    exit;
}

$idPedido = intval($_POST["idPedido"]);
$estrellas = intval($_POST["estrellas"]);

if ($idPedido <= 0) {
    echo json_encode([
        "error" => "Pedido invalido"
    ]);
    exit;
}



if ($estrellas < 1 || $estrellas > 5) {
    echo json_encode([
        "error" => "La calificacion debe ser entre 1 y 5"
    ]);
    exit;
}

$sqlPedido = "
    SELECT id_pedido
    FROM pedidos
    WHERE id_pedido = ?
";

$stmtPedido = $conexion->prepare($sqlPedido);

if (!$stmtPedido) {
    echo json_encode([
        "error" => "Error en la consulta del pedido: " . $conexion->error
    ]);
    exit;
}

$stmtPedido->bind_param(
    "i",
    $idPedido
);

$stmtPedido->execute();

$resultadoPedido = $stmtPedido->get_result();

if ($resultadoPedido->num_rows === 0) {

    echo json_encode([
        "error" => "El pedido no existe"
    ]);

    $stmtPedido->close();
    $conexion->close();
    exit;
}

$stmtPedido->close();



$sqlHistorial = "
    SELECT idPedido
    FROM historial
    WHERE idPedido = ?
";

$stmtHistorial = $conexion->prepare($sqlHistorial);

if (!$stmtHistorial) {
    echo json_encode([
        "error" => "Error al consultar el historial: " . $conexion->error
    ]);
    exit;
}

$stmtHistorial->bind_param(
    "i",
    $idPedido
);

$stmtHistorial->execute();

$resultadoHistorial = $stmtHistorial->get_result();


if ($resultadoHistorial->num_rows > 0) {

    $stmtHistorial->close();

    $sqlUpdate = "
        UPDATE historial
        SET estrellas = ?
        WHERE idPedido = ?
    ";

    $stmtUpdate = $conexion->prepare($sqlUpdate);

    if (!$stmtUpdate) {
        echo json_encode([
            "error" => "Error al actualizar la calificacion"
        ]);
        exit;
    }

    $stmtUpdate->bind_param(
        "ii",
        $estrellas,
        $idPedido
    );

    if ($stmtUpdate->execute()) {

        echo json_encode([
            "success" => true,
            "mensaje" => "Calificacion guardada"
        ]);

    } else {

        echo json_encode([
            "error" => "No se pudo actualizar la calificacion"
        ]);
    }

    $stmtUpdate->close();



} else {

    $stmtHistorial->close();

    $sqlInsert = "
        INSERT INTO historial
        (
            idPedido,
            estrellas
        )
        VALUES (?, ?)
    ";

    $stmtInsert = $conexion->prepare($sqlInsert);

    if (!$stmtInsert) {
        echo json_encode([
            "error" => "Error al crear el historial: " . $conexion->error
        ]);
        exit;
    }

    $stmtInsert->bind_param(
        "ii",
        $idPedido,
        $estrellas
    );

    if ($stmtInsert->execute()) {

        echo json_encode([
            "success" => true,
            "mensaje" => "Calificacion guardada"
        ]);

    } else {

        echo json_encode([
            "error" => "No se pudo guardar la calificacion"
        ]);
    }

    $stmtInsert->close();
}


$conexion->close();

?>
