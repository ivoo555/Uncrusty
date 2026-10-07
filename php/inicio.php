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
        "error" => "Error de conexion"
    ]);
    exit;
}

if (!isset($_GET["idusuario"])) {
    echo json_encode([
        "error" => "No se recibio idusuario"
    ]);
    exit;
}

$idusuario = intval($_GET["idusuario"]);

$sql = "SELECT 
            COUNT(*) AS cantidad,
            MIN(fecha_pedido) AS proxima_fecha
        FROM pedidos
        WHERE id_cliente = ?
        AND estado != 'Entregado'";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "error" => "Error en la consulta"
    ]);
    exit;
}

$stmt->bind_param("i", $idusuario);
$stmt->execute();

$stmt->bind_result($cantidad, $proxima_fecha);
$stmt->fetch();

echo json_encode([
    "pedidos_activos" => intval($cantidad),
    "proxima_fecha" => $proxima_fecha
]);

$stmt->close();
$conexion->close();

?>
