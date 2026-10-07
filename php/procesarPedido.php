<?php

session_start();

header("Content-Type: application/json");

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión con la base de datos."
    ]);

    exit;
}

$datos = json_decode(
    file_get_contents("php://input"),
    true
);

$fecha = $datos["fecha"] ?? "";

$hora = $datos["hora"] ?? "";

$direccion = trim(
    $datos["direccion"] ?? ""
);

$cupon = trim(
    $datos["cupon"] ?? ""
);

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

    $id_cliente =
        $_SESSION["idusuario"] ?? null;
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

    $precio =
        floatval($producto["precio"] ?? 0);

    $cantidad =
        intval($producto["cantidad"] ?? 1);

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    $subtotalTotal +=
        $precio * $cantidad;
}

$descuento = 0;

if ($cupon !== "") {

    $sql = "
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

    $stmtCupon =
        $conexion->prepare($sql);

    $stmtCupon->bind_param(
        "s",
        $cupon
    );

    $stmtCupon->execute();

    $resultado =
        $stmtCupon->get_result();

    if ($resultado->num_rows === 0) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón ingresado no existe."
        ]);

        exit;
    }

    $datosCupon =
        $resultado->fetch_assoc();

    $fechaActual =
        date("Y-m-d");

    if (
        strtolower($datosCupon["estado"]) !==
        "activo"
    ) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón no está activo."
        ]);

        exit;
    }

    if (
        $fechaActual <
        $datosCupon["fecha_inicio"]
    ) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón todavía no está disponible."
        ]);

        exit;
    }

    if (
        $fechaActual >
        $datosCupon["fecha_vencimiento"]
    ) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "El cupón está vencido."
        ]);

        exit;
    }

    $descuento =
        floatval(
            $datosCupon["valor_descuento"]
        );

    if ($descuento > $subtotalTotal) {

        $descuento = $subtotalTotal;
    }

    $stmtCupon->close();
}

$costo_envio = 0;

$estado = "pendiente";

$observaciones =
    "Horario de retiro: " . $hora;

$sqlPedido = "
    INSERT INTO pedidos
    (
        id_cliente,
        fecha_pedido,
        direccion_entrega,
        estado,
        subtotal,
        descuento,
        costo_envio,
        total,
        observaciones,
        id_servicio
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmtPedido =
    $conexion->prepare($sqlPedido);

if (!$stmtPedido) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al preparar el pedido."
    ]);

    exit;
}

$pedidosCreados = [];

foreach ($pedido as $producto) {

    $id_servicio =
        intval($producto["id"] ?? 0);

    $precio =
        floatval($producto["precio"] ?? 0);

    $cantidad =
        intval($producto["cantidad"] ?? 1);

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    $subtotalServicio =
        $precio * $cantidad;

    $descuentoServicio = 0;

    if ($subtotalTotal > 0 && $descuento > 0) {

        $descuentoServicio =
            ($subtotalServicio / $subtotalTotal)
            * $descuento;
    }

    $totalServicio =
        $subtotalServicio -
        $descuentoServicio +
        $costo_envio;

    $stmtPedido->bind_param(
        "isssddddsi",
        $id_cliente,
        $fecha,
        $direccion,
        $estado,
        $subtotalServicio,
        $descuentoServicio,
        $costo_envio,
        $totalServicio,
        $observaciones,
        $id_servicio
    );

    if (!$stmtPedido->execute()) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al guardar el pedido."
        ]);

        exit;
    }

    $idPedido =
        $conexion->insert_id;

    $pedidosCreados[] = [
        "idPedido" => $idPedido,
        "id_servicio" => $id_servicio,
        "cantidad" => $cantidad,
        "subtotal" => $subtotalServicio,
        "descuento" => $descuentoServicio,
        "total" => $totalServicio
    ];
}

$stmtPedido->close();

$conexion->close();

echo json_encode([
    "ok" => true,
    "mensaje" => "Pedido realizado correctamente.",
    "pedidos" => $pedidosCreados
]);

?>
