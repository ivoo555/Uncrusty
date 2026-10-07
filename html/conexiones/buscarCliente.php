<?php

include("conexion.php");

header("Content-Type: application/json");

if (!isset($_GET["nombreCompleto"])) {
    echo json_encode(["error" => "No se recibió el nombre."]);
    exit;
}

$nombreCompleto = trim($_GET["nombreCompleto"]);

$sql = "
    SELECT
        c.id_cliente,
        c.DNI,
        c.email,
        c.nombreCompleto,
        p.id_pedido,
        p.fecha_pedido,
        p.estado,
        p.direccion AS direccion_pedido,
        p.id_descuento,
        p.observaciones,

        COALESCE(SUM(dp.subtotal), 0) AS total

    FROM clientes c

    LEFT JOIN pedidos p
        ON c.id_cliente = p.id_cliente

    LEFT JOIN detalle_pedido dp
        ON p.id_pedido = dp.id_pedido

    WHERE c.nombreCompleto = ?

    GROUP BY
        c.id_cliente,
        c.nombreCompleto,
        c.DNI,
        c.email,
        p.id_pedido,
        p.fecha_pedido,
        p.estado,
        p.direccion,
        p.id_descuento,
        p.observaciones

    ORDER BY p.fecha_pedido DESC
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "error" => "Error al preparar la consulta: " . $conexion->error
    ]);
    exit;
}

$stmt->bind_param("s", $nombreCompleto);

if (!$stmt->execute()) {
    echo json_encode(["error" => "Error al ejecutar la consulta: " . $stmt->error]);
    exit;
}

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(["error" => "No se encontró ningún cliente con ese nombre."]);
    exit;
}

$cliente = null;
$pedidos = [];

while ($fila = $resultado->fetch_assoc()) {

    if ($cliente === null) {

        $cliente = [
            "id_cliente" => $fila["id_cliente"],
            "nombreCompleto" => $fila["nombreCompleto"],
            "DNI" => $fila["DNI"],
            "email" => $fila["email"]
        ];
    }

    if ($fila["id_pedido"] !== null) {

        $idPedido = $fila["id_pedido"];

        $sqlDetalles = "
            SELECT
                dp.prenda,
                dp.cantidad,
                s.nombre     AS servicio
            FROM detalle_pedido dp
            LEFT JOIN servicios s
                ON dp.id_servicio = s.id_servicio
            WHERE dp.id_pedido = ?
        ";

        $stmtDetalle = $conexion->prepare($sqlDetalles);

        $stmtDetalle->bind_param( "i", $idPedido);

        $stmtDetalle->execute();

        $resultadoDetalles = $stmtDetalle->get_result();

        $detalles = [];

        while ($detalle = $resultadoDetalles->fetch_assoc()) {

            $detalles[] = [
                "prenda" => $detalle["prenda"],
                "servicio" => $detalle["servicio"],
                "cantidad" => $detalle["cantidad"]
            ];
        }

        $stmtDetalle->close();

        $pedidos[] = [
            "id_pedido" => $fila["id_pedido"],
            "fecha_pedido" => $fila["fecha_pedido"],
            "estado" => $fila["estado"],
            "direccion" => $fila["direccion_pedido"],
            "id_descuento" => $fila["id_descuento"],
            "observaciones" => $fila["observaciones"],
            "total" => $fila["total"],
            "detalles" => $detalles
        ];
    }
}

echo json_encode([
    "cliente" => $cliente,
    "pedidos" => $pedidos,
    "pedidos_totales" => count($pedidos)
]);

$stmt->close();
$conexion->close();

?>
