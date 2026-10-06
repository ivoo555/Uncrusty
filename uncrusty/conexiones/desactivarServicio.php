<?php

include("conexion.php");

$id_servicio = $_POST["id_servicio"] ?? "";


if ($id_servicio == "") {

    echo "ID de servicio no válido.";

    exit;

}


$sql = "UPDATE servicios
        SET estado = 'Inactivo'
        WHERE id_servicio = ?";


$stmt = $conexion->prepare($sql);


$stmt->bind_param("i",$id_servicio);

if ($stmt->execute()) {

    echo "Servicio desactivado correctamente.";

} else {

    echo "Error al desactivar el servicio.";

}

$stmt->close();

$conexion->close();

?>