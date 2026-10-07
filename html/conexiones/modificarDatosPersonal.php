<?php

session_start();

include("conexion.php");

header("Content-Type: application/json; charset=utf-8");


// Verificar sesión
if (!isset($_SESSION["id_personal"])) {

    echo json_encode([
        "error" => "No hay un usuario iniciado."
    ]);

    exit;
}


$id_personal = intval($_SESSION["id_personal"]);


// Recibir datos
$nombreCompleto = $_POST["nombreCompleto"] ?? "";
$DNI = $_POST["DNI"] ?? "";
$email = $_POST["email"] ?? "";
$contrasena = $_POST["contrasena"] ?? "";


// Verificar datos obligatorios
if (
    $nombreCompleto == "" ||
    $DNI == "" ||
    $email == ""
) {

    echo json_encode([
        "error" => "Complete todos los campos obligatorios."
    ]);

    exit;
}


// Si NO se ingresó una nueva contraseña
if ($contrasena == "") {

    $sql = "
        UPDATE personallavanderia
        SET
            nombreCompleto = ?,
            DNI = ?,
            email = ?
        WHERE id_personal = ?
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        echo json_encode([
            "error" => "Error al preparar la consulta: " . $conexion->error
        ]);

        exit;
    }

    $stmt->bind_param(
        "sssi",
        $nombreCompleto,
        $DNI,
        $email,
        $id_personal
    );


// Si ingresó una nueva contraseña
} else {

    $sql = "
        UPDATE personallavanderia
        SET
            nombreCompleto = ?,
            DNI = ?,
            email = ?,
            contrasena = ?
        WHERE id_personal = ?
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        echo json_encode([
            "error" => "Error al preparar la consulta: " . $conexion->error
        ]);

        exit;
    }

    $stmt->bind_param(
        "ssssi",
        $nombreCompleto,
        $DNI,
        $email,
        $contrasena,
        $id_personal
    );
}


// Ejecutar actualización
if (!$stmt->execute()) {

    echo json_encode([
        "error" => "Error al modificar los datos: " . $stmt->error
    ]);

    exit;
}


$stmt->close();
$conexion->close();


echo json_encode([
    "mensaje" => "Datos modificados correctamente."
], JSON_UNESCAPED_UNICODE);

?>