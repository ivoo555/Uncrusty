<?php

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
            "mensaje" => "Error de conexión"
        ]);
        exit;
    }
    
    $conexion->set_charset("utf8mb4");
    
    $id_cliente = $_GET["idusuario"] ?? null;
    
    if (!$id_cliente) {
        session_start();
    
        $id_cliente = $_SESSION["id"] ?? null;
    
        if (!$id_cliente) {
            $id_cliente = $_SESSION["idusuario"] ?? null;
        }
    }
    
    if (!$id_cliente) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Usuario no encontrado"
        ]);
        exit;
    }
    
    $id_cliente = intval($id_cliente);
    
    
    $sql = "
        SELECT
            p.id_pedido,
            dp.id_detalle,
            dp.id_servicio,
            dp.prenda,
            dp.cantidad,
            dp.precio_unitario,
            dp.subtotal,
            p.fecha_pedido,
            p.estado,
            p.direccion,
            p.observaciones
    
        FROM pedidos p
    
        INNER JOIN detalle_pedido dp
            ON p.id_pedido = dp.id_pedido
    
        WHERE p.id_cliente = ?
    
        ORDER BY p.id_pedido DESC
    ";
    
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error en la consulta: " . $conexion->error
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $id_cliente);
    
    if (!$stmt->execute()) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al ejecutar la consulta: " . $stmt->error
        ]);
        exit;
    }
    
    $resultado = $stmt->get_result();
    
    $pedidos = [];
    
    while ($fila = $resultado->fetch_assoc()) {
    
        $pedidos[] = [
            "id_pedido" => $fila["id_pedido"],
            "id_detalle" => $fila["id_detalle"],
    
            "id_servicio" => $fila["id_servicio"],
            "prenda" => $fila["prenda"],
            "cantidad" => $fila["cantidad"],
            "precio_unitario" => $fila["precio_unitario"],
            "subtotal" => $fila["subtotal"],
    
            "fecha_pedido" => $fila["fecha_pedido"],
            "estado" => $fila["estado"],
            "direccion" => $fila["direccion"],
            "observaciones" => $fila["observaciones"],
    
            "servicio" => $fila["prenda"]
        ];
    }
    
    echo json_encode([
        "ok" => true,
        "pedidos" => $pedidos
    ]);
    
    $stmt->close();
    $conexion->close();

?>    
