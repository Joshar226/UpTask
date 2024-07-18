(function() {

    obtenerTareas()
    let tareas = []
    let filtradas = []
    
    //Boton para mostrar el modal
    const nuevaTareaBtn = document.querySelector('#agregar-tarea')  
    nuevaTareaBtn.addEventListener('click', function() {
        mostrarFormulario()
    })

    //Filtros busqueda
    const filtros = document.querySelectorAll('#filtros input[type="radio"]')
    filtros.forEach(filtro => {
        filtro.addEventListener('input', filtrarTareas)
    });

    function filtrarTareas(e) {
        const filtro = e.target.value

        if(filtro !== '') {
            filtradas = tareas.filter( tarea => tarea.estado === filtro)
        } else {
            filtradas = []
        }
        mostrarTareas()
    }


    async function obtenerTareas() {
        try {
            const id = obtenerProyecto();
            const url = `/api/tareas?url=${id}`
            const respuesta = await fetch(url)

            const resultado = await respuesta.json()

            tareas = resultado.tareas

            mostrarTareas();

        } catch (error) {
            console.log(error);
        }
    }

    function mostrarTareas() {
        limpiarTareas();
        totalPendintes()
        totalCompletas()

        const arrayTareas = filtradas.length ? filtradas : tareas

        if(arrayTareas.length === 0 ) {
            const contenedorTareas = document.querySelector('#listado-tareas')

            const noTareas = document.createElement('LI')
            noTareas.textContent = 'No hay tareas'
            noTareas.classList.add('no-tareas')

            contenedorTareas.appendChild(noTareas)
            return
        }

        const estados = {
            0: 'Pendiente',
            1: 'Completa'
        }
        arrayTareas.forEach(tarea => {
            const contenedorTarea = document.createElement("LI")
            contenedorTarea.dataset.tareaId = tarea.id
            contenedorTarea.classList.add('tarea')

            const nombreTarea = document.createElement('P')
            nombreTarea.textContent = tarea.nombre
            nombreTarea.onclick = function() {
                mostrarFormulario(true, {...tarea});
            }

            const opcionesDiv = document.createElement('DIV')
            opcionesDiv.classList.add('opciones')

            //botones
            const btnEstadoTarea = document.createElement('BUTTON')
            btnEstadoTarea.classList.add('estado-tarea')
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`)
            btnEstadoTarea.textContent = estados[tarea.estado]
            btnEstadoTarea.dataset.estadoTarea = tarea.estado
            btnEstadoTarea.onclick = function() {
                cambiarEstadoTarea({...tarea})
            }

            const btnEliminarTarea = document.createElement('BUTTON')
            btnEliminarTarea.classList.add('eliminar-tarea')
            btnEliminarTarea.dataset.idTarea = tarea.id
            btnEliminarTarea.textContent = 'Eliminar'
            btnEliminarTarea.onclick = function() {
                confirmarEliminarTarea({...tarea})
            }

            opcionesDiv.appendChild(btnEstadoTarea)
            opcionesDiv.appendChild(btnEliminarTarea)

            contenedorTarea.appendChild(nombreTarea)
            contenedorTarea.appendChild(opcionesDiv)

            const listadoTareas = document.querySelector('#listado-tareas')
            listadoTareas.appendChild(contenedorTarea)
        });


    }

    function totalPendintes() {
        const totalPendintes = tareas.filter(tarea => tarea.estado === "0")
        const pendientesRadio = document.querySelector('#pendientes')

        if(totalPendintes.length === 0) {
            pendientesRadio.disabled = true
        } else {
            pendientesRadio.disabled = false
        }
    }

    function totalCompletas() {
        const totalCompletas = tareas.filter(tarea => tarea.estado === "1")
        const completasRadio = document.querySelector('#completadas')

        if(totalCompletas.length === 0) {
            completasRadio.disabled = true
        } else {
            completasRadio.disabled = false
        }
    }

    function mostrarFormulario(editar = false, tarea = {}) {
        const modal = document.createElement('DIV')
        modal.classList.add('modal')
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>${editar ? 'Editar Tarea' : 'Añade una nueva tarea'}</legend>
                <div class="campo">
                    <label>Tarea</label>
                    <input 
                        type="text"
                        name="tarea" 
                        placeholder="${tarea.nombre ? 'Edita la Tarea' : 'Añadir Tarea al Proyecto Actual'}" 
                        value="${tarea.nombre ? tarea.nombre : ''}"  
                        id="tarea">
                </div>

                <div class="opciones">
                    <input type="submit" class="submit-nueva-tarea" value="${tarea.nombre ? 'Editar Tarea' : 'Añadir Tarea'}" Tarea">
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        `

        setTimeout(() => {
            const formulario = document.querySelector('.formulario')
            formulario.classList.add('animar')
        }, 0);

        document.querySelector('.dashboard').appendChild(modal)
        

        modal.addEventListener('click', function(e) {
            e.preventDefault();

            if(e.target.classList.contains('cerrar-modal')) {
                const formulario = document.querySelector('.formulario')
                formulario.classList.add('cerrar')
                setTimeout(() => {
                    modal.remove();
                }, 500);

            }

            if(e.target.classList.contains('submit-nueva-tarea')) {
                const nombreTarea = document.querySelector('#tarea').value.trim()

                //tarea esta vacia
                if (nombreTarea === '') {
                    //Alerta de error
                    mostrarAlerta('El nombre de la tarea no puede ir vacio', 'error', '.formulario legend')
                    return
                } 

                if(editar) {
                    tarea.nombre = nombreTarea
                    actualizarTarea(tarea)
                } else {
                    agregarTarea(nombreTarea)
                }
            }
        })


        async function agregarTarea(tarea) {
            limpiarTareas();
            //Construir la peticion
            const datos = new FormData();
            datos.append('nombre', tarea)
            datos.append('proyectoId', obtenerProyecto())

            try {
                const url = '/api/tarea'
                const respuesta = await fetch(url, {
                    method: 'POST',
                    body: datos
                })

                const resultado = await respuesta.json()

                mostrarAlerta(resultado.mensaje, resultado.tipo, '.formulario legend')

                if(resultado.tipo === 'exito') {
                    const modal = document.querySelector('.modal')
                    setTimeout(() => {
                        modal.remove()
                    }, 2500);

                    //agregar el objeto de tareas al global de tareas

                    const tareaObj = {
                        id: String(resultado.id),
                        nombre: tarea,
                        estado: "0",
                        proyectoId: resultado.id
                    }

                    tareas = [...tareas, tareaObj]
                    mostrarTareas();
                }

            } catch (error) {
                console.log(error);
            }  
        }
    }

    function cambiarEstadoTarea(tarea) {
        const nuevoEstado = tarea.estado === "1" ? "0" : "1"
        tarea.estado = nuevoEstado
        actualizarTarea(tarea)
    }

    async function actualizarTarea(tarea) {
        const {estado, id, nombre} = tarea

        const datos = new FormData()
        datos.append('id', id)
        datos.append('nombre', nombre)
        datos.append('estado', estado)
        datos.append('proyectoId', obtenerProyecto())

        try {
            const url = '/api/tarea/actualizar'
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            })
            const resultado = await respuesta.json()
            
            if(resultado.respuesta.tipo === "exito") {
                Swal.fire({
                    title: "Actualizado!",
                    icon: "success"
                });

                const modal = document.querySelector('.modal')
                if(modal) {
                    modal.remove()
                }


                tareas = tareas.map(tareaMemoria => {
                    if(tareaMemoria.id === id) {
                        tareaMemoria.estado = estado
                        tareaMemoria.nombre = nombre
                    }
                    return tareaMemoria
                })

                mostrarTareas();
            }
        } catch (error) {
            console.log(error);
        }
    }

    function confirmarEliminarTarea(tarea) {
        Swal.fire({
            title: "Eliminar Tarea?",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "No"
          }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                eliminarTarea(tarea)
            }
        });
    }

    async function eliminarTarea(tarea) {
        const {estado, id, nombre} = tarea

        const datos = new FormData()
        datos.append('id', id)
        datos.append('nombre', nombre)
        datos.append('estado', estado)
        datos.append('proyectoId', obtenerProyecto())

        try {
            const url = '/api/tarea/eliminar'
            const respuesta = await fetch(url, {
                method: 'post',
                body: datos
            })

            const resultado = await respuesta.json()
            
            if(resultado.resultado) {
                // mostrarAlerta(resultado.mensaje, resultado.tipo, '.contenedor-nueva-tarea')
                Swal.fire({
                    title: "Eliminado!",
                    icon: "success"
                });
            }

            tareas = tareas.filter( tareaMemoria => tareaMemoria.id !== tarea.id)
            mostrarTareas();

        } catch (error) {
            console.log(error);
        }
    }


    //muestra un msj en interfaz
    function mostrarAlerta(mensaje, tipo, referencia) {
        //Previene muchas alertas
        const alertaPrevia = document.querySelector('.alerta')

        if(alertaPrevia) {
            alertaPrevia.remove()
        }

        const alerta = document.createElement('DIV')
        alerta.classList.add('alerta', tipo)
        alerta.textContent = mensaje

        const ref = document.querySelector(referencia)
        ref.parentElement.insertBefore(alerta, ref.nextElementSibling)

        setTimeout(() => {
            alerta.remove()
        }, 3500);
    }
    
    function obtenerProyecto() {
        const proyectoParams = new URLSearchParams(window.location.search)
        const proyecto = Object.fromEntries(proyectoParams.entries())
        return proyecto.url
    }

    function limpiarTareas() {
        const listadoTareas = document.querySelector('#listado-tareas')
        
        while(listadoTareas.firstChild) {
            listadoTareas.removeChild(listadoTareas.firstChild)
        }
    }
})();