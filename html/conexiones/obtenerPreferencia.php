<?php

include("conexion.php");

header("Content-Type: application/json; charset=UTF-8");

$id_cliente = $_GET["id_cliente"] ?? "";

if ($id_cliente == "") {
    echo json_encode([
        "error" => "No se recibió el ID del cliente."
    ]);
    exit;
}

$id_cliente = intval($id_cliente);

$sqlServicio = "SELECT d.id_servicio,s.nombre,SUM(d.cantidad) AS cantidad
                FROM detalle_pedido d
                INNER JOIN pedidos p
                    ON d.id_pedido = p.id_pedido
                INNER JOIN servicios s
                    ON d.id_servicio = s.id_servicio
                WHERE p.id_cliente = ?
                GROUP BY d.id_servicio, s.nombre
                ORDER BY cantidad DESC
                LIMIT 1";

$stmtServicio = $conexion->prepare($sqlServicio);
$stmtServicio->bind_param("i", $id_cliente);
$stmtServicio->execute();

$resultadoServicio = $stmtServicio->get_result();

$servicioFavorito = null;
$cantidadServicio = 0;

if ($resultadoServicio->num_rows > 0) {

    $servicio = $resultadoServicio->fetch_assoc();

    $servicioFavorito = $servicio["id_servicio"];
    $cantidadServicio = $servicio["cantidad"];
}

$stmtServicio->close();

$sqlPrenda = "SELECT d.prenda,SUM(d.cantidad) AS cantidad FROM detalle_pedido d
              INNER JOIN pedidos p
                  ON d.id_pedido = p.id_pedido
              WHERE p.id_cliente = ?
              GROUP BY d.prenda
              ORDER BY cantidad DESC
              LIMIT 1";

$stmtPrenda = $conexion->prepare($sqlPrenda);
$stmtPrenda->bind_param("i", $id_cliente);
$stmtPrenda->execute();

$resultadoPrenda = $stmtPrenda->get_result();

$prendaFavorita = null;
$cantidadPrenda = 0;

if ($resultadoPrenda->num_rows > 0) {

    $prenda = $resultadoPrenda->fetch_assoc();

    $prendaFavorita = $prenda["prenda"];
    $cantidadPrenda = $prenda["cantidad"];
}

$stmtPrenda->close();



$sqlExiste = "SELECT id_preferencia FROM preferencias
              WHERE id_cliente = ?";

$stmtExiste = $conexion->prepare($sqlExiste);
$stmtExiste->bind_param("i", $id_cliente);
$stmtExiste->execute();

$resultadoExiste = $stmtExiste->get_result();


if ($resultadoExiste->num_rows > 0) {

    $preferencia = $resultadoExiste->fetch_assoc();

    $idPreferencia = $preferencia["id_preferencia"];

    $sqlActualizar = "UPDATE preferencias
                      SET
                          servicio_favorito = ?,
                          prenda_favorita = ?,
                          cantidad_servicio = ?,
                          cantidad_prenda = ?
                      WHERE id_preferencia = ?";

    $stmtActualizar = $conexion->prepare($sqlActualizar);

    $stmtActualizar->bind_param(
        "isiii",
        $servicioFavorito,
        $prendaFavorita,
        $cantidadServicio,
        $cantidadPrenda,
        $idPreferencia
    );

    $stmtActualizar->execute();

    $stmtActualizar->close();

} else {

    $sqlInsertar = "INSERT INTO preferencias
                    (
                        id_cliente,
                        servicio_favorito,
                        prenda_favorita,
                        cantidad_servicio,
                        cantidad_prenda
                    )
                    VALUES (?, ?, ?, ?, ?)";

    $stmtInsertar = $conexion->prepare($sqlInsertar);

    $stmtInsertar->bind_param(
        "iisii",
        $id_cliente,
        $servicioFavorito,
        $prendaFavorita,
        $cantidadServicio,
        $cantidadPrenda
    );

    $stmtInsertar->execute();

    $stmtInsertar->close();
}

$stmtExiste->close();


$sqlFinal = "SELECT
                 p.id_preferencia,
                 p.id_cliente,
                 p.servicio_favorito,
                 s.nombre AS nombre_servicio,
                 p.prenda_favorita,
                 p.cantidad_servicio,
                 p.cantidad_prenda
             FROM preferencias p
             LEFT JOIN servicios s
                 ON p.servicio_favorito = s.id_servicio
             WHERE p.id_cliente = ?";

$stmtFinal = $conexion->prepare($sqlFinal);
$stmtFinal->bind_param("i", $id_cliente);
$stmtFinal->execute();

$resultadoFinal = $stmtFinal->get_result();

$preferencias = $resultadoFinal->fetch_assoc();

$stmtFinal->close();


echo json_encode([
    "preferencias" => $preferencias
], JSON_UNESCAPED_UNICODE);

$conexion->close();

?>