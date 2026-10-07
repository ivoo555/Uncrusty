<?php

include("conexion.php");

header("Content-Type: application/json");

$id_cupon = $_GET["id"] ?? "";

if ($id_cupon == "") {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se recibió el ID del cupón"
    ]);

    exit;
}

$sql = "SELECT 
            id_cupon,
            codigo,
            valor_descuento,
            fecha_inicio,
            fecha_vencimiento
        FROM cupones
        WHERE id_cupon = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error en la consulta"
    ]);

    exit;
}

$stmt->bind_param("i", $id_cupon);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {

    echo json_encode([
        "error" => true,
        "mensaje" => "El cupón no existe"
    ]);

    exit;
}

$cupon = $resultado->fetch_assoc();

echo json_encode([
    "error" => false,
    "id_cupon" => $cupon["id_cupon"],
    "codigo" => $cupon["codigo"],
    "valor_descuento" => $cupon["valor_descuento"],
    "fecha_inicio" => $cupon["fecha_inicio"],
    "fecha_vencimiento" => $cupon["fecha_vencimiento"]
]);

$stmt->close();
$conexion->close();

?>