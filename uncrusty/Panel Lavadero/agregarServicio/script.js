document.addEventListener("DOMContentLoaded", function () {

    const inputNombre = document.getElementById("inputNombre");
    const selectCategoria = document.getElementById("selectCategoria");
    const inputPrecio = document.getElementById("inputPrecio");
    const btnCrearServicio = document.getElementById("btnCrearServicio");
    const tablaServicios = document.getElementById("tablaServicios");

    cargarServicios();


    // CREAR SERVICIO
    btnCrearServicio.addEventListener("click", function () {

        const nombre = inputNombre.value.trim();
        const categoria = selectCategoria.value;
        const precio = inputPrecio.value;

        if (nombre === "") {
            alert("Ingresá el nombre del servicio.");
            return;
        }

        if (categoria === "") {
            alert("Seleccioná una categoría.");
            return;
        }

        if (precio === "") {
            alert("Ingresá el precio del servicio.");
            return;
        }

        if (Number(precio) < 0) {
            alert("El precio no puede ser negativo.");
            return;
        }

        const datos = new FormData();

        datos.append("nombre", nombre);
        datos.append("categoria", categoria);
        datos.append("precio", precio);

        fetch("../../conexiones/crearServicio.php", {
            method: "POST",
            body: datos
        })
        .then(response => response.text())
        .then(resultado => {

            alert(resultado);

            if (resultado.trim() === "Servicio creado correctamente.") {

                inputNombre.value = "";
                selectCategoria.value = "";
                inputPrecio.value = "";

                cargarServicios();
            }

        })
        .catch(error => {

            console.error(
                "Error al crear el servicio:",
                error
            );

        });

    });


    // CARGAR SERVICIOS
    function cargarServicios() {

        fetch("../../conexiones/cargarDatosServicios.php")

        .then(response => {

            if (!response.ok) {

                throw new Error(
                    "Error HTTP: " + response.status
                );

            }

            return response.json();

        })

        .then(servicios => {

            mostrarServicios(servicios);

        })

        .catch(error => {

            console.error(
                "Error al cargar los servicios:",
                error
            );

        });

    }


    // MOSTRAR SERVICIOS
    function mostrarServicios(servicios) {

        tablaServicios.innerHTML = "";

        const serviciosActivos = servicios.filter(
            servicio => servicio.estado === "Activo"
        );


        serviciosActivos.forEach(servicio => {

            const fila = document.createElement("tr");

            fila.innerHTML = `
                <td>
                    ${servicio.id_servicio}
                </td>

                <td>
                    ${servicio.nombre}
                </td>

                <td>
                    ${servicio.categoria}
                </td>

                <td>
                    $${servicio.precio}
                </td>

                <td class="acciones">

                    <button
                        class="btnMenu"
                        onclick="mostrarMenu(this)">
                        ⋮
                    </button>

                    <div class="menuAcciones">

                        <button
                            onclick="editarServicio(${servicio.id_servicio})">
                            Editar
                        </button>

                        <button
                            onclick="cancelarServicio(${servicio.id_servicio})">
                            Cancelar
                        </button>

                    </div>

                </td>
            `;

            tablaServicios.appendChild(fila);

        });


        if (serviciosActivos.length === 0) {

            tablaServicios.innerHTML = `
                <tr>
                    <td colspan="5">
                        No hay servicios activos.
                    </td>
                </tr>
            `;

        }

    }

});


// EDITAR SERVICIO
function editarServicio(idServicio) {

    window.location.href =
        "modificarServicio.html?id=" + idServicio;

}


// DESACTIVAR SERVICIO
function cancelarServicio(idServicio) {

    const confirmar = confirm(
        "¿Querés desactivar este servicio?"
    );

    if (!confirmar) {
        return;
    }

    const datos = new FormData();

    datos.append(
        "id_servicio",
        idServicio
    );

    fetch("../../conexiones/desactivarServicio.php", {
        method: "POST",
        body: datos
    })

    .then(response => response.text())

    .then(resultado => {

        alert(resultado);

        location.reload();

    })

    .catch(error => {

        console.error(
            "Error al desactivar el servicio:",
            error
        );

    });

}


// MOSTRAR MENÚ
function mostrarMenu(boton) {

    const menu =
        boton.parentElement.querySelector(".menuAcciones");

    document.querySelectorAll(".menuAcciones").forEach(elemento => {

        if (elemento !== menu) {

            elemento.classList.remove("mostrar");

        }

    });

    menu.classList.toggle("mostrar");

}