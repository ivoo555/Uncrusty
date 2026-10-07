<?php

include("conexion.php");

if (
    !isset($_POST["id_repartidor"]) ||
    !isset($_POST["id_pedido"]) ||
    !isset($_POST["direccion"]) ||
    !isset($_POST["fecha"]) ||
    !isset($_POST["hora"])
) {
    echo "Faltan datos para asignar la tarea.";
    exit;
}

$id_repartidor = $_POST["id_repartidor"];
$id_pedido = $_POST["id_pedido"];
$direccion = $_POST["direccion"];
$fecha = $_POST["fecha"];
$hora = $_POST["hora"];


$observaciones = $_POST["observaciones"] ?? "";

$fecha_programada = $fecha . " " . $hora;

$sql = "INSERT INTO repartos
(
    id_pedido,
    id_repartidor,
    tipo,
    direccion,
    fecha_programada,
    estado,
    observaciones
)
VALUES
(
    ?,?,'Entrega',?,?,'Pendiente',?
)";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo "Error al preparar la consulta: " . $conexion->error;
    exit;
}

$stmt->bind_param(
    "iisss",
    $id_pedido,
    $id_repartidor,
    $direccion,
    $fecha_programada,
    $observaciones
);

if ($stmt->execute()) {

    $sqlRepartidor = "
        UPDATE repartidores
        SET disponibilidad = 'Ocupado'
        WHERE id_repartidor = ?
    ";

    $stmtRepartidor = $conexion->prepare($sqlRepartidor);

    $stmtRepartidor->bind_param("i",$id_repartidor);

    $stmtRepartidor->execute();
    $stmtRepartidor->close();

    echo "Tarea asignada correctamente.";

} else {
    echo "Error al asignar la tarea: " . $stmt->error;
}

$stmt->close();
$conexion->close();

?>