<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=utf-8");
include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");


$sql = "SELECT id_pedido FROM pedidos ORDER BY id_pedido DESC";
$resultado = mysqli_query($conexion, $sql);

$pedidos = array();

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $pedidos[] = $fila;
    }
}

if (ob_get_length()) ob_clean();

echo json_encode($pedidos, JSON_UNESCAPED_UNICODE);

mysqli_close($conexion);
exit;
?>
