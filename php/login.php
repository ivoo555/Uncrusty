<?php

session_start();

$conexion = new mysqli(
    "localhost",
    "root",
    "",
    "lavanderia_uncrusty"
);

if ($conexion->connect_error) {
    die("Error de conexion");
}

$email = $_POST["email"];
$password = $_POST["password"];

$sql = "SELECT * FROM clientes
WHERE email = ?
AND contrasena = ?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ss",
    $email,
    $password
);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {

    $usuario = $resultado->fetch_assoc();

    $_SESSION["id"] = $usuario["id"];

    ?>

    <script>

    localStorage.setItem("email", "<?php echo $email; ?>");

    <?php if ($usuario["rol"] == "Usuario") { ?>

        window.location = "../html/panel cliente/catalogoServicio.html";

    <?php } elseif ($usuario["rol"] == "Repartidor") { ?>

        window.location = "repartidor.html";

    <?php } else { ?>

        window.location = "../html/Panel Lavadero/Pedidos/index.html";

    <?php } ?>

    </script>

    <?php

} else {

    ?>

    <script>

    alert("usuario o contraseña incorrectos");
    window.location = "../html/login.html";

    </script>

    <?php

}

$stmt->close();
$conexion->close();

?>