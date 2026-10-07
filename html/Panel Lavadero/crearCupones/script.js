document.addEventListener("DOMContentLoaded", function () {

    const btnCrearCupon = document.getElementById("btnCrearCupon");
    const inputCodigo = document.getElementById("inputCodigo");
    const inputValor = document.getElementById("inputValor");
    const inputFecha = document.getElementById("inputFecha");
    const tablaCupones = document.getElementById("tablaCupones");

    cargarCupones();


    btnCrearCupon.addEventListener("click", function () {

        const codigo = inputCodigo.value.trim();
        const valor = inputValor.value;
        const fecha = inputFecha.value;

        if (codigo === "") {
            alert("Ingresá el código del cupón.");
            return;
        }

        if (valor === "") {
            alert("Ingresá el valor del descuento.");
            return;
        }

        if (Number(valor) <= 0) {
            alert("El descuento debe ser mayor a 0.");
            return;
        }

        if (fecha === "") {
            alert("Seleccioná la fecha de vencimiento.");
            return;
        }

        const datos = new FormData();

        datos.append("codigo", codigo);
        datos.append("valor", valor);
        datos.append("fecha_vencimiento", fecha);

        fetch("../../conexiones/crearCupon.php", {
            method: "POST",
            body: datos
        })

        .then(response => response.text())

        .then(resultado => {

            alert(resultado);

            if (resultado.trim() === "Cupón creado correctamente.") {

                inputCodigo.value = "";
                inputValor.value = "";
                inputFecha.value = "";

                cargarCupones();
            }

        })

        .catch(error => {

            console.error(
                "Error al crear el cupón:",
                error
            );

        });

    });



    function cargarCupones() {

        fetch("../../conexiones/cargarDatosCupones.php")

        .then(response => {

            if (!response.ok) {
                throw new Error(
                    "Error HTTP: " + response.status
                );
            }

            return response.json();

        })

        .then(cupones => {

            mostrarCupones(cupones);

        })

        .catch(error => {

            console.error(
                "Error al cargar los cupones:",
                error
            );

        });

    }



    function mostrarCupones(cupones) {

        tablaCupones.innerHTML = "";

        cupones.forEach(cupon => {

            const fila = document.createElement("tr");

            fila.innerHTML = `
                <td>
                    ${cupon.id_cupon}
                </td>

                <td>
                    ${cupon.codigo}
                </td>

                <td>
                    ${cupon.valor_descuento}%
                </td>

                <td>
                    ${cupon.fecha_vencimiento}
                </td>

                <td>
                    ${cupon.estado}
                </td>

                <td class="acciones">

                    <button
                        class="btnMenu"
                        onclick="mostrarMenu(this)">
                        ⋮
                    </button>

                    <div class="menuAcciones">

                        <button
                            onclick="editarCupon(${cupon.id_cupon})">
                            Editar
                        </button>

                        <button
                            onclick="cancelarCupon(${cupon.id_cupon})">
                            Cancelar
                        </button>

                    </div>

                </td>
            `;

            tablaCupones.appendChild(fila);

        });


        if (cupones.length === 0) {

            tablaCupones.innerHTML = `
                <tr>
                    <td colspan="6">
                        No hay cupones registrados.
                    </td>
                </tr>
            `;

        }

    }



    window.editarCupon = function (idCupon) {

        window.location.href =
            "modificarCupon.html?id=" + idCupon;

    };


  
    window.cancelarCupon = function (idCupon) {

        const confirmar = confirm(
            "¿Querés desactivar este cupón?"
        );

        if (!confirmar) {
            return;
        }

        const datos = new FormData();

        datos.append(
            "id_cupon",
            idCupon
        );

        fetch("../../conexiones/desactivarCupon.php", {
            method: "POST",
            body: datos
        })

        .then(response => response.text())

        .then(resultado => {

            alert(resultado);

            cargarCupones();

        })

        .catch(error => {

            console.error(
                "Error al desactivar el cupón:",
                error
            );

            alert("Error al desactivar el cupón.");

        });

    };



    window.mostrarMenu = function (boton) {

        const menu =
            boton.parentElement.querySelector(".menuAcciones");

        document.querySelectorAll(".menuAcciones").forEach(elemento => {

            if (elemento !== menu) {

                elemento.classList.remove("mostrar");

            }

        });

        menu.classList.toggle("mostrar");

    };


    document.addEventListener("click", function (event) {

        if (!event.target.closest(".acciones")) {

            document.querySelectorAll(".menuAcciones").forEach(menu => {

                menu.classList.remove("mostrar");

            });

        }

    });

});
