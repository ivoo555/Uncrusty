<?php

header("Content-Type: application/json; charset=UTF-8");

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {
    echo json_encode([
        "error" => "Error de conexion a la base de datos"
    ]);
    exit;
}

$sql = "SELECT id_servicio, nombre, descripcion, categoria, precio
        FROM servicio
        WHERE estado = 'Activo'";

$resultado = $conexion->query($sql);

if (!$resultado) {
    echo json_encode([
        "error" => "Error en la consulta: " . $conexion->error
    ]);
    exit;
}

$servicios = [];

while ($fila = $resultado->fetch_assoc()) {
    $servicios[] = [
        "id" => $fila["id_servicio"],
        "nombre" => $fila["nombre"],
        "descripcion" => $fila["descripcion"],
        "categoria" => $fila["categoria"],
        "precio" => $fila["precio"]
    ];
}

echo json_encode($servicios);

$conexion->close();

?>
