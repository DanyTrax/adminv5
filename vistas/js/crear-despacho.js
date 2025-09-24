/*=============================================
VARIABLES GLOBALES
=============================================*/
var productosDespacho = [];
var inventarioLocal = [];
var solicitudSeleccionada = null;

// Evitar redeclaración de variables globales
if (typeof window.perfilUsuario === 'undefined') {
    window.perfilUsuario = document.body.dataset.perfil || 'Usuario';
}
if (typeof window.idUsuario === 'undefined') {
    window.idUsuario = document.body.dataset.userId || '0';
}

/*=============================================
INICIALIZACIÓN
=============================================*/
$(document).ready(function() {
    
    console.log("🚛 Sistema de crear despacho inicializado");
    
    // Cargar inventario local
    cargarProductosInventario();
    
    // Configurar eventos
    configurarEventos();
    
    // Configurar filtro de búsqueda
    configurarFiltroProductos();
    
    // Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
});

/*=============================================
CARGAR PRODUCTOS DEL INVENTARIO LOCAL
=============================================*/
function cargarProductosInventario() {
    
    $("#listaProductosLocal").html(`
        <tr>
            <td colspan="5" class="text-center">
                <i class="fa fa-spinner fa-spin"></i> Cargando inventario local...
            </td>
        </tr>
    `);
    
    $.ajax({
        url: "ajax/productos-despacho.ajax.php",
        method: "POST",
        data: {
            obtenerInventarioLocal: true
        },
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.success) {
                
                inventarioLocal = respuesta.productos;
                mostrarProductosInventario(respuesta.productos);
                
                console.log("✅ Inventario cargado:", respuesta.total, "productos");
                
            } else {
                $("#listaProductosLocal").html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <i class="fa fa-exclamation-triangle"></i> Error: ${respuesta.error}
                        </td>
                    </tr>
                `);
                
                console.error("Error cargando inventario:", respuesta.error);
            }
        },
        error: function(xhr, status, error) {
            $("#listaProductosLocal").html(`
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        <i class="fa fa-times-circle"></i> Error de conexión
                    </td>
                </tr>
            `);
            
            console.error("Error AJAX cargando inventario:", error);
        }
    });
}

/*=============================================
MOSTRAR PRODUCTOS EN LA TABLA DE INVENTARIO
=============================================*/
function mostrarProductosInventario(productos) {
    
    if(!productos || productos.length === 0) {
        $("#listaProductosLocal").html(`
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <i class="fa fa-info-circle"></i> No hay productos con stock disponible
                </td>
            </tr>
        `);
        return;
    }
    
    var html = '';
    
    productos.forEach(function(producto) {
        
        // Determinar clase de stock
        var claseStock = 'stock-disponible';
        if(producto.stock <= 5) {
            claseStock = 'stock-bajo';
        }
        if(producto.stock <= 0) {
            claseStock = 'stock-agotado';
        }
        
        // Imagen del producto
        var imagenProducto = producto.imagen && producto.imagen !== '' ? 
                           'vistas/img/productos/' + producto.imagen : 
                           'vistas/img/productos/default/anonymous.png';
        
        html += `
            <tr data-codigo="${producto.codigo}">
                <td class="text-center">
                    <img src="${imagenProducto}" 
                         class="img-thumbnail" 
                         width="40" height="40"
                         style="max-width: 40px; max-height: 40px; object-fit: cover;"
                         onerror="this.src='vistas/img/productos/default/anonymous.png'">
                </td>
                <td><code>${producto.codigo}</code></td>
                <td>${producto.descripcion}</td>
                <td class="text-center">
                    <span class="${claseStock}">${producto.stock}</span>
                </td>
                <td class="text-center">
                    ${producto.stock > 0 ? 
                      `<button type="button" 
                               class="btn btn-success btn-xs" 
                               onclick="abrirModalCantidad('${producto.codigo}', '${producto.descripcion.replace(/'/g, "\\'")}', ${producto.stock})"
                               data-toggle="tooltip" 
                               title="Agregar al despacho">
                           <i class="fa fa-plus"></i>
                       </button>` :
                      `<button type="button" 
                               class="btn btn-default btn-xs" 
                               disabled
                               data-toggle="tooltip" 
                               title="Sin stock">
                           <i class="fa fa-ban"></i>
                       </button>`
                    }
                </td>
            </tr>
        `;
    });
    
    $("#listaProductosLocal").html(html);
    
    // Reactivar tooltips
    $('[data-toggle="tooltip"]').tooltip();
}

/*=============================================
CONFIGURAR EVENTOS
=============================================*/
function configurarEventos() {
    
    // Filtro de búsqueda en inventario
    $("#filtroProductosLocal").on("keyup", function() {
        filtrarProductosLocal($(this).val());
    });
    
    // Búsqueda de solicitudes
    $("#numeroSolicitudBuscar").on("keyup", function() {
        var termino = $(this).val();
        if(termino.length >= 3) {
            buscarSolicitudesStock(termino);
        } else {
            ocultarResultadosSolicitudes();
        }
    });
    
    // Confirmar agregar producto
    $("#confirmarAgregarProductoDespacho").on("click", function() {
        confirmarAgregarProducto();
    });
    
    // Validar cantidad en modal
    $("#cantidadProductoDespachoModal").on("input", function() {
        validarCantidadModal();
    });
}

/*=============================================
FILTRAR PRODUCTOS LOCAL
=============================================*/
function filtrarProductosLocal(termino) {
    
    if(!termino || termino.length === 0) {
        mostrarProductosInventario(inventarioLocal);
        return;
    }
    
    var productosFiltrados = inventarioLocal.filter(function(producto) {
        return producto.codigo.toLowerCase().includes(termino.toLowerCase()) ||
               producto.descripcion.toLowerCase().includes(termino.toLowerCase());
    });
    
    mostrarProductosInventario(productosFiltrados);
    
    // Highlight del término buscado
    if(productosFiltrados.length > 0) {
        setTimeout(function() {
            $("#tablaInventarioLocal tbody tr:first").addClass("producto-encontrado");
            setTimeout(function() {
                $("#tablaInventarioLocal tbody tr:first").removeClass("producto-encontrado");
            }, 2000);
        }, 100);
    }
}

/*=============================================
BUSCAR SOLICITUDES DE STOCK
=============================================*/
function buscarSolicitudesStock(termino) {
    
    $.ajax({
        url: "ajax/productos-despacho.ajax.php",
        method: "POST",
        data: {
            buscarSolicitudes: true,
            termino: termino
        },
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.success && respuesta.solicitudes.length > 0) {
                mostrarResultadosSolicitudes(respuesta.solicitudes);
            } else {
                mostrarSinResultadosSolicitudes();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error buscando solicitudes:", error);
            mostrarErrorBusquedaSolicitudes();
        }
    });
}

/*=============================================
MOSTRAR RESULTADOS DE SOLICITUDES
=============================================*/
function mostrarResultadosSolicitudes(solicitudes) {
    
    var html = '<div class="list-group" style="max-height: 200px; overflow-y: auto;">';
    
    solicitudes.forEach(function(solicitud) {
        var fecha = new Date(solicitud.fecha_solicitud).toLocaleDateString();
        var estadoClass = solicitud.estado === 'aprobada' ? 'success' : 'warning';
        
        html += `
            <a href="#" 
               class="list-group-item list-group-item-action" 
               onclick="seleccionarSolicitud(${solicitud.id})"
               style="padding: 8px 12px;">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        <code>${solicitud.codigo_solicitud}</code>
                        <span class="label label-${estadoClass}">${solicitud.estado.toUpperCase()}</span>
                    </h6>
                    <small>${fecha}</small>
                </div>
                <p class="mb-1">
                    <strong>Usuario:</strong> ${solicitud.nombre_usuario_solicitante}<br>
                    <strong>Productos:</strong> ${solicitud.cantidad_productos} 
                    (<strong>${solicitud.cantidad_total}</strong> unidades)
                </p>
            </a>
        `;
    });
    
    html += '</div>';
    
    $("#numeroSolicitudBuscar").after(`
        <div id="resultadosBusquedaSolicitudes" class="dropdown-menu" 
             style="display: block; position: relative; width: 100%; margin-top: 5px;">
            ${html}
        </div>
    `);
}

/*=============================================
SELECCIONAR SOLICITUD
=============================================*/
function seleccionarSolicitud(idSolicitud) {
    
    $.ajax({
        url: "ajax/productos-despacho.ajax.php",
        method: "POST",
        data: {
            obtenerDetalleSolicitud: true,
            idSolicitud: idSolicitud
        },
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.success) {
                
                solicitudSeleccionada = respuesta.solicitud;
                
                // Mostrar información de la solicitud
                $("#numeroSolicitudBuscar").val(solicitudSeleccionada.codigo_solicitud);
                
                var infoHtml = `
                    <strong>Código:</strong> ${solicitudSeleccionada.codigo_solicitud}<br>
                    <strong>Usuario:</strong> ${solicitudSeleccionada.nombre_usuario_solicitante}<br>
                    <strong>Origen:</strong> ${solicitudSeleccionada.sucursal_origen}<br>
                    <strong>Estado:</strong> <span class="label label-${solicitudSeleccionada.estado === 'aprobada' ? 'success' : 'warning'}">${solicitudSeleccionada.estado.toUpperCase()}</span><br>
                    <strong>Productos:</strong> ${solicitudSeleccionada.cantidad_productos} (${solicitudSeleccionada.cantidad_total} unidades)
                `;
                
                $("#datosSolicitudEncontrada").html(infoHtml);
                $("#infoSolicitudEncontrada").show();
                $("#idSolicitudOrigenHidden").val(solicitudSeleccionada.id);
                
                // Ocultar resultados
                ocultarResultadosSolicitudes();
                
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error,
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function() {
            swal({
                title: "Error de conexión",
                text: "No se pudo obtener los detalles de la solicitud",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
CARGAR PRODUCTOS DE SOLICITUD
=============================================*/
function cargarProductosDeSolicitud() {
    
    if(!solicitudSeleccionada) {
        swal({
            title: "Error",
            text: "No hay solicitud seleccionada",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    try {
        var productos = JSON.parse(solicitudSeleccionada.productos_solicitud);
        
        // Limpiar productos actuales
        productosDespacho = [];
        
        // Agregar productos de la solicitud
        var productosAgregados = 0;
        var productosConProblemas = [];
        
        productos.forEach(function(producto) {
            
            // Verificar stock disponible
            var productoInventario = inventarioLocal.find(function(p) {
                return p.codigo === producto.codigo;
            });
            
            if(productoInventario) {
                if(productoInventario.stock >= producto.cantidad) {
                    
                    productosDespacho.push({
                        codigo: producto.codigo,
                        descripcion: producto.descripcion,
                        cantidad: producto.cantidad,
                        stock_disponible: productoInventario.stock,
                        observacion: "Cargado desde solicitud: " + solicitudSeleccionada.codigo_solicitud
                    });
                    
                    productosAgregados++;
                    
                } else {
                    productosConProblemas.push({
                        codigo: producto.codigo,
                        descripcion: producto.descripcion,
                        solicitado: producto.cantidad,
                        disponible: productoInventario.stock
                    });
                }
            } else {
                productosConProblemas.push({
                    codigo: producto.codigo,
                    descripcion: producto.descripcion,
                    solicitado: producto.cantidad,
                    disponible: 0
                });
            }
        });
        
        // Actualizar vista
        actualizarVistaProductosDespacho();
        
        // Mostrar resultado
        var mensaje = `Se agregaron ${productosAgregados} productos al despacho.`;
        
        if(productosConProblemas.length > 0) {
            mensaje += `\n\n⚠️ ${productosConProblemas.length} productos tienen problemas de stock:`;
            productosConProblemas.forEach(function(p) {
                mensaje += `\n• ${p.codigo}: Solicitado ${p.solicitado}, Disponible ${p.disponible}`;
            });
        }
        
        swal({
            title: productosConProblemas.length > 0 ? "Carga parcial" : "¡Productos cargados!",
            text: mensaje,
            type: productosConProblemas.length > 0 ? "warning" : "success",
            confirmButtonText: "Entendido"
        });
        
    } catch(error) {
        swal({
            title: "Error",
            text: "Error al procesar los productos de la solicitud",
            type: "error",
            confirmButtonText: "Cerrar"
        });
    }
}

/*=============================================
ABRIR MODAL PARA CANTIDAD
=============================================*/
function abrirModalCantidad(codigo, descripcion, stock) {
    
    $("#codigoProductoDespachoModal").val(codigo);
    $("#descripcionProductoDespachoModal").val(descripcion);
    $("#stockActualProductoDespachoModal").val(stock);
    
    $("#nombreProductoDespachoModal").text(descripcion);
    $("#stockProductoDespachoModal").text(stock);
    $("#maximoCantidadDespacho").text(stock);
    
    $("#cantidadProductoDespachoModal").val(1).attr("max", stock);
    $("#observacionProductoDespachoModal").val("");
    
    // Limpiar validaciones
    $("#cantidadProductoDespachoModal").removeClass("cantidad-invalida cantidad-valida");
    
    $("#modalCantidadProductoDespacho").modal("show");
    
    // Focus en cantidad
    setTimeout(function() {
        $("#cantidadProductoDespachoModal").focus().select();
    }, 500);
}

/*=============================================
VALIDAR CANTIDAD EN MODAL
=============================================*/
function validarCantidadModal() {
    
    var cantidad = parseInt($("#cantidadProductoDespachoModal").val()) || 0;
    var stockMaximo = parseInt($("#stockActualProductoDespachoModal").val()) || 0;
    var $input = $("#cantidadProductoDespachoModal");
    var $boton = $("#confirmarAgregarProductoDespacho");
    
    if(cantidad <= 0) {
        $input.removeClass("cantidad-valida").addClass("cantidad-invalida");
        $("#ayudaCantidadDespacho").html('<i class="fa fa-exclamation-triangle text-danger"></i> La cantidad debe ser mayor a 0');
        $boton.prop("disabled", true);
    } else if(cantidad > stockMaximo) {
        $input.removeClass("cantidad-valida").addClass("cantidad-invalida");
        $("#ayudaCantidadDespacho").html('<i class="fa fa-exclamation-triangle text-danger"></i> Cantidad excede el stock disponible');
        $boton.prop("disabled", true);
    } else {
        $input.removeClass("cantidad-invalida").addClass("cantidad-valida");
        $("#ayudaCantidadDespacho").html('<i class="fa fa-check-circle text-success"></i> Cantidad válida');
        $boton.prop("disabled", false);
    }
}

/*=============================================
CONFIRMAR AGREGAR PRODUCTO
=============================================*/
function confirmarAgregarProducto() {
    
    var codigo = $("#codigoProductoDespachoModal").val();
    var descripcion = $("#descripcionProductoDespachoModal").val();
    var cantidad = parseInt($("#cantidadProductoDespachoModal").val());
    var stock = parseInt($("#stockActualProductoDespachoModal").val());
    var observacion = $("#observacionProductoDespachoModal").val();
    
    // Validar que no exista ya el producto
    var existente = productosDespacho.find(function(p) {
        return p.codigo === codigo;
    });
    
    if(existente) {
        swal({
            title: "Producto duplicado",
            text: "Este producto ya está en el despacho. ¿Desea actualizar la cantidad?",
            type: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, actualizar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                // Actualizar cantidad existente
                existente.cantidad = cantidad;
                existente.observacion = observacion;
                
                actualizarVistaProductosDespacho();
                $("#modalCantidadProductoDespacho").modal("hide");
                
                swal({
                    title: "¡Cantidad actualizada!",
                    text: `Se actualizó la cantidad de ${descripcion}`,
                    type: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
        return;
    }
    
    // Agregar nuevo producto
    productosDespacho.push({
        codigo: codigo,
        descripcion: descripcion,
        cantidad: cantidad,
        stock_disponible: stock,
        observacion: observacion
    });
    
    actualizarVistaProductosDespacho();
    $("#modalCantidadProductoDespacho").modal("hide");
    
    swal({
        title: "¡Producto agregado!",
        text: `Se agregó ${cantidad} unidades de ${descripcion} al despacho`,
        type: "success",
        timer: 2000,
        showConfirmButton: false
    });
}

/*=============================================
ACTUALIZAR VISTA DE PRODUCTOS DESPACHO
=============================================*/
function actualizarVistaProductosDespacho() {
    
    if(productosDespacho.length === 0) {
        $("#productosDespachoSeleccionados").html(`
            <tr id="sinProductosDespacho">
                <td colspan="4" class="text-center text-muted">
                    <i class="fa fa-info-circle"></i> No hay productos agregados al despacho
                </td>
            </tr>
        `);
        
        $("#resumenDespacho").hide();
        $("#btnCrearDespacho").prop("disabled", true);
        $("#contadorProductosDespacho").text("0");
        
        return;
    }
    
    var html = '';
    var totalProductos = productosDespacho.length;
    var totalUnidades = 0;
    
    productosDespacho.forEach(function(producto, index) {
        
        totalUnidades += producto.cantidad;
        
        // Determinar clase de stock
        var claseStock = 'text-success';
        if(producto.cantidad > producto.stock_disponible) {
            claseStock = 'text-danger';
        } else if(producto.cantidad > (producto.stock_disponible * 0.8)) {
            claseStock = 'text-warning';
        }
        
        html += `
            <tr class="producto-agregado">
                <td>
                    <strong>${producto.codigo}</strong><br>
                    <small class="text-muted">${producto.descripcion}</small>
                    ${producto.observacion ? '<br><small class="text-info"><i class="fa fa-comment"></i> ' + producto.observacion + '</small>' : ''}
                </td>
                <td class="text-center">
                    <input type="number" 
                           class="form-control input-sm text-center" 
                           value="${producto.cantidad}" 
                           min="1" 
                           max="${producto.stock_disponible}"
                           onchange="actualizarCantidadProducto(${index}, this.value)"
                           style="width: 70px;">
                </td>
                <td class="text-center ${claseStock}">
                    <strong>${producto.stock_disponible}</strong>
                </td>
                <td class="text-center">
                    <button type="button" 
                            class="btn btn-danger btn-xs" 
                            onclick="eliminarProductoDespacho(${index})"
                            data-toggle="tooltip" 
                            title="Eliminar del despacho">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    $("#productosDespachoSeleccionados").html(html);
    
    // Actualizar resumen
    $("#totalProductosResumen").text(totalProductos);
    $("#totalUnidadesResumen").text(totalUnidades);
    $("#resumenDespacho").show();
    
    // Actualizar contador y habilitar botón
    $("#contadorProductosDespacho").text(totalProductos);
    $("#btnCrearDespacho").prop("disabled", false);
    
    // Reactivar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Actualizar campos ocultos
    $("#productosDespachoHidden").val(JSON.stringify(productosDespacho));
    $("#totalProductosHidden").val(totalProductos);
    $("#totalCantidadHidden").val(totalUnidades);
}

/*=============================================
ACTUALIZAR CANTIDAD DE PRODUCTO
=============================================*/
function actualizarCantidadProducto(indice, nuevaCantidad) {
    
    var cantidad = parseInt(nuevaCantidad) || 0;
    var producto = productosDespacho[indice];
    
    if(cantidad <= 0) {
        swal({
            title: "Cantidad inválida",
            text: "La cantidad debe ser mayor a 0",
            type: "error",
            confirmButtonText: "Cerrar"
        }).then(function() {
            // Restaurar cantidad anterior
            $(`#productosDespachoSeleccionados tr:eq(${indice}) input`).val(producto.cantidad);
        });
        return;
    }
    
    if(cantidad > producto.stock_disponible) {
        swal({
            title: "Stock insuficiente",
            text: `Solo hay ${producto.stock_disponible} unidades disponibles`,
            type: "warning",
            confirmButtonText: "Cerrar"
        }).then(function() {
            // Restaurar cantidad anterior
            $(`#productosDespachoSeleccionados tr:eq(${indice}) input`).val(producto.cantidad);
        });
        return;
    }
    
    // Actualizar cantidad
    productosDespacho[indice].cantidad = cantidad;
    
    // Actualizar vista
    actualizarVistaProductosDespacho();
    
    // Mostrar confirmación
    toastr.success(`Cantidad actualizada: ${cantidad} unidades`, "✅ Actualizado", {
        timeOut: 2000
    });
}

/*=============================================
ELIMINAR PRODUCTO DEL DESPACHO
=============================================*/
function eliminarProductoDespacho(indice) {
    
    var producto = productosDespacho[indice];
    
    swal({
        title: "¿Eliminar producto?",
        text: `Se eliminará "${producto.descripcion}" del despacho`,
        type: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if(result.value) {
            
            // Eliminar producto del array
            productosDespacho.splice(indice, 1);
            
            // Actualizar vista
            actualizarVistaProductosDespacho();
            
            swal({
                title: "¡Producto eliminado!",
                text: `Se eliminó "${producto.descripcion}" del despacho`,
                type: "success",
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

/*=============================================
VALIDAR STOCK DE PRODUCTOS
=============================================*/
function validarStockProductos() {
    
    if(productosDespacho.length === 0) {
        swal({
            title: "No hay productos",
            text: "Agregue productos al despacho para validar stock",
            type: "info",
            confirmButtonText: "Entendido"
        });
        return;
    }
    
    var problemasStock = [];
    var promesasValidacion = [];
    
    productosDespacho.forEach(function(producto, indice) {
        
        var promesa = new Promise(function(resolve, reject) {
            
            $.ajax({
                url: "ajax/productos-despacho.ajax.php",
                method: "POST",
                data: {
                    validarStock: true,
                    codigoProducto: producto.codigo,
                    cantidad: producto.cantidad
                },
                dataType: "json",
                success: function(respuesta) {
                    
                    if(respuesta.success && !respuesta.valido) {
                        problemasStock.push({
                            indice: indice,
                            codigo: producto.codigo,
                            descripcion: producto.descripcion,
                            cantidad_solicitada: producto.cantidad,
                            stock_actual: respuesta.stock_disponible,
                            mensaje: respuesta.mensaje
                        });
                    }
                    
                    resolve();
                },
                error: function() {
                    problemasStock.push({
                        indice: indice,
                        codigo: producto.codigo,
                        descripcion: producto.descripcion,
                        cantidad_solicitada: producto.cantidad,
                        stock_actual: 0,
                        mensaje: "Error verificando stock"
                    });
                    resolve();
                }
            });
        });
        
        promesasValidacion.push(promesa);
    });
    
    // Esperar todas las validaciones
    Promise.all(promesasValidacion).then(function() {
        
        if(problemasStock.length === 0) {
            
            $("#alertaValidacionStock").hide();
            
            swal({
                title: "✅ Stock validado",
                text: "Todos los productos tienen stock suficiente",
                type: "success",
                confirmButtonText: "Perfecto"
            });
            
        } else {
            
            // Mostrar problemas encontrados
            var listaProblemas = '';
            problemasStock.forEach(function(problema) {
                listaProblemas += `
                    <li class="text-danger">
                        <strong>${problema.codigo}</strong>: ${problema.mensaje}
                        <br><small>Solicitado: ${problema.cantidad_solicitada}, Disponible: ${problema.stock_actual}</small>
                    </li>
                `;
                
                // Marcar fila con problema
                $(`#productosDespachoSeleccionados tr:eq(${problema.indice})`).addClass('producto-problema-stock');
            });
            
            $("#listaProblemasStock").html(listaProblemas);
            $("#alertaValidacionStock").show();
            
            swal({
                title: "⚠️ Problemas de stock",
                text: `Se encontraron ${problemasStock.length} productos con problemas de stock`,
                type: "warning",
                confirmButtonText: "Revisar"
            });
        }
    });
}

/*=============================================
LIMPIAR FILTRO LOCAL
=============================================*/
function limpiarFiltroLocal() {
    $("#filtroProductosLocal").val("");
    mostrarProductosInventario(inventarioLocal);
}

/*=============================================
ACTUALIZAR INVENTARIO LOCAL
=============================================*/
function actualizarInventarioLocal() {
    
    var $boton = $(".btn[onclick='actualizarInventarioLocal()']");
    var iconoOriginal = $boton.find("i").attr("class");
    
    $boton.find("i").attr("class", "fa fa-spinner fa-spin");
    $boton.prop("disabled", true);
    
    cargarProductosInventario();
    
    setTimeout(function() {
        $boton.find("i").attr("class", iconoOriginal);
        $boton.prop("disabled", false);
        
        toastr.success("Inventario actualizado", "✅ Actualizado", {
            timeOut: 2000
        });
    }, 1000);
}

/*=============================================
ENVIAR FORMULARIO DE DESPACHO
=============================================*/
function enviarFormularioDespacho() {
    
    if(productosDespacho.length === 0) {
        swal({
            title: "No hay productos",
            text: "Debe agregar al menos un producto al despacho",
            type: "warning",
            confirmButtonText: "Entendido"
        });
        return;
    }
    
    // Validar stock antes de enviar
    var hayProblemas = false;
    productosDespacho.forEach(function(producto) {
        if(producto.cantidad > producto.stock_disponible) {
            hayProblemas = true;
        }
    });
    
    if(hayProblemas) {
        swal({
            title: "⚠️ Problemas de stock",
            text: "Hay productos con problemas de stock. ¿Desea validar antes de continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Validar Stock",
            cancelButtonText: "Continuar de todas formas"
        }).then(function(result) {
            if(result.value) {
                validarStockProductos();
            } else {
                confirmarEnvioDespacho();
            }
        });
        return;
    }
    
    confirmarEnvioDespacho();
}

/*=============================================
CONFIRMAR ENVÍO DE DESPACHO
=============================================*/
function confirmarEnvioDespacho() {
    
    var totalProductos = productosDespacho.length;
    var totalUnidades = productosDespacho.reduce(function(sum, p) { return sum + p.cantidad; }, 0);
    
    var mensaje = `¿Confirma la creación del despacho con:\n\n`;
    mensaje += `• ${totalProductos} productos diferentes\n`;
    mensaje += `• ${totalUnidades} unidades totales\n`;
    
    if(solicitudSeleccionada) {
        mensaje += `• Basado en solicitud: ${solicitudSeleccionada.codigo_solicitud}`;
    }
    
    swal({
        title: "Confirmar creación",
        text: mensaje,
        type: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, crear despacho",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if(result.value) {
            
            // Deshabilitar botón y mostrar carga
            var $boton = $("#btnCrearDespacho");
            var textoOriginal = $boton.html();
            
            $boton.prop("disabled", true)
                  .html('<i class="fa fa-spinner fa-spin"></i> Creando despacho...');
            
            // Enviar formulario
            $("#formCrearDespacho").submit();
        }
    });
}

/*=============================================
OCULTAR RESULTADOS DE SOLICITUDES
=============================================*/
function ocultarResultadosSolicitudes() {
    $("#resultadosBusquedaSolicitudes").remove();
}

/*=============================================
MOSTRAR SIN RESULTADOS SOLICITUDES
=============================================*/
function mostrarSinResultadosSolicitudes() {
    $("#numeroSolicitudBuscar").after(`
        <div id="resultadosBusquedaSolicitudes" class="alert alert-info" 
             style="margin-top: 5px; padding: 10px;">
            <i class="fa fa-info-circle"></i> No se encontraron solicitudes con ese criterio
        </div>
    `);
    
    setTimeout(function() {
        $("#resultadosBusquedaSolicitudes").fadeOut(function() {
            $(this).remove();
        });
    }, 3000);
}

/*=============================================
MOSTRAR ERROR BÚSQUEDA SOLICITUDES
=============================================*/
function mostrarErrorBusquedaSolicitudes() {
    $("#numeroSolicitudBuscar").after(`
        <div id="resultadosBusquedaSolicitudes" class="alert alert-danger" 
             style="margin-top: 5px; padding: 10px;">
            <i class="fa fa-exclamation-triangle"></i> Error al buscar solicitudes
        </div>
    `);
    
    setTimeout(function() {
        $("#resultadosBusquedaSolicitudes").fadeOut(function() {
            $(this).remove();
        });
    }, 3000);
}

/*=============================================
CONFIGURAR FILTRO DE PRODUCTOS
=============================================*/
function configurarFiltroProductos() {
    
    // Filtro en tiempo real con debounce
    var timeoutFiltro;
    
    $("#filtroProductosLocal").on("keyup", function() {
        clearTimeout(timeoutFiltro);
        var termino = $(this).val();
        
        timeoutFiltro = setTimeout(function() {
            filtrarProductosLocal(termino);
        }, 300);
    });
    
    // Limpiar con ESC
    $("#filtroProductosLocal").on("keydown", function(e) {
        if(e.which === 27) { // ESC
            $(this).val("");
            mostrarProductosInventario(inventarioLocal);
        }
    });
}

/*=============================================
ATAJOS DE TECLADO
=============================================*/
$(document).on("keydown", function(e) {
    
    // Ctrl + F para enfocar filtro
    if(e.ctrlKey && e.which === 70) {
        $("#filtroProductosLocal").focus();
        e.preventDefault();
    }
    
    // ESC para cerrar modales
    if(e.which === 27) {
        $(".modal").modal("hide");
        ocultarResultadosSolicitudes();
    }
    
    // Enter en modal de cantidad
    if(e.which === 13 && $("#modalCantidadProductoDespacho").hasClass("in")) {
        if(!$("#confirmarAgregarProductoDespacho").prop("disabled")) {
            confirmarAgregarProducto();
        }
        e.preventDefault();
    }
});

/*=============================================
CLEANUP AL SALIR
=============================================*/
$(window).on('beforeunload', function(e) {
    if(productosDespacho.length > 0) {
        var mensaje = 'Hay productos agregados al despacho. ¿Está seguro de salir?';
        e.returnValue = mensaje;
        return mensaje;
    }
});

/*=============================================
CONFIGURAR TOASTR (NOTIFICACIONES)
=============================================*/
if(typeof toastr !== 'undefined') {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
}

/*=============================================
LOG DE INICIALIZACIÓN
=============================================*/
console.log("✅ JavaScript de crear despacho cargado completamente");
console.log("🚛 Productos en despacho:", productosDespacho.length);
console.log("📦 Inventario local:", inventarioLocal.length);