<?php

error_reporting(0);
ini_set("display_errors", 0);

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");


$id_pedido = $_POST["id_pedido"] ?? "";
$tipo_comprobante = $_POST["tipo_comprobante"] ?? "";
$nombre_razon_social = trim($_POST["nombre_razon_social"] ?? "");
$identificacion = trim($_POST["identificacion"] ?? "");
$metodo_pago = trim($_POST["metodo_pago"] ?? "");


if (
    $id_pedido == "" ||
    $tipo_comprobante == "" ||
    $nombre_razon_social == "" ||
    $identificacion == "" ||
    $metodo_pago == ""
) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Faltan datos obligatorios."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$id_pedido = intval($id_pedido);


$tiposPermitidos = [
    "Factura A",
    "Factura B",
    "Factura C"
];

if (!in_array($tipo_comprobante, $tiposPermitidos)) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Tipo de comprobante inválido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$sqlPedido = "
    SELECT
        id_pedido,
        id_descuento
    FROM pedidos
    WHERE id_pedido = ?
";

$stmtPedido = $conexion->prepare($sqlPedido);

if (!$stmtPedido) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al preparar la consulta del pedido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmtPedido->bind_param("i", $id_pedido);

$stmtPedido->execute();

$resultadoPedido = $stmtPedido->get_result();

$pedido = $resultadoPedido->fetch_assoc();

$stmtPedido->close();

if (!$pedido) {

    echo json_encode([
        "error" => true,
        "mensaje" => "El pedido no existe."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$sqlVerificar = "
    SELECT id_factura
    FROM facturas
    WHERE id_pedido = ?
    AND estado = 'Emitida'
    LIMIT 1
";

$stmtVerificar = $conexion->prepare($sqlVerificar);

if (!$stmtVerificar) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al verificar la factura existente."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmtVerificar->bind_param(
    "i",
    $id_pedido
);

$stmtVerificar->execute();

$resultadoVerificar = $stmtVerificar->get_result();

if ($resultadoVerificar->num_rows > 0) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Este pedido ya tiene una factura emitida."
    ], JSON_UNESCAPED_UNICODE);

    $stmtVerificar->close();
    $conexion->close();

    exit;
}

$stmtVerificar->close();

$sqlSubtotal = "
    SELECT
        COALESCE(SUM(subtotal), 0) AS subtotal
    FROM detalle_pedido
    WHERE id_pedido = ?
";

$stmtSubtotal = $conexion->prepare($sqlSubtotal);

if (!$stmtSubtotal) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Error al calcular el subtotal."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmtSubtotal->bind_param(
    "i",
    $id_pedido
);

$stmtSubtotal->execute();

$resultadoSubtotal = $stmtSubtotal->get_result();

$datosSubtotal = $resultadoSubtotal->fetch_assoc();

$stmtSubtotal->close();

$subtotal = floatval($datosSubtotal["subtotal"]);


$porcentajeDescuento = 0;

if (
    $pedido["id_descuento"] !== null &&
    $pedido["id_descuento"] != ""
) {

    $id_descuento = intval($pedido["id_descuento"]);

    $sqlCupon = "
        SELECT valor_descuento
        FROM cupones
        WHERE id_cupon = ?
    ";

    $stmtCupon = $conexion->prepare($sqlCupon);

    if (!$stmtCupon) {

        echo json_encode([
            "error" => true,
            "mensaje" => "Error al buscar el cupón."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $stmtCupon->bind_param(
        "i",
        $id_descuento
    );

    $stmtCupon->execute();

    $resultadoCupon = $stmtCupon->get_result();

    $cupon = $resultadoCupon->fetch_assoc();

    $stmtCupon->close();

    if ($cupon) {

        $porcentajeDescuento =
            floatval($cupon["valor_descuento"]);

    }
}



$descuento =
    $subtotal *
    ($porcentajeDescuento / 100);




$costo_envio = 0;



$total =
    $subtotal -
    $descuento +
    $costo_envio;



$urlARCA =
    "http://localhost:8012/TPNazarena/conexiones/simularARCA.php";

$respuestaARCA = @file_get_contents($urlARCA);

if ($respuestaARCA === false) {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se pudo conectar con ARCA."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$arca = json_decode(
    $respuestaARCA,
    true
);

if (
    !$arca ||
    !isset($arca["resultado"])
) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Respuesta inválida de ARCA."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($arca["resultado"] != "A") {

    echo json_encode([
        "error" => true,
        "mensaje" => "ARCA rechazó la factura."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$cae =
    $arca["cae"];

$fecha_vencimiento_cae =
    $arca["fecha_vencimiento_cae"];

$sqlNumero = "
    SELECT COUNT(*) AS cantidad
    FROM facturas
    WHERE tipo_comprobante = ?
";

$stmtNumero = $conexion->prepare($sqlNumero);

if (!$stmtNumero) {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se pudo generar el número de factura."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmtNumero->bind_param(
    "s",
    $tipo_comprobante
);

$stmtNumero->execute();

$resultadoNumero =
    $stmtNumero->get_result();

$datosNumero =
    $resultadoNumero->fetch_assoc();

$numero =
    intval($datosNumero["cantidad"]) + 1;

$stmtNumero->close();

$numero_factura =
    "0001-" .
    str_pad(
        $numero,
        8,
        "0",
        STR_PAD_LEFT
    );


$sql = "
    INSERT INTO facturas (
        id_pedido,
        tipo_comprobante,
        numero_factura,
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
    )
    VALUES (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        'Emitida',
        ?,
        ?
    )
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "error" => true,
        "mensaje" =>
            "Error al preparar la factura: " .
            $conexion->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$stmt->bind_param(
    "isssssddddss",
    $id_pedido,
    $tipo_comprobante,
    $numero_factura,
    $nombre_razon_social,
    $identificacion,
    $metodo_pago,
    $subtotal,
    $descuento,
    $costo_envio,
    $total,
    $cae,
    $fecha_vencimiento_cae
);

if ($stmt->execute()) {

    echo json_encode([

        "error" => false,

        "mensaje" =>
            "Factura generada correctamente.",

        "id_factura" =>
            $conexion->insert_id,

        "numero_factura" =>
            $numero_factura,

        "tipo_comprobante" =>
            $tipo_comprobante,

        "subtotal" =>
            number_format($subtotal, 2, ".", ""),

        "porcentaje_descuento" =>
            number_format($porcentajeDescuento, 2, ".", ""),

        "descuento" =>
            number_format($descuento, 2, ".", ""),

        "costo_envio" =>
            number_format($costo_envio, 2, ".", ""),

        "total" =>
            number_format($total, 2, ".", ""),

        "cae" =>
            $cae,

        "fecha_vencimiento_cae" =>
            $fecha_vencimiento_cae,

        "estado" =>
            "Emitida"

    ], JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode([

        "error" => true,

        "mensaje" =>
            "Error al guardar la factura: " .
            $stmt->error

    ], JSON_UNESCAPED_UNICODE);
}


$stmt->close();
$conexion->close();

?>