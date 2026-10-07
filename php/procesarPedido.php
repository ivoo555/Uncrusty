<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "uncrustybd"
);

if ($conexion->connect_error) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión con la base de datos."
    ]);
    exit;
}

$conexion->set_charset("utf8mb4");


$datos = json_decode(
    file_get_contents("php://input"),
    true
);

$fecha = $datos["fecha"] ?? "";
$hora = $datos["hora"] ?? "";
$direccion = trim($datos["direccion"] ?? "");
$cupon = trim($datos["cupon"] ?? "");
$pedido = $datos["pedido"] ?? [];


if (
    empty($fecha) ||
    empty($hora) ||
    empty($direccion) ||
    empty($pedido)
) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Faltan datos para realizar el pedido."
    ]);
    exit;
}


$hoy = date("Y-m-d");

if ($fecha <= $hoy) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "La fecha debe ser posterior al día de hoy."
    ]);
    exit;
}


$id_cliente = $_SESSION["id"] ?? null;

if (!$id_cliente) {
    $id_cliente = $_SESSION["idusuario"] ?? null;
}

if (!$id_cliente) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "No se encontró el cliente."
    ]);
    exit;
}

$subtotalTotal = 0;

foreach ($pedido as $producto) {

    $precio = floatval(
        $producto["precio"] ?? 0
    );

    $cantidad = intval(
        $producto["cantidad"] ?? 1
    );

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    $subtotalTotal += $precio * $cantidad;
}


$id_descuento = null;
$descuentoTotal = 0;

if ($cupon !== "") {

    $sqlCupon = "
        SELECT
            id_cupon,
            valor_descuento,
            fecha_inicio,
            fecha_vencimiento,
            estado
        FROM cupones
        WHERE codigo = ?
        LIMIT 1
    ";

    $stmtCupon = $conexion->prepare($sqlCupon);

    if (!$stmtCupon) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al preparar la consulta del cupón."
        ]);
        exit;
    }

    $stmtCupon->bind_param(
        "s",
        $cupon
    );

    $stmtCupon->execute();

    $resultadoCupon = $stmtCupon->get_result();

    if ($resultadoCupon->num_rows === 0) {

        $stmtCupon->close();

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón ingresado no existe."
        ]);

        exit;
    }

    $datosCupon = $resultadoCupon->fetch_assoc();

    $fechaActual = date("Y-m-d");

    if (
        strtolower($datosCupon["estado"]) !== "activo"
    ) {

        $stmtCupon->close();

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón no está activo."
        ]);

        exit;
    }

   
    if (
        $fechaActual < $datosCupon["fecha_inicio"]
    ) {

        $stmtCupon->close();

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón todavía no está disponible."
        ]);

        exit;
    }

    
    if (
        $fechaActual > $datosCupon["fecha_vencimiento"]
    ) {

        $stmtCupon->close();

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón está vencido."
        ]);

        exit;
    }

    $id_descuento = intval(
        $datosCupon["id_cupon"]
    );

    $descuentoTotal = floatval(
        $datosCupon["valor_descuento"]
    );

    if ($descuentoTotal > $subtotalTotal) {
        $descuentoTotal = $subtotalTotal;
    }

    $stmtCupon->close();
}

$estado = "Pendiente";


$observaciones =
    "Horario de retiro: " . $hora;


$sqlPedido = "
    INSERT INTO pedidos
    (
        id_cliente,
        fecha_pedido,
        direccion,
        estado,
        id_descuento,
        observaciones
    )
    VALUES
    (?, ?, ?, ?, ?, ?)
";

$stmtPedido = $conexion->prepare($sqlPedido);

if (!$stmtPedido) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al preparar el pedido: " .
            $conexion->error
    ]);
    exit;
}


$fechaPedido = $fecha . " " . date(
    "H:i:s",
    strtotime($hora)
);

$stmtPedido->bind_param(
    "isssis",
    $id_cliente,
    $fechaPedido,
    $direccion,
    $estado,
    $id_descuento,
    $observaciones
);

if (!$stmtPedido->execute()) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar el pedido: " .
            $stmtPedido->error
    ]);

    exit;
}


$id_pedido = $conexion->insert_id;

$stmtPedido->close();


$sqlDetalle = "
    INSERT INTO detalle_pedido
    (
        id_pedido,
        id_servicio,
        prenda,
        cantidad,
        precio_unitario,
        subtotal
    )
    VALUES
    (?, ?, ?, ?, ?, ?)
";

$stmtDetalle = $conexion->prepare($sqlDetalle);

if (!$stmtDetalle) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al preparar el detalle del pedido: " .
            $conexion->error
    ]);

    exit;
}

$detallesCreados = [];


foreach ($pedido as $producto) {

    $id_servicio = intval(
        $producto["id"] ?? 0
    );

    $nombre = trim(
        $producto["nombre"] ?? ""
    );

    $cantidad = intval(
        $producto["cantidad"] ?? 1
    );

    $precio = floatval(
        $producto["precio"] ?? 0
    );

    if ($cantidad < 1) {
        $cantidad = 1;
    }


    if ($nombre === "") {
        $nombre = "Servicio";
    }

    $subtotalServicio =
        $precio * $cantidad;

    $stmtDetalle->bind_param(
        "iisidd",
        $id_pedido,
        $id_servicio,
        $nombre,
        $cantidad,
        $precio,
        $subtotalServicio
    );

    if (!$stmtDetalle->execute()) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al guardar el detalle del pedido: " .
                $stmtDetalle->error
        ]);

        exit;
    }

    $detallesCreados[] = [
        "id_detalle" => $conexion->insert_id,
        "id_servicio" => $id_servicio,
        "prenda" => $nombre,
        "cantidad" => $cantidad,
        "precio_unitario" => $precio,
        "subtotal" => $subtotalServicio
    ];
}

$stmtDetalle->close();
$conexion->close();


$total = $subtotalTotal - $descuentoTotal;

if ($total < 0) {
    $total = 0;
}

echo json_encode([
    "ok" => true,
    "mensaje" => "Pedido realizado correctamente.",
    "id_pedido" => $id_pedido,
    "subtotal" => $subtotalTotal,
    "descuento" => $descuentoTotal,
    "total" => $total,
    "pedidos" => $detallesCreados
]);

?>
