<?php

header("Content-Type: application/json; charset=UTF-8");

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "uncrustybd"
);

if ($conexion->connect_error) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Error de conexion"
    ]);
    exit;
}

$conexion->set_charset("utf8mb4");


// ==========================================
// RECIBIR DATOS
// ==========================================

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


// ==========================================
// PROCESAR CAMBIOS
// ==========================================

foreach ($datos["cambios"] as $cambio) {

    if (
        !isset($cambio["id_pedido"]) ||
        !isset($cambio["estado"])
    ) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Faltan datos del pedido"
        ]);
        exit;
    }

    $idPedido = intval($cambio["id_pedido"]);
    $estado = $cambio["estado"];


    // ======================================
    // ACTUALIZAR ESTADO DEL REPARTO
    // ======================================

    $sql = "
        UPDATE repartos
        SET estado = ?
        WHERE id_pedido = ?
    ";

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
            "mensaje" => "Error al modificar el reparto"
        ]);

        $stmt->close();
        exit;
    }

    $stmt->close();


    // ======================================
    // SI ESTÁ COMPLETADO
    // ======================================

    if ($estado == "Completado") {


        // ----------------------------------
        // CAMBIAR PEDIDO A ENTREGADO
        // ----------------------------------

        $sql_pedido = "
            UPDATE pedidos
            SET estado = 'Entregado'
            WHERE id_pedido = ?
        ";

        $stmt_pedido =
            $conexion->prepare($sql_pedido);

        if (!$stmt_pedido) {

            echo json_encode([
                "exito" => false,
                "mensaje" => "Error al actualizar el pedido"
            ]);

            exit;
        }

        $stmt_pedido->bind_param(
            "i",
            $idPedido
        );

        if (!$stmt_pedido->execute()) {

            echo json_encode([
                "exito" => false,
                "mensaje" => "Error al cambiar el estado del pedido"
            ]);

            $stmt_pedido->close();
            exit;
        }

        $stmt_pedido->close();


        // ----------------------------------
        // AGREGAR AL HISTORIAL
        // ----------------------------------

        $sql_historial = "
            INSERT INTO historial
            (
                idPedido,
                estrellas
            )

            SELECT
                ?,
                NULL

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

            $stmt_historial->close();
            exit;
        }

        $stmt_historial->close();
    }
}


// ==========================================
// RESPUESTA
// ==========================================

echo json_encode([
    "exito" => true,
    "mensaje" => "Datos modificados correctamente"
]);

$conexion->close();

?>