<?php

header("Content-Type: application/json; charset=utf-8");


$cae = str_pad(
    (string) rand(10000000000000, 99999999999999),
    14,
    "0",
    STR_PAD_LEFT
);

$fecha_vencimiento = date(
    "Y-m-d",
    strtotime("+10 days")
);

echo json_encode([
    "resultado" => "A",
    "cae" => $cae,
    "fecha_vencimiento_cae" => $fecha_vencimiento
]);

?>