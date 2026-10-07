<?php

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");

$producto = trim($_POST["producto"] ?? "");
$marca = trim($_POST["marca"] ?? "");
$cantidad_actual = $_POST["cantidad_actual"] ?? "";
$cantidad_minima = $_POST["cantidad_minima"] ?? "";

if (
    $producto == "" ||
    $marca == "" ||
    $cantidad_actual == "" ||
    $cantidad_minima == ""
) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Todos los campos son obligatorios."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$cantidad_actual = intval($cantidad_actual);
$cantidad_minima = intval($cantidad_minima);

if ($cantidad_actual < 0 || $cantidad_minima < 0) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Las cantidades no pueden ser negativas."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$sql = "
    INSERT INTO stock (
        producto,
        marca,
        cantidad_actual,
        cantidad_minima
    )
    VALUES (?, ?, ?, ?)
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al preparar la consulta: " . $conexion->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmt->bind_param(
    "ssii",
    $producto,
    $marca,
    $cantidad_actual,
    $cantidad_minima
);

if ($stmt->execute()) {

    echo json_encode([
        "error" => false,
        "mensaje" => "Producto agregado correctamente.",
        "id_producto" => $conexion->insert_id
    ], JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se pudo agregar el producto: " . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conexion->close();

?>