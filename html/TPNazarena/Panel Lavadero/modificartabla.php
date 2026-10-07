<?php

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {
    die(json_encode([
        "exito" => false,
        "mensaje" => "Error de conexion"
    ]));
}

$datos = json_decode(
    file_get_contents("php://input"),
    true
);

if (!isset($datos["cambios"])) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "No se recibieron cambios"
    ]);
    exit;
}

foreach ($datos["cambios"] as $cambio) {

    $idPedido = $cambio["id_pedido"];
    $estado = $cambio["estado"];

    $sql = "UPDATE repartos SET estado = ? WHERE id_pedido = ?";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Error al preparar la consulta"
        ]);
        exit;
    }

    $stmt->bind_param(
        "si",
        $estado,
        $idPedido
    );

    if (!$stmt->execute()) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Error al modificar el pedido"
        ]);
        exit;
    }

    $stmt->close();


    if ($estado == "Completado") {

        $sql_historial = "
            INSERT INTO historial (idPedido, estrellas)
            SELECT ?, NULL
            WHERE NOT EXISTS (
                SELECT 1
                FROM historial
                WHERE idPedido = ?
            )
        ";

        $stmt_historial =
            $conexion->prepare($sql_historial);

        if (!$stmt_historial) {
            echo json_encode([
                "exito" => false,
                "mensaje" => "Error al preparar historial"
            ]);
            exit;
        }

        $stmt_historial->bind_param(
            "ii",
            $idPedido,
            $idPedido
        );

        if (!$stmt_historial->execute()) {
            echo json_encode([
                "exito" => false,
                "mensaje" => "Error al agregar al historial"
            ]);
            exit;
        }

        $stmt_historial->close();
    }
}

echo json_encode([
    "exito" => true,
    "mensaje" => "Datos modificados correctamente"
]);

$conexion->close();

?>
