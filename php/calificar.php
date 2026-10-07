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
        "error" => "Error de conexion"
    ]);
    exit;
}

if (!isset($_POST["idPedido"]) || !isset($_POST["estrellas"])) {
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

$sql = "UPDATE historial
        SET estrellas = ?
        WHERE idPedido = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "error" => "Error en la consulta: " . $conexion->error
    ]);
    exit;
}

$stmt->bind_param("ii", $estrellas, $idPedido);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {

        echo json_encode([
            "success" => true,
            "mensaje" => "Calificacion guardada"
        ]);

    } else {

        echo json_encode([
            "error" => "No se encontro el pedido"
        ]);

    }

} else {

    echo json_encode([
        "error" => "No se pudo guardar la calificacion"
    ]);

}

$stmt->close();
$conexion->close();

?>
