document.addEventListener("DOMContentLoaded", function () {

    const selectRepartidor = document.getElementById("Repartidor");
    const selectPedido = document.getElementById("Pedido");
    const listaRepartidores = document.getElementById("listaRepartidores");
    const formulario = document.querySelector("form");


    fetch("../../conexiones/cargarDatosAsignarTareas.php")
        .then(response => response.json())
        .then(datos => {


            selectRepartidor.innerHTML =
                '<option value="" disabled selected hidden>Seleccione un repartidor</option>';

            datos.repartidores.forEach(repartidor => {

                const opcion = document.createElement("option");

                opcion.value = repartidor.id_repartidor;
                opcion.textContent = repartidor.nombreCompleto;

                selectRepartidor.appendChild(opcion);
            });



            selectPedido.innerHTML =
                '<option value="" disabled selected hidden>Seleccione un pedido</option>';

            datos.pedidos.forEach(pedido => {

                const opcion = document.createElement("option");

                opcion.value = pedido.id_pedido;

                opcion.textContent =
                    "Pedido #" +
                    pedido.id_pedido +
                    " - " +
                    pedido.cliente;

                selectPedido.appendChild(opcion);
            });




            listaRepartidores.innerHTML = "";

            datos.repartidores.forEach(repartidor => {

                if (repartidor.disponibilidad === "Disponible") {

                    const div = document.createElement("div");

                    div.classList.add("repartidorDisponible");

                    div.innerHTML = `
                        <span>${repartidor.nombreCompleto}</span>
                        <button type="button">
                            Asignar
                        </button>
                    `;

                    const boton = div.querySelector("button");

                    boton.addEventListener("click", function () {

                        selectRepartidor.value =
                            repartidor.id_repartidor;

                        window.scrollTo({
                            top: 0,
                            behavior: "smooth"
                        });
                    });

                    listaRepartidores.appendChild(div);
                }
            });

        })
        .catch(error => {

            console.error(
                "Error al cargar los datos:",
                error
            );

            alert(
                "No se pudieron cargar los repartidores y pedidos."
            );
        });



    formulario.addEventListener("submit", function (event) {

        event.preventDefault();

        const idRepartidor = selectRepartidor.value;
        const idPedido = selectPedido.value;

        if (!idRepartidor || !idPedido) {

            alert(
                "Seleccione un repartidor y un pedido."
            );

            return;
        }

        const datosFormulario = new FormData(formulario);

        fetch(formulario.action, {
            method: "POST",
            body: datosFormulario
        })
            .then(response => response.text())
            .then(respuesta => {

                console.log("Respuesta PHP:", respuesta);

                alert(respuesta);

                if (
                    respuesta.includes(
                        "Tarea asignada correctamente"
                    )
                ) {

                    formulario.reset();

                    // Volver a cargar los datos
                    location.reload();
                }

            })
            .catch(error => {

                console.error(
                    "Error al asignar la tarea:",
                    error
                );

                alert(
                    "Ocurrió un error al asignar la tarea."
                );
            });

    });

});
