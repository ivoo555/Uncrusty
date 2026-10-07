<?php

if (!isset($_GET["idrepartidor"])) {
?>

<script>

let idrepartidor = localStorage.getItem("idrepartidor");

if (idrepartidor) {

    window.location.href = "repartir.php?idrepartidor=" + idrepartidor;

}

</script>

<?php
    exit;
}


$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {
    die("Error de conexion: " . $conexion->connect_error);
}


$idrepartidor = $_GET["idrepartidor"];


$sql = "SELECT * FROM repartos WHERE id_repartidor = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en la consulta");
}

$stmt->bind_param("i", $idrepartidor);

$stmt->execute();

$resultado = $stmt->get_result();


$sql_columnas = "SELECT * FROM repartos WHERE id_repartidor = ? AND estado = 'pendiente'";

$stmt_columnas = $conexion->prepare($sql_columnas);

if (!$stmt_columnas) {
    die("Error en la consulta");
}

$stmt_columnas->bind_param("i", $idrepartidor);

$stmt_columnas->execute();

$resultado_columnas = $stmt_columnas->get_result();

$valor_columnas = $resultado_columnas->num_rows;


$repartos = [];

while ($fila = $resultado->fetch_assoc()) {

    $repartos[] = $fila;

}


header("Content-Type: application/json");

echo json_encode([
    "repartos" => $repartos,
    "cantidad_columnas" => $valor_columnas
]);


$stmt->close();
$stmt_columnas->close();
$conexion->close();

?>
