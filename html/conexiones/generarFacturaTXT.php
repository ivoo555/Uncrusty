<?php

header("Content-Type: text/plain; charset=utf-8");

$id_factura = $_GET["id_factura"] ?? "";

if ($id_factura == "") {
    echo "No se especificó la factura.";
    exit;
}

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");

$sql = "SELECT
            id_factura,
            id_pedido,
            tipo_comprobante,
            numero_factura,
            fecha_emision,
            nombre_razon_social,
            identificacion,
            metodo_pago,
            subtotal,
            descuento,
            costo_envio,
            total,
            estado,
            cae,
            fecha_vencimiento_cae
        FROM facturas
        WHERE id_factura = ?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id_factura
);

$stmt->execute();

$resultado = $stmt->get_result();

$factura = $resultado->fetch_assoc();

if (!$factura) {

    echo "No se encontró la factura.";

    exit;
}

$sqlDetalle = "SELECT
                    detalle_pedido.prenda,
                    detalle_pedido.cantidad,
                    detalle_pedido.precio_unitario,
                    detalle_pedido.subtotal,
                    servicios.nombre AS servicio
               FROM detalle_pedido
               INNER JOIN servicios
                   ON detalle_pedido.id_servicio = servicios.id_servicio
               WHERE detalle_pedido.id_pedido = ?";

$stmtDetalle = $conexion->prepare($sqlDetalle);

$stmtDetalle->bind_param(
    "i",
    $factura["id_pedido"]
);

$stmtDetalle->execute();

$resultadoDetalle =
    $stmtDetalle->get_result();



$txt = "";

$txt .= "========================================\n";
$txt .= "          FACTURA ELECTRÓNICA\n";
$txt .= "========================================\n\n";

$txt .= "Tipo de comprobante: " .
        $factura["tipo_comprobante"] . "\n";

$txt .= "Número: " .
        $factura["numero_factura"] . "\n";

$txt .= "Fecha de emisión: " .
        $factura["fecha_emision"] . "\n";

$txt .= "ID Pedido: " .
        $factura["id_pedido"] . "\n\n";


$txt .= "--------------- CLIENTE ----------------\n";

$txt .= "Nombre/Razón Social: " .
        $factura["nombre_razon_social"] . "\n";

$txt .= "CUIT/CUIL/DNI: " .
        $factura["identificacion"] . "\n";

$txt .= "Método de pago: " .
        $factura["metodo_pago"] . "\n\n";


$txt .= "--------------- DETALLE ----------------\n";

while ($detalle = $resultadoDetalle->fetch_assoc()) {

    $txt .= "\n";

    $txt .= "Servicio: " .
            $detalle["servicio"] . "\n";

    $txt .= "Prenda: " .
            $detalle["prenda"] . "\n";

    $txt .= "Cantidad: " .
            $detalle["cantidad"] . "\n";

    $txt .= "Precio unitario: $" .
            number_format(
                $detalle["precio_unitario"],
                2,
                ".",
                ""
            ) . "\n";

    $txt .= "Subtotal: $" .
            number_format(
                $detalle["subtotal"],
                2,
                ".",
                ""
            ) . "\n";

    $txt .= "----------------------------------------\n";
}


$txt .= "\n";
$txt .= "--------------- RESUMEN ----------------\n";

$txt .= "Subtotal: $" .
        number_format(
            $factura["subtotal"],
            2,
            ".",
            ""
        ) . "\n";

$txt .= "Descuento: $" .
        number_format(
            $factura["descuento"],
            2,
            ".",
            ""
        ) . "\n";

$txt .= "Costo de envío: $" .
        number_format(
            $factura["costo_envio"],
            2,
            ".",
            ""
        ) . "\n";

$txt .= "TOTAL: $" .
        number_format(
            $factura["total"],
            2,
            ".",
            ""
        ) . "\n\n";


$txt .= "-------------- DATOS ARCA --------------\n";

$txt .= "CAE: " .
        $factura["cae"] . "\n";

$txt .= "Vencimiento CAE: " .
        $factura["fecha_vencimiento_cae"] . "\n";

$txt .= "Estado: " .
        strtoupper($factura["estado"]) . "\n";

$txt .= "\n========================================\n";


echo $txt;


$stmt->close();
$stmtDetalle->close();
$conexion->close();

?>