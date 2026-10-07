<?php

include("conexion.php");

if (
    !isset($_POST["id_repartidor"]) ||
    !isset($_POST["id_pedido"])
) {
    echo "Faltan datos para asignar la tarea.";
    exit;
}

$id_repartidor = intval($_POST["id_repartidor"]);
$id_pedido = intval($_POST["id_pedido"]);
$observaciones = $_POST["observaciones"] ?? "";

$conexion->begin_transaction();

try {

    // 1. Obtener la dirección del pedido
    $sqlDireccion = "
        SELECT direccion
        FROM pedidos
        WHERE id_pedido = ?
    ";

    $stmtDireccion = $conexion->prepare($sqlDireccion);

    if (!$stmtDireccion) {
        throw new Exception(
            "Error al preparar la consulta de dirección: " .
            $conexion->error
        );
    }

    $stmtDireccion->bind_param("i", $id_pedido);
    $stmtDireccion->execute();

    $resultadoDireccion = $stmtDireccion->get_result();

    if ($resultadoDireccion->num_rows == 0) {
        throw new Exception("No se encontró el pedido.");
    }

    $pedido = $resultadoDireccion->fetch_assoc();

    $direccion = $pedido["direccion"];

    $stmtDireccion->close();


    // 2. Crear el reparto
    $sql = "
        INSERT INTO repartos
        (
            id_pedido,
            id_repartidor,
            tipo,
            direccion,
            estado,
            observaciones
        )
        VALUES (?, ?, 'Entrega', ?, 'Pendiente', ?)
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Error al preparar el INSERT: " . $conexion->error
        );
    }

    $stmt->bind_param(
        "iiss",
        $id_pedido,
        $id_repartidor,
        $direccion,
        $observaciones
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Error al crear el reparto: " . $stmt->error
        );
    }

    $stmt->close();


    // 3. Cambiar disponibilidad del repartidor
    $sqlRepartidor = "
        UPDATE repartidores
        SET disponibilidad = 'Ocupado'
        WHERE id_repartidor = ?
    ";

    $stmtRepartidor = $conexion->prepare($sqlRepartidor);

    if (!$stmtRepartidor) {
        throw new Exception(
            "Error al preparar el UPDATE del repartidor: " .
            $conexion->error
        );
    }

    $stmtRepartidor->bind_param(
        "i",
        $id_repartidor
    );

    if (!$stmtRepartidor->execute()) {
        throw new Exception(
            "Error al actualizar el repartidor: " .
            $stmtRepartidor->error
        );
    }

    $stmtRepartidor->close();


    // 4. Cambiar estado del pedido
    $sqlPedido = "
        UPDATE pedidos
        SET estado = 'En Recolección'
        WHERE id_pedido = ?
    ";

    $stmtPedido = $conexion->prepare($sqlPedido);

    if (!$stmtPedido) {
        throw new Exception(
            "Error al preparar el UPDATE del pedido: " .
            $conexion->error
        );
    }

    $stmtPedido->bind_param(
        "i",
        $id_pedido
    );

    if (!$stmtPedido->execute()) {
        throw new Exception(
            "Error al actualizar el pedido: " .
            $stmtPedido->error
        );
    }

    $stmtPedido->close();


    // 5. Confirmar los cambios
    $conexion->commit();

    echo "Tarea asignada correctamente.";

} catch (Exception $e) {

    $conexion->rollback();

    echo "Error al asignar la tarea: " . $e->getMessage();
}

$conexion->close();

?>