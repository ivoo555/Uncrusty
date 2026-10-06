<?php

include("conexion.php");


$sqlCupones = "SELECT *
        FROM cupones
        WHERE estado = 'Activo'";

$cupones = mysqli_query(
    $conexion,
    $sqlCupones
);

$datos = array();

while ($cupon = mysqli_fetch_assoc($cupones)) {

    $datos[] = $cupon;

}

header("Content-Type: application/json");

echo json_encode($datos);

?>