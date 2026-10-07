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

    $rol = "Usuario";
    $id = $usuario["id_cliente"];

    $_SESSION["id"] = $id;

    ?>

    <script>

    localStorage.clear();

    localStorage.setItem("email", "<?php echo $email; ?>");
    localStorage.setItem("rol", "<?php echo $rol; ?>");

    localStorage.setItem(
        "idusuario",
        "<?php echo $id; ?>"
    );

    window.location = "catalogoServicio.html";

    </script>

    <?php

    exit();
}


$sql = "SELECT * FROM personallavanderia
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

    $rol = "Lavandero";
    $id = $usuario["id_personal"];

    $_SESSION["id"] = $id;

    ?>

    <script>

    localStorage.clear();

    localStorage.setItem("email", "<?php echo $email; ?>");
    localStorage.setItem("rol", "<?php echo $rol; ?>");

    localStorage.setItem(
        "id_personal",
        "<?php echo $id; ?>"
    );

    window.location = "../html/Panel Lavadero/Pedidos/index.html";

    </script>

    <?php

    exit();
}




$sql = "SELECT * FROM repartidores
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

    $rol = "Repartidor";
    $id = $usuario["id_repartidor"];

    $_SESSION["id"] = $id;

    ?>

    <script>

    localStorage.clear();

    localStorage.setItem("email", "<?php echo $email; ?>");
    localStorage.setItem("rol", "<?php echo $rol; ?>");

    localStorage.setItem(
        "idrepartidor",
        "<?php echo $id; ?>"
    );

    window.location = "repartidor.html";

    </script>

    <?php

    exit();
}

?>

<script>

alert("usuario o contraseña incorrectos");
window.location = "../html/login.html";

</script>

<?php

?>
