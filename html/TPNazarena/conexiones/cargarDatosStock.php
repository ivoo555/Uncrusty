
<?php

include("conexion.php");


$sqlProductos = "SELECT * FROM stock ORDER BY id_producto DESC";

$productos = mysqli_query($conexion,$sqlProductos);

$datos = array();

while ($producto = mysqli_fetch_assoc($productos)) {

    $datos[] = $producto;

}


header("Content-Type: application/json");

echo json_encode($datos);

?>

