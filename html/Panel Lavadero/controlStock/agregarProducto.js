document.addEventListener("DOMContentLoaded", function () {

    const formulario = document.getElementById("formProducto");
    const btnCancelar = document.getElementById("btnCancelar");

    formulario.addEventListener("submit", function (event) {

        event.preventDefault();

        const producto = document.getElementById("Producto").value;
        const marca = document.getElementById("marca").value.trim();
        const cantidadActual =
            document.getElementById("cantidadActual").value;
        const cantidadMinima =
            document.getElementById("cantidadMinima").value;

        if (producto===""){
            alert("Ingrese el nombre de producto");
            return;
        }
        if (marca === "") {

            alert("Ingrese la marca del producto.");
            return;

        }

        if (cantidadActual === "") {

            alert("Ingrese la cantidad actual.");
            return;

        }

        if (cantidadMinima === "") {

            alert("Ingrese la cantidad mínima.");
            return;

        }


        const datos = new FormData();
        datos.append("producto",producto);
        datos.append("marca", marca);
        datos.append("cantidad_actual", cantidadActual);
        datos.append("cantidad_minima", cantidadMinima);


        fetch("../../conexiones/agregarProducto.php", {

            method: "POST",
            body: datos

        })

        .then(response => response.json())

        .then(resultado => {

            console.log("Respuesta del servidor:", resultado);

            if (resultado.error) {

                alert(resultado.mensaje);
                return;

            }

            alert(resultado.mensaje);

            formulario.reset();

        })

        .catch(error => {

            console.error(
                "Error al agregar el producto:",
                error
            );

            alert(
                "Ocurrió un error al agregar el producto."
            );

        });

    });


    btnCancelar.addEventListener("click", function () {

        window.location.href = "index.html";

    });

});