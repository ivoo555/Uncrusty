<?php

if (!isset($_GET["idrepartidor"])) {

    ?>
    <script>
        let idrepartidor = localStorage.getItem("idrepartidor");

        if (idrepartidor) {
            window.location.href =
                "repartir.php?idrepartidor=" + idrepartidor;
        }
    </script>
    <?php

    exit;
}


$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "uncrustybd"
);


if ($conexion->connect_error) {
    die("Error de conexion: " . $conexion->connect_error);
}


$conexion->set_charset("utf8mb4");


$idrepartidor = intval($_GET["idrepartidor"]);


// ==========================================
// BUSCAR REPARTOS DEL REPARTIDOR
// ==========================================

$sql = "
    SELECT
        r.id_reparto,
        r.id_pedido,
        r.id_repartidor,
        r.tipo,
        r.direccion,
        p.fecha_pedido AS fecha_programada,
        r.fecha_realizada,
        r.estado,
        r.observaciones
    FROM repartos r
    INNER JOIN pedidos p
        ON r.id_pedido = p.id_pedido
    WHERE r.id_repartidor = ?
    AND r.estado != 'Completado'
    ORDER BY p.fecha_pedido ASC
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en la consulta: " . $conexion->error);
}


$stmt->bind_param(
    "i",
    $idrepartidor
);


$stmt->execute();

$resultado = $stmt->get_result();

$repartos = [];


while ($fila = $resultado->fetch_assoc()) {
    $repartos[] = $fila;
}


// ==========================================
// SI NO TIENE REPARTOS → DISPONIBLE
// ==========================================

if (count($repartos) == 0) {

    $sql_disponibilidad = "
        UPDATE repartidores
        SET disponibilidad = 'Disponible'
        WHERE id_repartidor = ?
    ";

    $stmt_disponibilidad =
        $conexion->prepare($sql_disponibilidad);

    if ($stmt_disponibilidad) {

        $stmt_disponibilidad->bind_param(
            "i",
            $idrepartidor
        );

        $stmt_disponibilidad->execute();

        $stmt_disponibilidad->close();
    }
}


// ==========================================
// RESPUESTA JSON
// ==========================================

header("Content-Type: application/json; charset=UTF-8");

echo json_encode([
    "repartos" => $repartos,
    "cantidad" => count($repartos)
]);


$stmt->close();

$conexion->close();

?>