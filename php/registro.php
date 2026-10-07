<?php

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "uncrustybd"
);

if ($conexion->connect_error) {
    die("Error de conexion x.x");
}

$contraseña = trim($_POST["password"] ?? "");
$dni = trim($_POST["Dni"] ?? "");
$email = trim($_POST["email"] ?? "");
$rol = trim($_POST["rol"] ?? "");

$nombre = isset($_POST["nombre"])
    ? trim($_POST["nombre"])
    : "";

$telefono = isset($_POST["telefono"])
    ? trim($_POST["telefono"])
    : "";




if (!preg_match('/^[0-9]{8}$/', $dni)) {
    ?>
    <script>
        alert("DNI debe tener 8 cifras");
        window.location = "../html/registro.html";
    </script>
    <?php
    exit();
}



$sql_verificar = "
    SELECT email
    FROM clientes
    WHERE email = ?

    UNION

    SELECT email
    FROM personallavanderia
    WHERE email = ?

    UNION

    SELECT email
    FROM repartidores
    WHERE email = ?
";

$stmt_verificar = $conexion->prepare($sql_verificar);

if (!$stmt_verificar) {
    die("Error al preparar la consulta.");
}

$stmt_verificar->bind_param(
    "sss",
    $email,
    $email,
    $email
);

$stmt_verificar->execute();

$resultado = $stmt_verificar->get_result();

if ($resultado->num_rows > 0) {
    ?>
    <script>
        alert("El gmail ya está en uso");
        window.location = "../html/registro.html";
    </script>
    <?php
    exit();
}

$stmt_verificar->close();




try {


    if ($rol == "Usuario") {

        $sql = "
            INSERT INTO clientes
            (
                nombreCompleto,
                email,
                DNI,
                contrasena
            )
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $nombre,
            $email,
            $dni,
            $contraseña
        );
    }




    elseif ($rol == "Lavandero") {

        $sql = "
            INSERT INTO personallavanderia
            (
                nombreCompleto,
                DNI,
                email,
                contrasena
            )
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $nombre,
            $dni,
            $email,
            $contraseña
        );
    }


    

    elseif ($rol == "Repartidor") {

        $disponibilidad = 1;

        $sql = "
            INSERT INTO repartidores
            (
                nombreCompleto,
                DNI,
                email,
                contrasena,
                telefono,
                disponibilidad
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "sssssi",
            $nombre,
            $dni,
            $email,
            $contraseña,
            $telefono,
            $disponibilidad
        );
    }




    else {
        ?>
        <script>
            alert("Rol no valido");
            window.location = "../html/registro.html";
        </script>
        <?php
        exit();
    }



    $stmt->execute();

    ?>

    <script>
        localStorage.clear();

        localStorage.setItem(
            "rol",
            "<?php echo $rol; ?>"
        );

        alert("Registro realizado correctamente");

        window.location = "../html/login.html";
    </script>

    <?php

    exit();

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {

        echo "Gmail o DNI ya en uso.";

    } else {

        echo "Error de MySQL: " . $e->getMessage();
    }
}

?>
