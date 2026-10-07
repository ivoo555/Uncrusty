<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

$id_producto = $_POST["id_producto"] ?? "";

if ($id_producto == "") {
    echo json_encode([
        "error" => true,
        "mensaje" => "No se especificó el producto."
    ]);
    exit;
}

$id_producto = intval($id_producto);

$sqlBuscar = "SELECT id_producto FROM stock WHERE id_producto = ?";

$stmtBuscar = $conexion->prepare($sqlBuscar);

if (!$stmtBuscar) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Error en la consulta: " . $conexion->error
    ]);
    exit;
}

$stmtBuscar->bind_param("i", $id_producto);
$stmtBuscar->execute();

$resultado = $stmtBuscar->get_result();

if ($resultado->num_rows == 0) {
    echo json_encode([
        "error" => true,
        "mensaje" => "El producto no existe."
    ]);
    exit;
}

$stmtBuscar->close();


$sqlEliminar = "DELETE FROM stock WHERE id_producto = ?";

$stmtEliminar = $conexion->prepare($sqlEliminar);

if (!$stmtEliminar) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Error al preparar DELETE: " . $conexion->error
    ]);
    exit;
}

$stmtEliminar->bind_param("i", $id_producto);

if ($stmtEliminar->execute()) {

    echo json_encode([
        "error" => false,
        "mensaje" => "Producto eliminado correctamente."
    ]);

} else {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se pudo eliminar el producto: " . $stmtEliminar->error
    ]);
}

$stmtEliminar->close();
$conexion->close();

?>