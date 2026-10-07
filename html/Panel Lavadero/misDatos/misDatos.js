document.addEventListener("DOMContentLoaded", function () {

    const formulario = document.getElementById("formDatos");

    const nombreCompleto =
        document.getElementById("nombreCompleto");

    const DNI =
        document.getElementById("DNI");

    const email =
        document.getElementById("email");

    const contrasena =
        document.getElementById("contrasena");

    const confirmarContrasena =
        document.getElementById("confirmarContrasena");

    const btnCancelar =
        document.getElementById("btnCancelar");


    // ==========================================
    // CARGAR DATOS DEL USUARIO
    // ==========================================

    fetch("../../conexiones/cargarDatosPersonal.php")
        .then(response => response.json())
        .then(datos => {

            if (datos.error) {

                alert(datos.error);

                return;
            }

            const personal = datos.personal;

            nombreCompleto.value =
                personal.nombreCompleto || "";

            DNI.value =
                personal.DNI || "";

            email.value =
                personal.email || "";

        })
        .catch(error => {

            console.error(
                "Error al cargar los datos:",
                error
            );

            alert(
                "No se pudieron cargar los datos."
            );
        });


    // ==========================================
    // GUARDAR CAMBIOS
    // ==========================================

    formulario.addEventListener("submit", function (event) {

        event.preventDefault();


        // Verificar contraseña
        if (
            contrasena.value !==
            confirmarContrasena.value
        ) {

            alert(
                "Las contraseñas no coinciden."
            );

            return;
        }


        const datosFormulario =
            new FormData(formulario);


        fetch(
            "../../conexiones/modificarDatosPersonal.php",
            {
                method: "POST",
                body: datosFormulario
            }
        )
            .then(response => response.json())
            .then(datos => {

                if (datos.error) {

                    alert(datos.error);

                    return;
                }

                alert(datos.mensaje);

                // Limpiar los campos de contraseña
                contrasena.value = "";
                confirmarContrasena.value = "";

            })
            .catch(error => {

                console.error(
                    "Error al modificar los datos:",
                    error
                );

                alert(
                    "Ocurrió un error al guardar los cambios."
                );
            });

    });


    // ==========================================
    // CANCELAR
    // ==========================================

    btnCancelar.addEventListener("click", function () {

        window.history.back();

    });

});