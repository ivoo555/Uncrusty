<?php

include("conexion.php");

$sql = "SELECT *
        FROM cupones
        WHERE estado = 'Activo'";

$resultado = mysqli_query($conexion, $sql);

$cupones = array();

while ($cupon = mysqli_fetch_assoc($resultado)) {
    $cupones[] = $cupon;
}

header("Content-Type: application/json");

echo json_encode($cupones);

mysqli_close($conexion);

?>