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


$subtotal = 0;


foreach ($pedido as $producto) {

    $precio =
        floatval($producto["precio"] ?? 0);

    $cantidad =
        intval($producto["cantidad"] ?? 1);


    if ($cantidad < 1) {
        $cantidad = 1;
    }


    $subtotal +=
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


    $stmt =
        $conexion->prepare($sql);


    $stmt->bind_param(
        "s",
        $cupon
    );


    $stmt->execute();


    $resultado =
        $stmt->get_result();


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


    // VERIFICAR ESTADO

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



    if ($descuento > $subtotal) {

        $descuento = $subtotal;

    }

}




$costo_envio = 0;


$total =
    $subtotal -
    $descuento +
    $costo_envio;



$estado = "pendiente";



$observaciones =
    "Horario de retiro: " . $hora;



$sql = "
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


$stmt =
    $conexion->prepare($sql);


if (!$stmt) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al preparar el pedido."
    ]);

    exit;
}


foreach ($pedido as $producto) {


    $id_servicio =
        intval($producto["id"]);


    $stmt->bind_param(
        "isssddddsi",
        $id_cliente,
        $fecha,
        $direccion,
        $estado,
        $subtotal,
        $descuento,
        $costo_envio,
        $total,
        $observaciones,
        $id_servicio
    );


    if (!$stmt->execute()) {

        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al guardar el pedido."
        ]);

        exit;
    }

}




echo json_encode([

    "ok" => true,

    "mensaje" => "Pedido realizado correctamente.",

    "subtotal" => $subtotal,

    "descuento" => $descuento,

    "costo_envio" => $costo_envio,

    "total" => $total

]);


$stmt->close();

$conexion->close();

?>
