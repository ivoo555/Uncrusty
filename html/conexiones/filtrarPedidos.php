<?php

include("conexion.php");

$estado = $_POST["estado"] ?? "";
$id_repartidor = $_POST["id_repartidor"] ?? "";

$sql = "
    SELECT
        p.id_pedido,
        c.nombreCompleto AS cliente,
        s.nombre AS servicio,
        GROUP_CONCAT(
            DISTINCT dp.prenda
            SEPARATOR ', '
        ) AS prenda,
        p.estado,
        p.id_descuento,
        p.observaciones,
        p.direccion,
        p.fecha_pedido,
        MAX(rep.nombreCompleto) AS repartidor

    FROM pedidos p

    INNER JOIN clientes c
        ON p.id_cliente = c.id_cliente

    LEFT JOIN servicios s
        ON p.id_servicio = s.id_servicio

    LEFT JOIN detalle_pedido dp
        ON p.id_pedido = dp.id_pedido

    LEFT JOIN repartos r
        ON p.id_pedido = r.id_pedido
        AND r.tipo = 'Entrega'

    LEFT JOIN repartidores rep
        ON r.id_repartidor = rep.id_repartidor
";

if ($id_repartidor != "") {

    $sql .= " WHERE r.id_repartidor = ?";

} else {

    $sql .= " WHERE 1=1";

}

if ($estado != "") {

    $sql .= " AND p.estado = ?";

}

$sql .= "
    GROUP BY
        p.id_pedido,
        c.nombreCompleto,
        s.nombre,
        p.estado,
        p.id_descuento,
        p.observaciones,
        p.direccion,
        p.fecha_pedido

    ORDER BY p.id_pedido DESC
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header("Content-Type: application/json");

    echo json_encode(["error" => "Error al preparar la consulta: " . $conexion->error]);
    exit;
}

if ($id_repartidor != "" && $estado != "") {

    $stmt->bind_param("is",$id_repartidor,$estado);

} elseif ($id_repartidor != "") {

    $stmt->bind_param("i",$id_repartidor);

} elseif ($estado != "") {

    $stmt->bind_param("s",$estado);

}

$stmt->execute();

$resultado = $stmt->get_result();

$pedidos = array();

while ($pedido = $resultado->fetch_assoc()) {

    $pedidos[] = $pedido;

}

header("Content-Type: application/json");

echo json_encode($pedidos);

$stmt->close();
$conexion->close();

?>