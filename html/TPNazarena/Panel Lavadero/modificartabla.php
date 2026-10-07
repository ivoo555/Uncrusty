<?php

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "Error de conexion"
    ]);

    exit;
}



$datos = json_decode(
    file_get_contents("php://input"),
    true
);



if (
    !isset($datos["cambios"]) ||
    !is_array($datos["cambios"])
) {

    echo json_encode([
        "exito" => false,
        "mensaje" => "No se recibieron cambios"
    ]);

    exit;
}



$sql = "UPDATE repartos
        SET estado = ?
        WHERE id_pedido = ?";

$stmt = $conexion->prepare($sql);



foreach ($datos["cambios"] as $cambio) {

    $id_pedido = $cambio["id_pedido"];

    $estado = $cambio["estado"];



    if (
        $estado != "pendiente" &&
        $estado != "en proceso" &&
        $estado != "completado"
    ) {

        continue;
    }



    $stmt->bind_param(
        "si",
        $estado,
        $id_pedido
    );

    $stmt->execute();

}


$stmt->close();

$conexion->close();


echo json_encode([
    "exito" => true,
    "mensaje" => "Cambios realizados correctamente"
]);

?>
