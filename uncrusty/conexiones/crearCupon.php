<?php

include("conexion.php");


$codigo = $_POST["codigo"] ?? "";
$valor = $_POST["valor"] ?? "";
$fecha_vencimiento = $_POST["fecha_vencimiento"] ?? "";


if ( $codigo == "" || $valor == "" || $fecha_vencimiento == "") {

    echo "Faltan datos.";
    exit;
}


$codigo = strtoupper(trim($codigo));


$sqlExiste = "SELECT id_cupon FROM cupones
              WHERE codigo = ?";

$stmtExiste = $conexion->prepare($sqlExiste);

$stmtExiste->bind_param("s",$codigo);

$stmtExiste->execute();

$resultado = $stmtExiste->get_result();


if ($resultado->num_rows > 0) {

    echo "Ya existe un cupón con ese código.";

    $stmtExiste->close();
    $conexion->close();

    exit;

}

$stmtExiste->close();

$fecha_inicio = date("Y-m-d");

$sql = "INSERT INTO cupones
        (
            codigo,
            valor_descuento,
            fecha_inicio,
            fecha_vencimiento,
            estado
        )
        VALUES (?, ?, ?, ?, 'Activo')";


$stmt = $conexion->prepare($sql);


$stmt->bind_param("sdss",$codigo,$valor,$fecha_inicio,$fecha_vencimiento);


if ($stmt->execute()) {

    echo "Cupón creado correctamente.";

} else {

    echo "Error al crear el cupón: " . $conexion->error;

}

$stmt->close();

$conexion->close();

?>