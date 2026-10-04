<?php

include("conexion.php");

$sqlServicios = "SELECT id_servicio, nombre,tipo,precio,estado FROM servicios
                 ORDER BY id_servicio DESC";


$servicios = mysqli_query(
    $conexion,
    $sqlServicios
);




$datos = array();

while ($servicio = mysqli_fetch_assoc($servicios)) {

    $datos[] = $servicio;

}



header("Content-Type: application/json");

echo json_encode($datos);

$conexion->close();

?>