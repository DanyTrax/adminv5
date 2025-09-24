/*=============================================
VARIABLES GLOBALES
=============================================*/
var productosDespacho = [];
var solicitudActual = null;
var productosInventario = [];

/*=============================================
INICIALIZACIÓN
=============================================*/
$(document).ready(function() {
    
    console.log("🚛 Sistema de crear despacho inicializado");
    
    // Configurar autocomplete para búsqueda de productos
    configurarBusquedaProductos();
    
    // Cargar productos del inventario
    cargarProductosInventario();
    
    // Validar formulario
    validarFormulario();
    
    // Configurar eventos
    configurarEventos();
    
});

/*=============================================
CONFIGURAR BÚSQUEDA DE PRODUCTOS CON AUTOCOMPLETE
=============================================*/
function configurarBusquedaProductos() {
    
    $("#buscarProductoInput").on("input", function() {
        
        var termino = $(this).val().trim();
        
        if(termino.length >= 2) {
            buscarProductosEnTiempoReal(termino);
        } else {
            $("#resultadosBusquedaProducto").hide();
        }
    });
    
    // Ocultar resultados al hacer clic fuera
    $(document).on("click", function(event) {
        if(!$(event.target).closest("#buscarProductoInput, #resultadosBusquedaProducto").length) {
            $("#resultadosBusquedaProducto").hide();
        }
    });
}

/*=============================================
BUSCAR PRODUCTOS EN TIEMPO REAL
=============================================*/
function buscarProductosEnTiempoReal(termino) {
    
    var datos = new FormData();
    datos.append("termino", termino);
    
    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(productos) {
            
            if(productos && productos.length > 0) {
                mostrarResultadosBusqueda(productos);
            } else {
                mostrarSinResultados();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error buscando productos:", error);
            mostrarErrorBusqueda();
        }
    });
}

/*=============================================
MOSTRAR RESULTADOS DE BÚSQUEDA
=============================================*/
function mostrarResultadosBusqueda(productos) {
    
    var html = '';
    
    productos.forEach(function(producto) {
        
        var stockClass = '';
        var stockText = '';
        
        if(producto.stock <= 0) {
            stockClass = 'list-group-item-danger';
            stockText = 'SIN STOCK';
        } else if(producto.stock <= 5) {
            stockClass = 'list-group-item-warning';
            stockText = 'STOCK BAJO';
        } else {
            stockClass = 'list-group-item-success';
            stockText = 'DISPONIBLE';
        }
        
        html += `
            <a href="#" class="list-group-item ${stockClass}" 
               onclick="seleccionarProductoBusqueda('${producto.codigo}', '${producto.descripcion}', ${producto.stock})">
                <div class="row">
                    <div class="col-md-3">
                        <strong>${producto.codigo}</strong>
                    </div>
                    <div class="col-md-6">
                        ${producto.descripcion}
                    </div>
                    <div class="col-md-3 text-right">
                        <span class="badge">${producto.stock} - ${stockText}</span>
                    </div>
                </div>
            </a>
        `;
    });
    
    $("#resultadosBusquedaProducto").html(html).show();
}

/*=============================================
MOSTRAR SIN RESULTADOS
=============================================*/
function mostrarSinResultados() {
    
    var html = `
        <div class="list-group-item text-center text-muted">
            <i class="fa fa-search"></i>
            No se encontraron productos con ese criterio
        </div>
    `;
    
    $("#resultadosBusquedaProducto").html(html).show();
}

/*=============================================
MOSTRAR ERROR EN BÚSQUEDA
=============================================*/
function mostrarErrorBusqueda() {
    
    var html = `
        <div class="list-group-item list-group-item-danger text-center">
            <i class="fa fa-exclamation-triangle"></i>
            Error al buscar productos
        </div>
    `;
    
    $("#resultadosBusquedaProducto").html(html).show();
}

/*=============================================
SELECCIONAR PRODUCTO DE LA BÚSQUEDA
=============================================*/
function seleccionarProductoBusqueda(codigo, descripcion, stock) {
    
    $("#buscarProductoInput").val(codigo + " - " + descripcion);
    $("#resultadosBusquedaProducto").hide();
    
    // Mostrar información del producto seleccionado
    $("#codigoProductoSeleccionado").text(codigo);
    $("#descripcionProductoSeleccionado").text(descripcion);
    $("#stockProductoSeleccionado").text(stock + " unidades");
    
    // Configurar cantidad máxima
    $("#cantidadProductoDespachar").attr("max", stock).val(1);
    
    // Cambiar color según stock
    var $stockSpan = $("#stockProductoSeleccionado");
    if(stock <= 0) {
        $stockSpan.removeClass("text-green text-yellow").addClass("text-red");
        $("#cantidadProductoDespachar").attr("disabled", true);
    } else if(stock <= 5) {
        $stockSpan.removeClass("text-green text-red").addClass("text-yellow");
        $("#cantidadProductoDespachar").attr("disabled", false);
    } else {
        $stockSpan.removeClass("text-red text-yellow").addClass("text-green");
        $("#cantidadProductoDespachar").attr("disabled", false);
    }
    
    $("#productoSeleccionadoInfo").show();
}

/*=============================================
BUSCAR SOLICITUD DE STOCK
=============================================*/
function buscarSolicitud() {
    
    var numeroSolicitud = $("#numeroSolicitudBuscar").val().trim();
    
    if(numeroSolicitud === '') {
        swal({
            title: "Error",
            text: "Debe ingresar un número de solicitud",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    var datos = new FormData();
    datos.append("numeroSolicitud", numeroSolicitud);
    
    $.ajax({
        url: "ajax/solicitudes-stock.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(solicitud) {
            
            if(solicitud && solicitud.id) {
                mostrarSolicitudEncontrada(solicitud);
            } else {
                swal({
                    title: "Solicitud no encontrada",
                    text: "No se encontró una solicitud con el número: " + numeroSolicitud,
                    type: "warning",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error buscando solicitud:", error);
            swal({
                title: "Error de conexión",
                text: "No se pudo buscar la solicitud",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
MOSTRAR SOLICITUD ENCONTRADA
=============================================*/
function mostrarSolicitudEncontrada(solicitud) {
    
    solicitudActual = solicitud;
    
    $("#numeroSolicitudInfo").text(solicitud.numero_solicitud);
    $("#sucursalSolicitudInfo").text(solicitud.nombre_sucursal_solicitante);
    $("#totalProductosSolicitudInfo").text(solicitud.total_productos);
    $("#totalCantidadSolicitudInfo").text(solicitud.total_cantidad);
    
    $("#infoSolicitudEncontrada").show();
    
    // Establecer en campo oculto para vincular
    $("#idSolicitudOrigenHidden").val(solicitud.id);
}

/*=============================================
AGREGAR PRODUCTOS DE SOLICITUD AL DESPACHO
=============================================*/
function agregarProductosSolicitud() {
    
    if(!solicitudActual) {
        swal({
            title: "Error",
            text: "No hay solicitud seleccionada",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    try {
        var productos = JSON.parse(solicitudActual.productos_solicitud);
        var productosAgregados = 0;
        var productosConProblemas = [];
        
        productos.forEach(function(producto) {
            
            // Verificar si el producto ya está en el despacho
            var yaExiste = productosDespacho.find(function(p) {
                return p.codigo === producto.codigo;
            });
            
            if(!yaExiste) {
                // Verificar stock disponible
                verificarStockYAgregar(producto, function(stockDisponible) {
                    if(stockDisponible >= producto.cantidad) {
                        productosDespacho.push({
                            codigo: producto.codigo,
                            descripcion: producto.descripcion,
                            cantidad: parseInt(producto.cantidad),
                            stock_disponible: stockDisponible,
                            observacion: "Desde solicitud: " + solicitudActual.numero_solicitud
                        });
                        productosAgregados++;
                    } else {
                        productosConProblemas.push({
                            codigo: producto.codigo,
                            descripcion: producto.descripcion,
                            solicitado: producto.cantidad,
                            disponible: stockDisponible
                        });
                    }
                    
                    // Si es el último producto, actualizar vista
                    if(productosAgregados + productosConProblemas.length === productos.length) {
                        actualizarTablaProductos();
                        mostrarResultadoAgregarSolicitud(productosAgregados, productosConProblemas);
                    }
                });
            }
        });
        
    } catch(e) {
        console.error("Error procesando productos de solicitud:", e);
        swal({
            title: "Error",
            text: "Error al procesar los productos de la solicitud",
            type: "error",
            confirmButtonText: "Cerrar"
        });
    }
}

/*=============================================
MOSTRAR RESULTADO DE AGREGAR SOLICITUD
=============================================*/
function mostrarResultadoAgregarSolicitud(agregados, problemas) {
    
    var mensaje = `Se agregaron ${agregados} productos al despacho.`;
    
    if(problemas.length > 0) {
        mensaje += `\n\n⚠️ ${problemas.length} productos tienen problemas de stock:`;
        problemas.forEach(function(problema) {
            mensaje += `\n• ${problema.codigo}: Solicitado ${problema.solicitado}, Disponible ${problema.disponible}`;
        });
    }
    
    swal({
        title: agregados > 0 ? "Productos Agregados" : "Problemas de Stock",
        text: mensaje,
        type: agregados > 0 ? "success" : "warning",
        confirmButtonText: "Cerrar"
    });
}

/*=============================================
VERIFICAR STOCK Y AGREGAR PRODUCTO
=============================================*/
function verificarStockYAgregar(producto, callback) {
    
    var datos = new FormData();
    datos.append("codigoProducto", producto.codigo);
    datos.append("cantidad", producto.cantidad);
    
    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if(respuesta && typeof respuesta.stock_actual !== 'undefined') {
                callback(respuesta.stock_actual);
            } else {
                callback(0);
            }
        },
        error: function() {
            callback(0);
        }
    });
}

/*=============================================
AGREGAR PRODUCTO MANUAL AL DESPACHO
=============================================*/
function agregarProductoADespacho() {
    
    var codigo = $("#codigoProductoSeleccionado").text();
    var descripcion = $("#descripcionProductoSeleccionado").text();
    var cantidad = parseInt($("#cantidadProductoDespachar").val());
    var stockDisponible = parseInt($("#stockProductoSeleccionado").text().replace(" unidades", ""));
    var observacion = $("#observacionProductoDespachar").val() || "";
    
    // Validaciones
    if(cantidad <= 0) {
        swal({
            title: "Error",
            text: "La cantidad debe ser mayor a 0",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    if(cantidad > stockDisponible) {
        swal({
            title: "Stock Insuficiente",
            text: `Solo hay ${stockDisponible} unidades disponibles`,
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    // Verificar si el producto ya está en la lista
    var indiceExistente = productosDespacho.findIndex(function(p) {
        return p.codigo === codigo;
    });
    
    if(indiceExistente !== -1) {
        swal({
            title: "Producto ya agregado",
            text: "Este producto ya está en la lista. ¿Desea aumentar la cantidad?",
            type: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, aumentar",
            cancelButtonText: "No"
        }).then(function(result) {
            if(result.value) {
                var nuevaCantidad = productosDespacho[indiceExistente].cantidad + cantidad;
                
                if(nuevaCantidad <= stockDisponible) {
                    productosDespacho[indiceExistente].cantidad = nuevaCantidad;
                    productosDespacho[indiceExistente].observacion = observacion;
                    actualizarTablaProductos();
                    limpiarProductoSeleccionado();
                    
                    swal({
                        title: "Cantidad actualizada",
                        text: `Nueva cantidad: ${nuevaCantidad}`,
                        type: "success",
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    swal({
                        title: "Stock Insuficiente",
                        text: `La cantidad total (${nuevaCantidad}) supera el stock disponible (${stockDisponible})`,
                        type: "error",
                        confirmButtonText: "Cerrar"
                    });
                }
            }
        });
        return;
    }
    
    // Agregar producto nuevo
    productosDespacho.push({
        codigo: codigo,
        descripcion: descripcion,
        cantidad: cantidad,
        stock_disponible: stockDisponible,
        observacion: observacion
    });
    
    // Actualizar tabla y limpiar selección
    actualizarTablaProductos();
    limpiarProductoSeleccionado();
    
    // Mensaje de éxito
    swal({
        title: "Producto agregado",
        text: `${descripcion} - Cantidad: ${cantidad}`,
        type: "success",
        timer: 2000,
        showConfirmButton: false
    });
}

/*=============================================
ACTUALIZAR TABLA DE PRODUCTOS
=============================================*/
function actualizarTablaProductos() {
    
    var $tbody = $("#listaProductosDespacho");
    
    if(productosDespacho.length === 0) {
        $tbody.html(`
            <tr id="filaVaciaProductos">
                <td colspan="7" class="text-center text-muted">
                    <i class="fa fa-info-circle"></i>
                    No hay productos agregados al despacho
                </td>
            </tr>
        `);
        $("#totalDespacho").hide();
        actualizarContadores(0, 0);
        $("#btnCrearDespacho").prop("disabled", true);
        return;
    }
    
    var html = '';
    var totalCantidad = 0;
    var totalProductos = productosDespacho.length;
    
    productosDespacho.forEach(function(producto, index) {
        
        totalCantidad += producto.cantidad;
        
        // Determinar clase según stock
        var filaClass = '';
        if(producto.cantidad > producto.stock_disponible) {
            filaClass = 'producto-sin-stock';
        } else if(producto.stock_disponible <= 5) {
            filaClass = 'producto-stock-bajo';
        } else {
            filaClass = 'producto-stock-ok';
        }
        
        html += `
            <tr class="${filaClass}">
                <td class="text-center">${index + 1}</td>
                <td><code>${producto.codigo}</code></td>
                <td>${producto.descripcion}</td>
                <td class="text-center">
                    <strong>${producto.cantidad}</strong>
                </td>
                <td class="text-center">
                    <span class="badge">${producto.stock_disponible}</span>
                </td>
                <td>
                    <small>${producto.observacion || 'Sin observación'}</small>
                </td>
                <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning btn-xs" 
                                onclick="editarCantidadProducto(${index})"
                                data-toggle="tooltip" title="Editar cantidad">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-xs" 
                                onclick="eliminarProductoDespacho(${index})"
                                data-toggle="tooltip" title="Eliminar producto">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    $tbody.html(html);
    
    // Mostrar totales
    $("#totalCantidadDespacho").text(totalCantidad);
    $("#totalProductosDespacho").text(totalProductos);
    $("#totalDespacho").show();
    
    // Actualizar contadores en header
    actualizarContadores(totalProductos, totalCantidad);
    
    // Actualizar campos ocultos
    $("#productosDespachoHidden").val(JSON.stringify(productosDespacho));
    $("#totalProductosHidden").val(totalProductos);
    $("#totalCantidadHidden").val(totalCantidad);
    
    // Habilitar botón si hay productos
    $("#btnCrearDespacho").prop("disabled", totalProductos === 0);
    
    // Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();
}

/*=============================================
ACTUALIZAR CONTADORES EN HEADER
=============================================*/
function actualizarContadores(productos, cantidad) {
    
    $("#contadorProductos").text(productos + " producto" + (productos !== 1 ? "s" : ""));
    $("#contadorCantidad").text(cantidad + " unidad" + (cantidad !== 1 ? "es" : ""));
    
    // Cambiar colores según cantidad
    var $contadorProductos = $("#contadorProductos");
    var $contadorCantidad = $("#contadorCantidad");
    
    if(productos === 0) {
        $contadorProductos.removeClass("label-primary label-success").addClass("label-default");
        $contadorCantidad.removeClass("label-success label-warning").addClass("label-default");
    } else if(productos <= 5) {
        $contadorProductos.removeClass("label-default label-success").addClass("label-primary");
        $contadorCantidad.removeClass("label-default label-warning").addClass("label-success");
    } else {
        $contadorProductos.removeClass("label-default label-primary").addClass("label-success");
        
        if(cantidad > 100) {
            $contadorCantidad.removeClass("label-default label-success").addClass("label-warning");
        } else {
            $contadorCantidad.removeClass("label-default label-warning").addClass("label-success");
        }
    }
}

/*=============================================
EDITAR CANTIDAD DE PRODUCTO
=============================================*/
function editarCantidadProducto(index) {
    
    var producto = productosDespacho[index];
    
    $("#productoEditarInfo").text(producto.codigo + " - " + producto.descripcion);
    $("#stockDisponibleEditar").text(producto.stock_disponible);
    $("#nuevaCantidadEditar").val(producto.cantidad);
    $("#nuevaObservacionEditar").val(producto.observacion || "");
    $("#indiceProductoEditar").val(index);
    
    $("#modalEditarCantidad").modal("show");
}

/*=============================================
GUARDAR EDICIÓN DE CANTIDAD
=============================================*/
function guardarEdicionCantidad() {
    
    var index = parseInt($("#indiceProductoEditar").val());
    var nuevaCantidad = parseInt($("#nuevaCantidadEditar").val());
    var nuevaObservacion = $("#nuevaObservacionEditar").val();
    
    if(nuevaCantidad <= 0) {
        swal({
            title: "Error",
            text: "La cantidad debe ser mayor a 0",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    var producto = productosDespacho[index];
    
    if(nuevaCantidad > producto.stock_disponible) {
        swal({
            title: "Stock Insuficiente",
            text: `Solo hay ${producto.stock_disponible} unidades disponibles`,
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    // Actualizar producto
    productosDespacho[index].cantidad = nuevaCantidad;
    productosDespacho[index].observacion = nuevaObservacion;
    
    // Actualizar tabla
    actualizarTablaProductos();
    
    // Cerrar modal
    $("#modalEditarCantidad").modal("hide");
    
    swal({
        title: "Cantidad actualizada",
        type: "success",
        timer: 1500,
        showConfirmButton: false
    });
}

/*=============================================
ELIMINAR PRODUCTO DEL DESPACHO
=============================================*/
function eliminarProductoDespacho(index) {
    
    var producto = productosDespacho[index];
    
    swal({
        title: "¿Eliminar producto?",
        text: `Se eliminará: ${producto.descripcion}`,
        type: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if(result.value) {
            
            productosDespacho.splice(index, 1);
            actualizarTablaProductos();
            
            swal({
                title: "Producto eliminado",
                type: "success",
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

/*=============================================
MOSTRAR LISTA COMPLETA DE PRODUCTOS
=============================================*/
function mostrarListaProductos() {
    
    cargarProductosInventario();
    $("#modalListaProductos").modal("show");
}

/*=============================================
CARGAR PRODUCTOS DEL INVENTARIO
=============================================*/
function cargarProductosInventario() {
    
    var datos = new FormData();
    datos.append("cargarInventario", true);
    
    $.ajax({
        url: "ajax/productos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(productos) {
            
            if(productos && productos.length > 0) {
                productosInventario = productos;
                mostrarProductosEnModal(productos);
            } else {
                $("#tablaProductosModal tbody").html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No hay productos disponibles en el inventario
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error cargando inventario:", error);
            $("#tablaProductosModal tbody").html(`
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Error cargando productos
                    </td>
                </tr>
            `);
        }
    });
}

/*=============================================
MOSTRAR PRODUCTOS EN MODAL
=============================================*/
function mostrarProductosEnModal(productos) {
    
    var html = '';
    
    productos.forEach(function(producto) {
        
        var stockClass = '';
        var stockBadge = '';
        var botonClass = 'btn-success';
        var botonTexto = 'Seleccionar';
        
        if(producto.stock <= 0) {
            stockClass = 'text-danger';
            stockBadge = 'label-danger';
            botonClass = 'btn-default';
            botonTexto = 'Sin Stock';
        } else if(producto.stock <= 5) {
            stockClass = 'text-warning';
            stockBadge = 'label-warning';
        } else {
            stockClass = 'text-success';
            stockBadge = 'label-success';
        }
        
        html += `
            <tr>
                <td><code>${producto.codigo}</code></td>
                <td>${producto.descripcion}</td>
                <td class="text-center">
                    <span class="label ${stockBadge}">${producto.stock}</span>
                </td>
                <td class="text-right">$${formatearNumero(producto.precio_venta)}</td>
                <td class="text-center">
                    <button type="button" class="btn ${botonClass} btn-xs" 
                            ${producto.stock <= 0 ? 'disabled' : ''}
                            onclick="seleccionarProductoModal('${producto.codigo}', '${producto.descripcion}', ${producto.stock})">
                        ${botonTexto}
                    </button>
                </td>
            </tr>
        `;
    });
    
    $("#tablaProductosModal tbody").html(html);
}

/*=============================================
SELECCIONAR PRODUCTO DEL MODAL
=============================================*/
function seleccionarProductoModal(codigo, descripcion, stock) {
    
    $("#modalListaProductos").modal("hide");
    
    // Simular selección en búsqueda
    seleccionarProductoBusqueda(codigo, descripcion, stock);
    
    // Enfocar cantidad
    setTimeout(function() {
        $("#cantidadProductoDespachar").focus();
    }, 500);
}

/*=============================================
FILTRAR PRODUCTOS EN MODAL
=============================================*/
$("#filtroProductosModal").on("input", function() {
    
    var filtro = $(this).val().toLowerCase().trim();
    
    if(filtro === '') {
        mostrarProductosEnModal(productosInventario);
    } else {
        var productosFiltrados = productosInventario.filter(function(producto) {
            return producto.codigo.toLowerCase().includes(filtro) || 
                   producto.descripcion.toLowerCase().includes(filtro);
        });
        
        mostrarProductosEnModal(productosFiltrados);
    }
});

/*=============================================
VALIDAR STOCK COMPLETO
=============================================*/
function validarStockCompleto() {
    
    if(productosDespacho.length === 0) {
        swal({
            title: "Sin productos",
            text: "Debe agregar al menos un producto al despacho",
            type: "warning",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    var problemasStock = [];
    var validacionesCompletas = 0;
    
    productosDespacho.forEach(function(producto, index) {
        
        var datos = new FormData();
        datos.append("codigoProducto", producto.codigo);
        datos.append("cantidad", producto.cantidad);
        
        $.ajax({
            url: "ajax/despachos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                
                validacionesCompletas++;
                
                if(!respuesta.disponible) {
                    problemasStock.push({
                        indice: index,
                        codigo: producto.codigo,
                        descripcion: producto.descripcion,
                        solicitado: producto.cantidad,
                        disponible: respuesta.stock_actual || 0
                    });
                } else {
                    // Actualizar stock disponible
                    productosDespacho[index].stock_disponible = respuesta.stock_actual;
                }
                
                // Si completamos todas las validaciones
                if(validacionesCompletas === productosDespacho.length) {
                    mostrarResultadoValidacion(problemasStock);
                }
            },
            error: function() {
                validacionesCompletas++;
                problemasStock.push({
                    indice: index,
                    codigo: producto.codigo,
                    descripcion: producto.descripcion,
                    solicitado: producto.cantidad,
                    disponible: 0,
                    error: true
                });
                
                if(validacionesCompletas === productosDespacho.length) {
                    mostrarResultadoValidacion(problemasStock);
                }
            }
        });
    });
}

/*=============================================
MOSTRAR RESULTADO DE VALIDACIÓN
=============================================*/
function mostrarResultadoValidacion(problemas) {
    
    if(problemas.length === 0) {
        // Todo OK
        $("#alertaValidacionStock").hide();
        swal({
            title: "✅ Validación exitosa",
            text: "Todos los productos tienen stock suficiente",
            type: "success",
            confirmButtonText: "Cerrar"
        });
        
        actualizarTablaProductos();
        
    } else {
        // Hay problemas
        var listaProblemas = '';
        problemas.forEach(function(problema) {
            listaProblemas += `
                <li>
                    <strong>${problema.codigo}</strong>:
                    Solicitado: ${problema.solicitado}, Disponible: ${problema.disponible}
                    ${problema.error ? '<span class="text-danger">(Error de conexión)</span>' : ''}
                </li>
            `;
        });
        
        $("#listaProblemasStock").html(listaProblemas);
        $("#alertaValidacionStock").show();
        
        // Actualizar tabla con colores de problemas
        actualizarTablaProductos();
        
        swal({
            title: "⚠️ Problemas de Stock",
            text: `${problemas.length} productos tienen problemas de stock. Revise la tabla y ajuste las cantidades.`,
            type: "warning",
            confirmButtonText: "Entendido"
        });
    }
}

/*=============================================
LIMPIAR FORMULARIOS Y SELECCIONES
=============================================*/
function limpiarProductoSeleccionado() {
    
    $("#buscarProductoInput").val("");
    $("#cantidadProductoDespachar").val(1);
    $("#observacionProductoDespachar").val("");
    $("#productoSeleccionadoInfo").hide();
    $("#resultadosBusquedaProducto").hide();
}

function limpiarSolicitud() {
    
    $("#numeroSolicitudBuscar").val("");
    $("#infoSolicitudEncontrada").hide();
    $("#idSolicitudOrigenHidden").val("");
    solicitudActual = null;
}

/*=============================================
CONFIGURAR EVENTOS
=============================================*/
function configurarEventos() {
    
    // Evento para buscar solicitud al presionar Enter
    $("#numeroSolicitudBuscar").on("keypress", function(e) {
        if(e.which === 13) {
            buscarSolicitud();
            e.preventDefault();
        }
    });
    
    // Evento para agregar producto al presionar Enter en cantidad
    $("#cantidadProductoDespachar").on("keypress", function(e) {
        if(e.which === 13) {
            agregarProductoADespacho();
            e.preventDefault();
        }
    });
    
    // Validar cantidad en tiempo real
    $("#cantidadProductoDespachar").on("input", function() {
        
        var cantidad = parseInt($(this).val()) || 0;
        var stockDisponible = parseInt($("#stockProductoSeleccionado").text().replace(" unidades", "")) || 0;
        
        if(cantidad > stockDisponible) {
            $(this).addClass("cantidad-editando");
            swal({
                title: "Stock Insuficiente",
                text: `Solo hay ${stockDisponible} unidades disponibles`,
                type: "warning",
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            $(this).removeClass("cantidad-editando");
        }
    });
    
    // Validar cantidad en modal de edición
    $("#nuevaCantidadEditar").on("input", function() {
        
        var cantidad = parseInt($(this).val()) || 0;
        var stockDisponible = parseInt($("#stockDisponibleEditar").text()) || 0;
        
        if(cantidad > stockDisponible) {
            $(this).addClass("cantidad-editando");
        } else {
            $(this).removeClass("cantidad-editando");
        }
    });
    
    // Auto-focus en campos importantes
    $("#modalEditarCantidad").on("shown.bs.modal", function() {
        $("#nuevaCantidadEditar").focus().select();
    });
    
    $("#modalListaProductos").on("shown.bs.modal", function() {
        $("#filtroProductosModal").focus();
    });
}

/*=============================================
VALIDAR FORMULARIO ANTES DE ENVIAR
=============================================*/
function validarFormulario() {
    
    $("#formCrearDespacho").on("submit", function(e) {
        
        // Validaciones básicas
        if(productosDespacho.length === 0) {
            e.preventDefault();
            swal({
                title: "Error",
                text: "Debe agregar al menos un producto al despacho",
                type: "error",
                confirmButtonText: "Cerrar"
            });
            return false;
        }
        
        // Validar que no haya problemas de stock críticos
        var problemasStock = productosDespacho.filter(function(producto) {
            return producto.cantidad > producto.stock_disponible;
        });
        
        if(problemasStock.length > 0) {
            e.preventDefault();
            
            var listaProblemas = problemasStock.map(function(p) {
                return `• ${p.codigo}: Solicitado ${p.cantidad}, Disponible ${p.stock_disponible}`;
            }).join('\n');
            
            swal({
                title: "Problemas de Stock",
                text: `Los siguientes productos tienen problemas de stock:\n\n${listaProblemas}\n\n¿Desea continuar de todas formas?`,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, crear despacho",
                cancelButtonText: "Cancelar"
            }).then(function(result) {
                if(result.value) {
                    // Enviar formulario manualmente
                    $("#formCrearDespacho")[0].submit();
                }
            });
            
            return false;
        }
        
        // Mostrar loading en botón
        var $boton = $("#btnCrearDespacho");
        $boton.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Creando despacho...');
        
        // Actualizar campos ocultos finales
        $("#productosDespachoHidden").val(JSON.stringify(productosDespacho));
        $("#totalProductosHidden").val(productosDespacho.length);
        $("#totalCantidadHidden").val(productosDespacho.reduce(function(total, p) {
            return total + p.cantidad;
        }, 0));
        
        return true;
    });
}

/*=============================================
FUNCIONES DE UTILIDAD
=============================================*/
function formatearNumero(numero) {
    return new Intl.NumberFormat('es-CO').format(numero);
}

function obtenerFechaActual() {
    var fecha = new Date();
    var dia = String(fecha.getDate()).padStart(2, '0');
    var mes = String(fecha.getMonth() + 1).padStart(2, '0');
    var año = fecha.getFullYear();
    var horas = String(fecha.getHours()).padStart(2, '0');
    var minutos = String(fecha.getMinutes()).padStart(2, '0');
    
    return `${dia}/${mes}/${año} ${horas}:${minutos}`;
}

/*=============================================
FUNCIONES DE NOTIFICACIÓN
=============================================*/
function mostrarNotificacionExito(titulo, mensaje) {
    
    swal({
        title: titulo,
        text: mensaje,
        type: "success",
        timer: 3000,
        showConfirmButton: false
    });
}

function mostrarNotificacionError(titulo, mensaje) {
    
    swal({
        title: titulo,
        text: mensaje,
        type: "error",
        confirmButtonText: "Cerrar"
    });
}

/*=============================================
ATAJOS DE TECLADO
=============================================*/
$(document).on("keydown", function(e) {
    
    // Ctrl + Enter para crear despacho
    if(e.ctrlKey && e.which === 13) {
        if(!$("#btnCrearDespacho").prop("disabled")) {
            $("#formCrearDespacho").submit();
        }
        e.preventDefault();
    }
    
    // ESC para cerrar modales
    if(e.which === 27) {
        $(".modal").modal("hide");
        $("#resultadosBusquedaProducto").hide();
    }
    
    // F1 para ayuda (opcional)
    if(e.which === 112) {
        mostrarAyuda();
        e.preventDefault();
    }
});

/*=============================================
FUNCIÓN DE AYUDA
=============================================*/
function mostrarAyuda() {
    
    swal({
        title: "💡 Ayuda - Crear Despacho",
        html: `
            <div style="text-align: left;">
                <h4>🔍 Buscar Solicitud:</h4>
                <p>• Ingrese el número de solicitud para cargar productos automáticamente</p>
                <p>• Los productos se agregarán con las cantidades solicitadas</p>
                
                <h4>🛍️ Agregar Productos:</h4>
                <p>• Busque productos por código o descripción</p>
                <p>• Use la lista completa para ver todos los productos</p>
                <p>• Ajuste cantidades y agregue observaciones</p>
                
                <h4>✏️ Editar Productos:</h4>
                <p>• Haga clic en el botón amarillo para editar cantidades</p>
                <p>• Use el botón rojo para eliminar productos</p>
                
                <h4>⌨️ Atajos de Teclado:</h4>
                <p>• <strong>Ctrl + Enter:</strong> Crear despacho</p>
                <p>• <strong>ESC:</strong> Cerrar ventanas</p>
                <p>• <strong>F1:</strong> Mostrar esta ayuda</p>
            </div>
        `,
        confirmButtonText: "Entendido",
        width: 600
    });
}

/*=============================================
FUNCIÓN DE DEBUG (DESARROLLO)
=============================================*/
function debugCrearDespacho() {
    
    console.log("=== DEBUG CREAR DESPACHO ===");
    console.log("Productos en despacho:", productosDespacho);
    console.log("Solicitud actual:", solicitudActual);
    console.log("Productos inventario cargados:", productosInventario.length);
    console.log("Campos ocultos:");
    console.log("- productosDespachoHidden:", $("#productosDespachoHidden").val());
    console.log("- totalProductosHidden:", $("#totalProductosHidden").val());
    console.log("- totalCantidadHidden:", $("#totalCantidadHidden").val());
    console.log("- idSolicitudOrigenHidden:", $("#idSolicitudOrigenHidden").val());
    console.log("============================");
}

// Hacer función de debug accesible globalmente (solo en desarrollo)
window.debugCrearDespacho = debugCrearDespacho;

/*=============================================
INICIALIZACIÓN FINAL
=============================================*/
console.log("✅ JavaScript de crear despacho cargado correctamente");

// Si hay parámetros en la URL (para editar), cargarlos
$(window).on("load", function() {
    
    var urlParams = new URLSearchParams(window.location.search);
    var editarId = urlParams.get('editar');
    
    if(editarId) {
        cargarDespachoParaEditar(editarId);
    }
});

/*=============================================
FUNCIÓN PARA CARGAR DESPACHO EN MODO EDICIÓN
=============================================*/
function cargarDespachoParaEditar(idDespacho) {
    
    var datos = new FormData();
    datos.append("idDespacho", idDespacho);
    
    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(despacho) {
            
            if(despacho && despacho.id) {
                
                // Solo permitir edición de despachos pendientes
                if(despacho.estado !== 'pendiente') {
                    swal({
                        title: "No se puede editar",
                        text: "Solo se pueden editar despachos en estado pendiente",
                        type: "warning",
                        confirmButtonText: "Entendido"
                    }).then(function() {
                        window.location = "despachos";
                    });
                    return;
                }
                
                // Cargar datos del despacho
                $("#numeroDespacho").val(despacho.numero_despacho);
                $("textarea[name='detalleAdicional']").val(despacho.detalle_adicional || "");
                
                // Si tiene solicitud origen, mostrarla
                if(despacho.id_solicitud_origen) {
                    $("#idSolicitudOrigenHidden").val(despacho.id_solicitud_origen);
                }
                
                // Cargar productos
                try {
                    productosDespacho = JSON.parse(despacho.productos_despacho);
                    actualizarTablaProductos();
                    
                    swal({
                        title: "Despacho cargado",
                        text: "Despacho cargado para edición",
                        type: "success",
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                } catch(e) {
                    console.error("Error cargando productos del despacho:", e);
                    swal({
                        title: "Error",
                        text: "Error al cargar los productos del despacho",
                        type: "error",
                        confirmButtonText: "Cerrar"
                    });
                }
                
            } else {
                swal({
                    title: "Despacho no encontrado",
                    text: "No se pudo cargar el despacho para edición",
                    type: "error",
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    window.location = "despachos";
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error cargando despacho para editar:", error);
            swal({
                title: "Error de conexión",
                text: "No se pudo cargar el despacho",
                type: "error",
                confirmButtonText: "Cerrar"
            }).then(function() {
                window.location = "despachos";
            });
        }
    });
}