<?php

include("conexion.php");

header("Content-Type: application/json");

$id_producto = $_GET["id_producto"] ?? "";

if ($id_producto == "") {
    echo json_encode([
        "error" => "No se recibió el ID del producto."
    ]);
    exit;
}

$id_producto = intval($id_producto);

$sql = "
    SELECT
        id_producto,
        marca,
        cantidad_actual,
        cantidad_minima
    FROM stock
    WHERE id_producto = ?
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id_producto
);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {

    echo json_encode([
        "error" => "No se encontró el producto."
    ]);

    exit;
}

$producto = $resultado->fetch_assoc();

$stmt->close();

echo json_encode([
    "producto" => $producto
], JSON_UNESCAPED_UNICODE);

mysqli_close($conexion);

?>