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

        datos.append(
            "codigo",
            codigo
        );

        datos.append(
            "valor",
            valor
        );

        datos.append(
            "fecha_vencimiento",
            fecha
        );


        fetch("../../conexiones/crearCupon.php", {

            method: "POST",

            body: datos

        })

        .then(response => response.text())

        .then(resultado => {

            alert(resultado);



            if ( resultado ==="Cupón creado correctamente.") {

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

            .then(response => response.json())

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

                <td>

                    <button
                        onclick="desactivarCupon(${cupon.id_cupon})">
                        Desactivar
                    </button>

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

});
function desactivarCupon(idCupon) {

    const confirmar = confirm("¿Querés desactivar este cupón?" );


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

        location.reload();

    })

    .catch(error => {

        console.error(
            "Error al desactivar el cupón:",
            error
        );

    });

}