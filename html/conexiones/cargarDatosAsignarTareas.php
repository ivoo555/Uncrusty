<?php

include("conexion.php");


$sqlRepartidores = "SELECT * FROM repartidores
                    WHERE disponibilidad = 'Disponible'";

$repartidoresDisponibles = mysqli_query($conexion, $sqlRepartidores);


$sqlPedidos = "SELECT * FROM pedidos
               WHERE estado = 'Pendiente' OR estado = 'Listo para entrega'";

$pedidos = mysqli_query($conexion, $sqlPedidos);


$datos = array(
    "repartidores" => array(),
    "pedidos" => array()
);



while ($repartidor = mysqli_fetch_assoc($repartidoresDisponibles)) {

    $datos["repartidores"][] = $repartidor;

}


while ($pedido = mysqli_fetch_assoc($pedidos)) {

    $datos["pedidos"][] = $pedido;

}


header("Content-Type: application/json");

echo json_encode($datos);

?>