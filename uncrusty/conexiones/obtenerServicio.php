<?php

include("conexion.php");

header("Content-Type: application/json");

$id_servicio = $_GET["id"] ?? "";

if ($id_servicio == "") {
    echo json_encode([
        "error" => true,
        "mensaje" => "No se recibió el ID del servicio"
    ]);
    exit;
}

$sql = "SELECT
            id_servicio,
            nombre,
            descripcion,
            categoria,
            precio
        FROM servicios
        WHERE id_servicio = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Error al preparar la consulta: " . $conexion->error
    ]);
    exit;
}

$stmt->bind_param("i", $id_servicio);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    echo json_encode([
        "error" => true,
        "mensaje" => "No se encontró el servicio"
    ]);
    exit;
}

$servicio = $resultado->fetch_assoc();

echo json_encode($servicio);

$stmt->close();
$conexion->close();

?>