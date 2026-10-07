<?php

include("conexion.php");

$id_cupon = $_POST["id_cupon"] ?? "";

if ($id_cupon == "") {

    echo "ID de cupón no válido.";

    exit;

}


$sql = "UPDATE cupones
        SET estado = 'Inactivo'
        WHERE id_cupon = ?";


$stmt = $conexion->prepare($sql);


$stmt->bind_param("i",$id_cupon);




if ($stmt->execute()) {

    echo "Cupón desactivado correctamente.";

} else {

    echo "Error al desactivar el cupón.";

}


$stmt->close();

$conexion->close();

?>