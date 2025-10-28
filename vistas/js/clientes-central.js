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
            // Modo creación - establecer fecha actual
            $("#tituloModalCliente").text("Crear Cliente Central");
            var fechaActual = new Date().toISOString().split('T')[0];
            $("#fechaNacimientoCliente").val(fechaActual);
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
        
        if (!confirm("¿Estás seguro de importar todos los clientes desde las sucursales activas?\n\nEsta acción traerá los clientes de todas las sucursales al sistema central, evitando duplicados por documento.")) {
            return;
        }
        
        console.log("Confirmación aceptada, iniciando importación...");
        iniciarImportacion();
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
    
    // ========================================
    // NUEVOS EVENT LISTENERS PARA BOTONES
    // ========================================
    
    // Botón Sincronización Bidireccional
    $(document).on("click", "#btnSincronizacionBidireccional", function() {
        abrirModalSincronizacionBidireccional();
    });
    
    // Botón Copiar a Sucursal
    $(document).on("click", "#btnCopiarACentral", function() {
        abrirModalCopiarASucursal();
    });
    
    // Botón Borrar Clientes
    $(document).on("click", "#btnBorrarClientes", function() {
        abrirModalBorrarClientes();
    });
    
    // Cambio en select de origen para borrar
    $(document).on("change", "#selectOrigenBorrar", function() {
        var origen = $(this).val();
        if (origen === "sucursal") {
            $("#divSucursalBorrar").show();
            cargarSucursalesParaBorrar();
        } else {
            $("#divSucursalBorrar").hide();
        }
    });
    
    // Cambio en select de dirección para copiar
    $(document).on("change", "#selectDireccionCopiar", function() {
        var direccion = $(this).val();
        if (direccion === "central_a_sucursal" || direccion === "sucursal_a_central") {
            $("#divSucursalCopiar").show();
            cargarSucursalesDestino();
        } else {
            $("#divSucursalCopiar").hide();
        }
    });
    
    // Confirmar Sincronización Bidireccional
    $(document).on("click", "#btnGuardarSincronizacionBidireccional", function() {
        guardarSincronizacionBidireccional();
    });
    
    // Confirmar Copiar a Sucursal
    $(document).on("click", "#btnConfirmarCopiarASucursal", function() {
        confirmarCopiarASucursal();
    });
    
    // Confirmar Borrar Clientes
    $(document).on("click", "#btnConfirmarBorrarClientes", function() {
        confirmarBorrarClientes();
    });
    
    // ========================================
    // FUNCIONES PARA NUEVOS MODALES
    // ========================================
    
    function abrirModalSincronizacionBidireccional() {
        cargarSucursalesBidireccional();
        $("#modalSincronizacionBidireccional").modal("show");
    }
    
    function abrirModalCopiarASucursal() {
        $("#selectDireccionCopiar").val("");
        $("#divSucursalCopiar").hide();
        $("#modalCopiarASucursal").modal("show");
    }
    
    function abrirModalBorrarClientes() {
        $("#selectOrigenBorrar").val("");
        $("#divSucursalBorrar").hide();
        $("#modalBorrarClientes").modal("show");
    }
    
    function cargarSucursalesBidireccional() {
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: {
                accion: "obtenerSucursalesBidireccional"
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var html = "";
                    respuesta.sucursales.forEach(function(sucursal) {
                        html += `
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="${sucursal.id}" ${sucursal.sincronizada ? 'checked' : ''}>
                                    <strong>${sucursal.nombre}</strong> - ${sucursal.direccion}
                                </label>
                            </div>
                        `;
                    });
                    $("#listaSucursalesBidireccional").html(html);
                } else {
                    swal("Error", "No se pudieron cargar las sucursales", "error");
                }
            },
            error: function() {
                swal("Error", "Error de conexión", "error");
            }
        });
    }
    
    function cargarSucursalesDestino() {
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: {
                accion: "obtenerSucursalesDestino"
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var html = '<option value="">Selecciona una sucursal...</option>';
                    respuesta.sucursales.forEach(function(sucursal) {
                        html += `<option value="${sucursal.id}">${sucursal.nombre}</option>`;
                    });
                    $("#selectSucursalDestino").html(html);
                } else {
                    swal("Error", "No se pudieron cargar las sucursales", "error");
                }
            },
            error: function() {
                swal("Error", "Error de conexión", "error");
            }
        });
    }
    
    function cargarSucursalesParaBorrar() {
        $.ajax({
            url: "ajax/clientes-central.ajax.php",
            method: "POST",
            data: {
                accion: "obtenerSucursalesParaBorrar"
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var html = '<option value="">Selecciona una sucursal...</option>';
                    respuesta.sucursales.forEach(function(sucursal) {
                        html += `<option value="${sucursal.id}">${sucursal.nombre}</option>`;
                    });
                    $("#selectSucursalBorrar").html(html);
                } else {
                    swal("Error", "No se pudieron cargar las sucursales", "error");
                }
            },
            error: function() {
                swal("Error", "Error de conexión", "error");
            }
        });
    }
    
    function guardarSincronizacionBidireccional() {
        var sucursalesSeleccionadas = [];
        $("#listaSucursalesBidireccional input[type='checkbox']:checked").each(function() {
            sucursalesSeleccionadas.push($(this).val());
        });
        
        if (sucursalesSeleccionadas.length === 0) {
            swal("Advertencia", "Debes seleccionar al menos una sucursal", "warning");
            return;
        }
        
        swal({
            title: "¿Confirmar configuración?",
            text: "Se configurará la sincronización bidireccional para " + sucursalesSeleccionadas.length + " sucursal(es)",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, configurar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "ajax/clientes-central.ajax.php",
                    method: "POST",
                    data: {
                        accion: "guardarSincronizacionBidireccional",
                        sucursales: sucursalesSeleccionadas
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        if (respuesta.success) {
                            swal("Éxito", "Sincronización bidireccional configurada correctamente", "success");
                            $("#modalSincronizacionBidireccional").modal("hide");
                        } else {
                            swal("Error", respuesta.mensaje, "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Error de conexión", "error");
                    }
                });
            }
        });
    }
    
    function confirmarCopiarASucursal() {
        var direccion = $("#selectDireccionCopiar").val();
        var sucursalId = $("#selectSucursalDestino").val();
        
        if (!direccion) {
            swal("Advertencia", "Debes seleccionar una dirección de copia", "warning");
            return;
        }
        
        if (!sucursalId) {
            swal("Advertencia", "Debes seleccionar una sucursal", "warning");
            return;
        }
        
        var sucursalNombre = $("#selectSucursalDestino option:selected").text();
        var mensajeConfirmacion = "";
        
        if (direccion === "central_a_sucursal") {
            mensajeConfirmacion = "Se copiarán todos los clientes centrales a: " + sucursalNombre;
        } else if (direccion === "sucursal_a_central") {
            mensajeConfirmacion = "Se copiarán todos los clientes de " + sucursalNombre + " a la central";
        }
        
        swal({
            title: "¿Confirmar copia?",
            text: mensajeConfirmacion,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, copiar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "ajax/clientes-central.ajax.php",
                    method: "POST",
                    data: {
                        accion: "copiarClientesASucursal",
                        direccion: direccion,
                        sucursalId: sucursalId
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        if (respuesta.success) {
                            var mensaje = "";
                            if (direccion === "central_a_sucursal") {
                                mensaje = "Clientes copiados correctamente a " + sucursalNombre + "\n\n";
                            } else {
                                mensaje = "Clientes copiados correctamente desde " + sucursalNombre + " a la central\n\n";
                            }
                            mensaje += "📊 Estadísticas:\n";
                            mensaje += "• Nuevos clientes: " + respuesta.copiados + "\n";
                            mensaje += "• Duplicados (omitidos): " + respuesta.duplicados + "\n";
                            mensaje += "• Total procesados: " + respuesta.total;
                            
                            swal("Éxito", mensaje, "success");
                            $("#modalCopiarASucursal").modal("hide");
                            cargarClientesCentrales(); // Recargar la tabla
                        } else {
                            swal("Error", respuesta.mensaje, "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Error de conexión", "error");
                    }
                });
            }
        });
    }
    
    function confirmarBorrarClientes() {
        var origen = $("#selectOrigenBorrar").val();
        var sucursalId = $("#selectSucursalBorrar").val();
        
        if (!origen) {
            swal("Advertencia", "Debes seleccionar un origen", "warning");
            return;
        }
        
        if (origen === "sucursal" && !sucursalId) {
            swal("Advertencia", "Debes seleccionar una sucursal", "warning");
            return;
        }
        
        var mensaje = "";
        if (origen === "central") {
            mensaje = "Se eliminarán TODOS los clientes centrales. Esta acción es irreversible.";
        } else {
            var sucursalNombre = $("#selectSucursalBorrar option:selected").text();
            mensaje = "Se eliminarán todos los clientes de: " + sucursalNombre + ". Esta acción es irreversible.";
        }
        
        swal({
            title: "¡PELIGRO!",
            text: mensaje,
            type: "error",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#dd4b39"
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "ajax/clientes-central.ajax.php",
                    method: "POST",
                    data: {
                        accion: "borrarClientes",
                        origen: origen,
                        sucursalId: sucursalId
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        if (respuesta.success) {
                            var mensaje = "";
                            if (origen === "central") {
                                mensaje = "Todos los clientes centrales han sido eliminados correctamente.";
                            } else {
                                mensaje = "Clientes eliminados correctamente de " + respuesta.sucursal + "\n\n";
                                mensaje += "📊 Estadísticas:\n";
                                mensaje += "• Clientes eliminados: " + respuesta.eliminados;
                            }
                            
                            swal("Éxito", mensaje, "success");
                            $("#modalBorrarClientes").modal("hide");
                            cargarClientesCentrales(); // Recargar la tabla
                        } else {
                            swal("Error", respuesta.mensaje, "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Error de conexión", "error");
                    }
                });
            }
        });
    }
});
