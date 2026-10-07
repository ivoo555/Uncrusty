<?php

include("conexion.php");

header("Content-Type: application/json");

$id_producto = $_POST["id_producto"] ?? "";
$marca = $_POST["marca"] ?? "";
$cantidad_actual = $_POST["cantidad_actual"] ?? "";
$cantidad_minima = $_POST["cantidad_minima"] ?? "";

if (
    $id_producto == "" ||
    $cantidad_actual == "" ||
    $cantidad_minima == ""
) {
    echo json_encode([
        "error" => "Faltan datos obligatorios."
    ]);
    exit;
}

$id_producto = intval($id_producto);
$cantidad_actual = intval($cantidad_actual);
$cantidad_minima = intval($cantidad_minima);

$sql = "
    UPDATE stock
    SET
        marca = ?,
        cantidad_actual = ?,
        cantidad_minima = ?
    WHERE id_producto = ?
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "siii",
    $marca,
    $cantidad_actual,
    $cantidad_minima,
    $id_producto
);

if (!$stmt->execute()) {

    echo json_encode([
        "error" => "Error al modificar el producto: " . $stmt->error
    ]);

    exit;
}

$stmt->close();

echo json_encode([
    "mensaje" => "Producto modificado correctamente."
]);

mysqli_close($conexion);

?>