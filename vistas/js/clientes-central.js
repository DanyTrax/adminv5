$(document).ready(function() {
    
    // Variables globales
    var clientesCentrales = [];
    var sucursalesDisponibles = [];
    
    // Inicializar la interfaz
    inicializarInterfaz();
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Cargar sucursales disponibles
    cargarSucursalesDisponibles();
    
    // Cargar clientes centrales
    cargarClientesCentrales();
    
    // Event listeners
    $(document).on("click", "#btnNuevoClienteCentral", function() {
        abrirModalCliente();
    });
    
    $(document).on("click", "#btnSincronizarTodosClientes", function() {
        sincronizarTodosClientes();
    });
    
    $(document).on("click", ".btnEditarCliente", function() {
        var idCliente = $(this).data("id");
        editarCliente(idCliente);
    });
    
    $(document).on("click", ".btnEliminarCliente", function() {
        var idCliente = $(this).data("id");
        var nombreCliente = $(this).data("nombre");
        eliminarCliente(idCliente, nombreCliente);
    });
    
    $(document).on("click", "#btnConfirmarEliminacionCliente", function() {
        var idCliente = $("#idClienteEliminar").val();
        confirmarEliminacionCliente(idCliente);
    });
    
    // Validación en tiempo real
    $(document).on("input", "#documentoCliente", function() {
        var documento = $(this).val();
        if (documento.length >= 6) {
            verificarDuplicadoCliente();
        }
    });
    
    $(document).on("input", "#emailCliente", function() {
        var email = $(this).val();
        if (email.length > 0) {
            verificarDuplicadoCliente();
        }
    });
    
    // Submit del formulario
    $(document).on("submit", "#formClienteCentral", function(e) {
        e.preventDefault();
        guardarCliente();
    });
    
    // Función para inicializar la interfaz
    function inicializarInterfaz() {
        console.log("Interfaz de clientes centrales inicializada");
    }
    
    // Función para cargar estadísticas
    function cargarEstadisticas() {
        // Aquí puedes cargar estadísticas del sistema
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_estadisticas" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    $("#totalClientesCentral").text(respuesta.total_clientes || 0);
                    $("#sucursalesActivas").text(respuesta.sucursales_activas || 0);
                    $("#clientesUnicos").text(respuesta.clientes_unicos || 0);
                    $("#duplicadosDetectados").text(respuesta.duplicados || 0);
                }
            },
            error: function() {
                console.error("Error cargando estadísticas");
            }
        });
    }
    
    // Función para cargar sucursales disponibles
    function cargarSucursalesDisponibles() {
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_sucursales_disponibles" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    sucursalesDisponibles = respuesta.sucursales;
                    console.log("Sucursales disponibles cargadas:", sucursalesDisponibles);
                }
            },
            error: function() {
                console.error("Error cargando sucursales disponibles");
            }
        });
    }
    
    // Función para cargar clientes centrales
    function cargarClientesCentrales() {
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: { accion: "obtener_clientes_centrales" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    clientesCentrales = respuesta.clientes;
                    mostrarClientesCentrales(clientesCentrales);
                }
            },
            error: function() {
                console.error("Error cargando clientes centrales");
            }
        });
    }
    
    // Función para mostrar clientes centrales en la tabla
    function mostrarClientesCentrales(clientes) {
        var html = "";
        
        clientes.forEach(function(cliente, index) {
            var sucursalesAsignadas = cliente.sucursales_asignadas ? cliente.sucursales_asignadas.split(',') : [];
            var sucursalesNombres = sucursalesAsignadas.map(function(id) {
                var sucursal = sucursalesDisponibles.find(function(s) {
                    return s.id == id;
                });
                return sucursal ? sucursal.nombre : id;
            }).join(', ');
            
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.documento}</td>
                    <td>${cliente.email || '-'}</td>
                    <td>${cliente.telefono || '-'}</td>
                    <td>${cliente.sucursal_origen || '-'}</td>
                    <td>
                        ${sucursalesNombres || 'Sin asignar'}
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-warning btnEditarCliente" data-id="${cliente.id_central}" title="Editar Cliente">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn btn-danger btnEliminarCliente" data-id="${cliente.id_central}" data-nombre="${cliente.nombre}" title="Eliminar Cliente">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        $("#tbodyClientesCentral").html(html);
        
        // Inicializar DataTable si no está inicializado
        if (!$.fn.DataTable.isDataTable('#tablaClientesCentral')) {
            $('#tablaClientesCentral').DataTable({
                "language": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });
        }
    }
    
    // Función para abrir modal de cliente
    function abrirModalCliente(cliente = null) {
        $("#formClienteCentral")[0].reset();
        $("#esEdicionCliente").val("0");
        $("#idClienteCentral").val("");
        
        if (cliente) {
            // Modo edición
            $("#tituloModalCliente").text("Editar Cliente Central");
            $("#esEdicionCliente").val("1");
            $("#idClienteCentral").val(cliente.id_central);
            $("#documentoCliente").val(cliente.documento);
            $("#emailCliente").val(cliente.email);
            $("#nombreCliente").val(cliente.nombre);
            $("#telefonoCliente").val(cliente.telefono);
            $("#direccionCliente").val(cliente.direccion);
            $("#fechaNacimientoCliente").val(cliente.fecha_nacimiento);
            
            // Cargar sucursales asignadas
            cargarSucursalesAsignadas(cliente.sucursales_asignadas);
        } else {
            // Modo creación
            $("#tituloModalCliente").text("Crear Cliente Central");
            cargarSucursalesAsignadas("");
        }
        
        $("#modalClienteCentral").modal("show");
    }
    
    // Función para cargar sucursales asignadas
    function cargarSucursalesAsignadas(sucursalesAsignadas) {
        var html = "";
        var sucursalesArray = sucursalesAsignadas ? sucursalesAsignadas.split(',') : [];
        
        sucursalesDisponibles.forEach(function(sucursal) {
            var checked = sucursalesArray.includes(sucursal.id.toString()) ? 'checked' : '';
            html += `
                <div class="col-md-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" class="checkbox-sucursal-cliente" value="${sucursal.id}" ${checked}>
                            ${sucursal.nombre}
                        </label>
                    </div>
                </div>
            `;
        });
        
        $("#sucursalesAsignadasCliente").html(html);
    }
    
    // Función para verificar duplicado de cliente
    function verificarDuplicadoCliente() {
        var documento = $("#documentoCliente").val();
        var email = $("#emailCliente").val();
        var esEdicion = $("#esEdicionCliente").val() == "1";
        
        if (documento.length < 6 && (!email || email.length == 0)) {
            return;
        }
        
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: {
                accion: "verificar_duplicado_cliente",
                documento: documento,
                email: email,
                es_edicion: esEdicion,
                id_central: $("#idClienteCentral").val()
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.existe) {
                    mostrarErrorDuplicado(respuesta);
                } else {
                    limpiarErroresDuplicado();
                }
            },
            error: function() {
                console.error("Error verificando duplicado");
            }
        });
    }
    
    // Función para mostrar error de duplicado
    function mostrarErrorDuplicado(respuesta) {
        var mensaje = `Este ${respuesta.campo} ya existe en el cliente: ${respuesta.cliente.nombre} (Sucursal: ${respuesta.cliente.sucursal_origen})`;
        
        if (respuesta.campo == 'documento') {
            $("#errorDocumento").text(mensaje).show();
        } else if (respuesta.campo == 'email') {
            $("#errorEmail").text(mensaje).show();
        }
    }
    
    // Función para limpiar errores de duplicado
    function limpiarErroresDuplicado() {
        $("#errorDocumento").hide();
        $("#errorEmail").hide();
    }
    
    // Función para guardar cliente
    function guardarCliente() {
        var formData = {
            nombre: $("#nombreCliente").val(),
            documento: $("#documentoCliente").val(),
            email: $("#emailCliente").val(),
            telefono: $("#telefonoCliente").val(),
            direccion: $("#direccionCliente").val(),
            fecha_nacimiento: $("#fechaNacimientoCliente").val(),
            sucursales_asignadas: obtenerSucursalesSeleccionadas().join(','),
            es_edicion: $("#esEdicionCliente").val(),
            id_central: $("#idClienteCentral").val()
        };
        
        // Validar campos requeridos
        if (!formData.nombre || !formData.documento) {
            alert("Por favor completa todos los campos requeridos");
            return;
        }
        
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: {
                accion: formData.es_edicion == "1" ? "editar_cliente_central" : "crear_cliente_central",
                datos: JSON.stringify(formData)
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    alert("Cliente guardado exitosamente");
                    $("#modalClienteCentral").modal("hide");
                    cargarClientesCentrales();
                    cargarEstadisticas();
                } else {
                    alert("Error: " + respuesta.error);
                }
            },
            error: function() {
                alert("Error de conexión al guardar cliente");
            }
        });
    }
    
    // Función para obtener sucursales seleccionadas
    function obtenerSucursalesSeleccionadas() {
        var sucursales = [];
        $(".checkbox-sucursal-cliente:checked").each(function() {
            sucursales.push($(this).val());
        });
        return sucursales;
    }
    
    // Función para editar cliente
    function editarCliente(idCliente) {
        var cliente = clientesCentrales.find(function(c) {
            return c.id_central == idCliente;
        });
        
        if (cliente) {
            abrirModalCliente(cliente);
        }
    }
    
    // Función para eliminar cliente
    function eliminarCliente(idCliente, nombreCliente) {
        $("#idClienteEliminar").val(idCliente);
        $("#nombreClienteEliminar").text(nombreCliente);
        $("#modalConfirmarEliminacionCliente").modal("show");
    }
    
    // Función para confirmar eliminación
    function confirmarEliminacionCliente(idCliente) {
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: {
                accion: "eliminar_cliente_central",
                id_central: idCliente
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    alert("Cliente eliminado exitosamente");
                    $("#modalConfirmarEliminacionCliente").modal("hide");
                    cargarClientesCentrales();
                    cargarEstadisticas();
                } else {
                    alert("Error: " + respuesta.error);
                }
            },
            error: function() {
                alert("Error de conexión al eliminar cliente");
            }
        });
    }
    
    // Función para sincronizar todos los clientes
    function sincronizarTodosClientes() {
        console.log("sincronizarTodosClientes llamada");
        swal({
            title: "¿Importar clientes desde sucursales?",
            text: "Esta acción traerá los clientes de todas las sucursales activas al sistema central, evitando duplicados por documento.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5cb85c",
            confirmButtonText: "Sí, importar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        }, function(isConfirm) {
            console.log("SweetAlert confirmado:", isConfirm);
            if (isConfirm) {
                console.log("Iniciando importación...");
                iniciarImportacion();
            }
        });
    }
    
    // Función para iniciar la importación
    function iniciarImportacion() {
        console.log("iniciarImportacion llamada");
        
        // Mostrar modal de progreso
        $("#estadoImportacion").text("Conectando a sucursales...");
        $("#clientesImportados").text("0");
        $("#clientesDuplicados").text("0");
        $("#progresoSucursales").html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Iniciando importación...</p>');
        $("#modalFooterImportacion").hide();
        $("#modalProgresoImportacion").modal("show");
        
        console.log("Enviando AJAX...");
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: { accion: "sincronizar_todos_clientes" },
            dataType: "json",
            success: function(respuesta) {
                console.log("Respuesta AJAX:", respuesta);
                if (respuesta.success) {
                    // Actualizar estadísticas
                    $("#estadoImportacion").text("✅ Importación completada");
                    $("#clientesImportados").text(respuesta.clientes_importados || 0);
                    $("#clientesDuplicados").text(respuesta.clientes_duplicados || 0);
                    
                    // Mostrar resultados por sucursal
                    var html = "";
                    if (respuesta.resultados) {
                        for (var sucursalId in respuesta.resultados) {
                            var resultado = respuesta.resultados[sucursalId];
                            html += `
                                <div class="alert alert-info">
                                    <strong>${resultado.sucursal}</strong><br>
                                    ✅ Importados: ${resultado.clientes_importados} | 
                                    ⚠️ Duplicados: ${resultado.clientes_duplicados}
                                </div>
                            `;
                        }
                    }
                    $("#progresoSucursales").html(html);
                    
                    // Mostrar botón de cerrar
                    $("#modalFooterImportacion").show();
                    
                    // Actualizar tabla después de cerrar modal
                    setTimeout(function() {
                        cargarClientesCentrales();
                        cargarEstadisticas();
                    }, 500);
                } else {
                    $("#estadoImportacion").text("❌ Error en la importación");
                    $("#progresoSucursales").html('<div class="alert alert-danger">' + respuesta.error + '</div>');
                    $("#modalFooterImportacion").show();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", xhr, status, error);
                $("#estadoImportacion").text("❌ Error de conexión");
                $("#progresoSucursales").html('<div class="alert alert-danger">Error de conexión durante la importación</div>');
                $("#modalFooterImportacion").show();
            }
        });
    }
});
