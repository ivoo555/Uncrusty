<?php

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {
    die("Error de conexion x.x");
}

$contraseña = trim($_POST["password"]);
$dni = trim($_POST["Dni"]);
$email = trim($_POST["email"]);
$rol = trim($_POST["rol"]);

if (!preg_match('/^[0-9]{8}$/', $dni)) {
    ?>
    <script>
       alert("dni distintas cifras");
        window.location = "../html/registro.html";
    </script>
    <?php
    exit();
     
   
}

$sql = "INSERT INTO clientes
(email,DNI,rol,contrasena)
VALUES (?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ssss",
    $email,
    $dni,
    $rol,
    $contraseña
);

try {

    $stmt->execute();

    header("Location: ../html/login.html ");
    exit();

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {

        echo "gmail o dni ya en uso";

    } else {

        echo "Error de MySQL: " . $e->getMessage();

    }
}