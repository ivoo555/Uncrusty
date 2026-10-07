<?php

session_start();

include("conexion.php");

header("Content-Type: application/json; charset=utf-8");


if (!isset($_SESSION["id_personal"])) {

    echo json_encode([
        "error" => "No hay un usuario iniciado."
    ]);

    exit;
}


$id_personal = intval($_SESSION["id_personal"]);


$sql = "
    SELECT
        id_personal,
        nombreCompleto,
        DNI,
        email,
        contrasena
    FROM personallavanderia
    WHERE id_personal = ?
";


$stmt = $conexion->prepare($sql);


if (!$stmt) {

    echo json_encode([
        "error" => "Error al preparar la consulta: " .
                   $conexion->error
    ]);

    exit;
}


$stmt->bind_param(
    "i",
    $id_personal
);


if (!$stmt->execute()) {

    echo json_encode([
        "error" => "Error al ejecutar la consulta: " .
                   $stmt->error
    ]);

    exit;
}


$resultado = $stmt->get_result();


if ($resultado->num_rows == 0) {

    echo json_encode([
        "error" => "No se encontró el personal."
    ]);

    exit;
}


$personal = $resultado->fetch_assoc();


$stmt->close();
$conexion->close();


echo json_encode([
    "personal" => $personal
], JSON_UNESCAPED_UNICODE);

?>
