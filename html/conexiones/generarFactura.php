<?php

error_reporting(0);
ini_set("display_errors", 0);

header("Content-Type: application/json; charset=utf-8");

include("conexion.php");

mysqli_set_charset($conexion, "utf8mb4");


// ==========================================
// RECIBIR DATOS
// ==========================================

$id_pedido = $_POST["id_pedido"] ?? "";
$tipo_comprobante = $_POST["tipo_comprobante"] ?? "";
$nombre_razon_social = $_POST["nombre_razon_social"] ?? "";
$identificacion = $_POST["identificacion"] ?? "";
$metodo_pago = $_POST["metodo_pago"] ?? "";

$subtotal = $_POST["subtotal"] ?? 0;
$descuento = $_POST["descuento"] ?? 0;
$costo_envio = $_POST["costo_envio"] ?? 0;
$total = $_POST["total"] ?? 0;


// ==========================================
// VALIDAR DATOS
// ==========================================

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
    ]);

    exit;
}


// ==========================================
// VALIDAR COMPROBANTE
// ==========================================

$tiposPermitidos = [
    "Factura A",
    "Factura B",
    "Factura C"
];

if (!in_array($tipo_comprobante, $tiposPermitidos)) {

    echo json_encode([
        "error" => true,
        "mensaje" => "Tipo de comprobante inválido."
    ]);

    exit;
}


// ==========================================
// VERIFICAR SI EL PEDIDO YA FUE FACTURADO
// ==========================================

$sqlVerificar = "
    SELECT id_factura
    FROM facturas
    WHERE id_pedido = ?
    AND estado = 'Emitida'
    LIMIT 1
";

$stmtVerificar = $conexion->prepare($sqlVerificar);

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
    ]);

    $stmtVerificar->close();
    $conexion->close();

    exit;
}

$stmtVerificar->close();


// ==========================================
// SIMULAR CONEXIÓN CON ARCA
// ==========================================

$urlARCA =
    "http://localhost:8012/TPNazarena/conexiones/simularARCA.php";

$respuestaARCA = @file_get_contents($urlARCA);


if ($respuestaARCA === false) {

    echo json_encode([
        "error" => true,
        "mensaje" => "No se pudo conectar con ARCA."
    ]);

    exit;
}


// ==========================================
// DECODIFICAR RESPUESTA
// ==========================================

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
    ]);

    exit;
}


// ==========================================
// VERIFICAR AUTORIZACIÓN
// ==========================================

if ($arca["resultado"] != "A") {

    echo json_encode([
        "error" => true,
        "mensaje" => "ARCA rechazó la factura."
    ]);

    exit;
}


$cae = $arca["cae"];

$fecha_vencimiento_cae =
    $arca["fecha_vencimiento_cae"];


// ==========================================
// GENERAR NÚMERO DE FACTURA
// ==========================================

$sqlNumero = "
    SELECT COUNT(*) AS cantidad
    FROM facturas
    WHERE tipo_comprobante = ?
";

$stmtNumero = $conexion->prepare(
    $sqlNumero
);

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


// Formato:
// 0001-00000001

$numero_factura =
    "0001-" .
    str_pad(
        $numero,
        8,
        "0",
        STR_PAD_LEFT
    );


// ==========================================
// INSERTAR FACTURA
// ==========================================

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
    ]);

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


// ==========================================
// GUARDAR
// ==========================================

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

        "cae" =>
            $cae,

        "fecha_vencimiento_cae" =>
            $fecha_vencimiento_cae,

        "estado" =>
            "Emitida"

    ]);

} else {

    echo json_encode([

        "error" => true,

        "mensaje" =>
            "Error al guardar la factura: " .
            $stmt->error

    ]);
}


$stmt->close();
$conexion->close();

?>