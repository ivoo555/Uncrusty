<?php

include("conexion.php");

header("Content-Type: application/json");

$id_cupon = $_POST["id_cupon"] ?? "";
$codigo = $_POST["codigo"] ?? "";
$valor_descuento = $_POST["valor_descuento"] ?? "";
$fecha_inicio = $_POST["fecha_inicio"] ?? "";
$fecha_vencimiento = $_POST["fecha_vencimiento"] ?? "";


if (
    $id_cupon == "" ||
    $codigo == "" ||
    $valor_descuento == "" ||
    $fecha_inicio == "" ||
    $fecha_vencimiento == ""
) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Todos los campos son obligatorios"
    ]);

    exit;
}


if ($valor_descuento < 0) {

    echo json_encode([
        "error" => true,
        "mensaje" => "El descuento no puede ser negativo"
    ]);

    exit;
}


if ($fecha_vencimiento < $fecha_inicio) {

    echo json_encode([
        "error" => true,
        "mensaje" => "La fecha de vencimiento no puede ser anterior a la fecha de inicio"
    ]);

    exit;
}

$sql = "UPDATE cupones
        SET codigo = ?,
            valor_descuento = ?,
            fecha_inicio = ?,
            fecha_vencimiento = ?
        WHERE id_cupon = ?";


$stmt = $conexion->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al preparar la consulta"
    ]);

    exit;
}


$stmt->bind_param(
    "sdssi",
    $codigo,
    $valor_descuento,
    $fecha_inicio,
    $fecha_vencimiento,
    $id_cupon
);


if ($stmt->execute()) {

    echo json_encode([
        "error" => false,
        "mensaje" => "Cupón modificado correctamente"
    ]);

} else {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al modificar el cupón"
    ]);

}


$stmt->close();
$conexion->close();

?>