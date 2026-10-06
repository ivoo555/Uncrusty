<?php

include("conexion.php");

header("Content-Type: application/json");

$id_servicio = $_POST["id_servicio"] ?? "";
$nombre = $_POST["nombre"] ?? "";
$descripcion = $_POST["descripcion"] ?? "";
$categoria = $_POST["categoria"] ?? "";
$precio = $_POST["precio"] ?? "";

if (
    $id_servicio == "" ||
    $nombre == "" ||
    $categoria == "" ||
    $precio == ""
) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Faltan datos obligatorios"
    ]);
    exit;
}

$sql = "UPDATE servicios
        SET nombre = ?,
            descripcion = ?,
            categoria = ?,
            precio = ?
        WHERE id_servicio = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Error al preparar la consulta: " . $conexion->error
    ]);
    exit;
}

$stmt->bind_param(
    "sssdi",
    $nombre,
    $descripcion,
    $categoria,
    $precio,
    $id_servicio
);

if ($stmt->execute()) {

    echo json_encode([
        "error" => false,
        "mensaje" => "Servicio modificado correctamente"
    ]);

} else {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al modificar el servicio: " . $stmt->error
    ]);

}

$stmt->close();
$conexion->close();

?>