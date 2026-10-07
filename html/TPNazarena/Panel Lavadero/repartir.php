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

$sql = "SELECT * FROM repartos WHERE id_repartidor = ? AND estado != 'Completado'";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en la consulta");
}

$stmt->bind_param("i", $idrepartidor);

$stmt->execute();

$resultado = $stmt->get_result();

$repartos = [];

while ($fila = $resultado->fetch_assoc()) {
    $repartos[] = $fila;
}

if (count($repartos) == 0) {

    $sql_disponibilidad = "UPDATE repartidores SET disponibilidad = 'Ocupado' WHERE id_repartidor = ?";

    $stmt_disponibilidad = $conexion->prepare($sql_disponibilidad);

    if ($stmt_disponibilidad) {
        $stmt_disponibilidad->bind_param("i", $idrepartidor);
        $stmt_disponibilidad->execute();
        $stmt_disponibilidad->close();
    }
}

header("Content-Type: application/json");

echo json_encode([
    "repartos" => $repartos,
    "cantidad_columnas" => count($repartos)
]);

$stmt->close();
$conexion->close();

?>
