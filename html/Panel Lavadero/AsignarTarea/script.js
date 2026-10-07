fetch("../../conexiones/cargarDatosAsignarTareas.php")

    .then(response => response.json())

    .then(datos => {

        const selectRepartidor =document.getElementById("Repartidor");

        const selectPedido = document.getElementById("Pedido");

        const listaRepartidores = document.getElementById("listaRepartidores");


        datos.repartidores.forEach(repartidor => {

            const opcion =document.createElement("option");

            opcion.value =repartidor.id_repartidor;

            opcion.textContent =repartidor.nombreCompleto;

            selectRepartidor.appendChild(opcion);

            const fila = document.createElement("div");

            fila.classList.add("repartidorDisponible");


            const nombre = document.createElement("span");

            nombre.textContent = repartidor.nombreCompleto;


            const boton = document.createElement("button");

            boton.textContent = "Asignar";

            boton.type = "button";

            boton.addEventListener(
                "click",
                function() {

                    selectRepartidor.value =
                        repartidor.id_repartidor;

                }
            );


            fila.appendChild(nombre);

            fila.appendChild(boton);

            listaRepartidores.appendChild(fila);

        });



        datos.pedidos.forEach(pedido => {

            const opcion = document.createElement("option");

            opcion.value = pedido.id_pedido;

            opcion.textContent = "Pedido #" + pedido.id_pedido + " - " + pedido.estado;

            selectPedido.appendChild(opcion);

        });

    })


    .catch(error => {

        console.error(
            "Error al cargar los datos:",
            error
        );

    });




document.addEventListener(
    "DOMContentLoaded",
    () => {

        const form = document.querySelector("form");


        if (!form) {
            return;
        }


        form.addEventListener(
            "submit",
            async function(e) {

                e.preventDefault();


                const submitBtn =
                    document.getElementById("Boton");


                submitBtn.disabled = true;

                submitBtn.textContent =
                    "Asignando...";


                const formData =
                    new FormData(form);


                try {

                    const url = form.getAttribute("action");
                    const response =
                        await fetch(
                            url,
                            {
                                method: "POST",
                                body: formData
                            }
                        );


                    if (!response.ok) {

                        throw new Error(
                            "Error HTTP: " +
                            response.status
                        );

                    }

                    const resultado = await response.text();


                    console.log(
                        "Respuesta PHP:",
                        resultado
                    );


                    alert(resultado);

                    if ( resultado.includes("correctamente")) {

                        form.reset();

                    }

                }


                catch(error) {

                    console.error(
                        "Error al asignar la tarea:",
                        error
                    );


                    alert(
                        "Ocurrió un error al intentar asignar la tarea."
                    );

                }


                finally {

                    submitBtn.disabled = false;

                    submitBtn.textContent = "Asignar Tarea";

                }

            }
        );

    }
);
