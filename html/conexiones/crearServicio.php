<?php

include("conexion.php");

$nombre = $_POST["nombre"] ?? "";
$categoria = $_POST["categoria"] ?? "";
$precio = $_POST["precio"] ?? "";

if ($nombre == "" || $categoria == "" || $precio == "")
{

    echo "Faltan datos.";

    exit;

}

$nombre = trim($nombre);
$categoria = trim($categoria);
$precio = floatval($precio);


if ($precio < 0) {

    echo "El precio no puede ser negativo.";

    exit;

}

$sqlExiste = "SELECT id_servicio  FROM servicios
              WHERE nombre = ? AND estado = 'Activo'";

$stmtExiste = $conexion->prepare($sqlExiste);

$stmtExiste->bind_param("s",$nombre);

$stmtExiste->execute();

$resultado = $stmtExiste->get_result();


if ($resultado->num_rows > 0) {

    echo "Ya existe un servicio activo con ese nombre.";

    $stmtExiste->close();
    $conexion->close();

    exit;

}

$stmtExiste->close();


$sql = "INSERT INTO servicios
        (
            nombre,
            descripcion,
            categoria,
            precio,
            estado
        )
        VALUES (?, NULL, ?, ?, 'Activo')";


$stmt = $conexion->prepare($sql);


$stmt->bind_param(
    "ssd",
    $nombre,
    $categoria,
    $precio
);


if ($stmt->execute()) {

    echo "Servicio creado correctamente.";

} else {

    echo "Error al crear el servicio: ". $conexion->error;
}

$stmt->close();

$conexion->close();

?>