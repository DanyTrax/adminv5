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
MOSTRAR RESULTADOS DE SOLICITUDES - CORREGIDO
=============================================*/
function mostrarResultadosSolicitudes(solicitudes) {
    
    // Eliminar resultados anteriores
    ocultarResultadosSolicitudes();
    
    var html = '<div class="list-group" style="max-height: 200px; overflow-y: auto;">';
    
    solicitudes.forEach(function(solicitud) {
        var fecha = new Date(solicitud.fecha_solicitud).toLocaleDateString();
        var estadoClass = solicitud.estado === 'aprobado' ? 'success' : 'warning';
        
        html += `
            <a href="#" 
               class="list-group-item list-group-item-action" 
               onclick="seleccionarSolicitud(${solicitud.id})"
               style="padding: 8px 12px;">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        <code>${solicitud.numero_solicitud}</code>
                        <span class="label label-${estadoClass}">${solicitud.estado.toUpperCase()}</span>
                    </h6>
                    <small>${fecha}</small>
                </div>
                <p class="mb-1">
                    <strong>Usuario:</strong> ${solicitud.nombre_usuario_solicitante}<br>
                    <strong>Productos:</strong> ${solicitud.total_productos} 
                    (<strong>${solicitud.total_cantidad}</strong> unidades)
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
SELECCIONAR SOLICITUD - CORREGIDO
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
                $("#numeroSolicitudBuscar").val(solicitudSeleccionada.numero_solicitud);
                
                var infoHtml = `
                    <strong>Código:</strong> ${solicitudSeleccionada.numero_solicitud}<br>
                    <strong>Usuario:</strong> ${solicitudSeleccionada.nombre_usuario_solicitante}<br>
                    <strong>Origen:</strong> ${solicitudSeleccionada.nombre_sucursal_solicitante}<br>
                    <strong>Estado:</strong> <span class="label label-${solicitudSeleccionada.estado === 'aprobado' ? 'success' : 'warning'}">${solicitudSeleccionada.estado.toUpperCase()}</span><br>
                    <strong>Productos:</strong> ${solicitudSeleccionada.total_productos} (${solicitudSeleccionada.total_cantidad} unidades)
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
CARGAR PRODUCTOS DE SOLICITUD - VERSIÓN COMPLETA CON FALTANTES
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
        var productos = JSON.parse(solicitudSeleccionada.productos_solicitados);
        
        var productosAgregados = 0;
        var productosActualizados = 0;
        var productosSinStock = [];           // Productos que no tienen nada de stock
        var productosConFaltantes = [];       // Productos con stock parcial
        
        productos.forEach(function(producto) {
            
            // Verificar stock disponible
            var productoInventario = inventarioLocal.find(function(p) {
                return p.codigo === producto.codigo;
            });
            
            if(productoInventario && productoInventario.stock > 0) {
                
                // HAY STOCK (completo o parcial)
                var cantidadAgregar = Math.min(producto.cantidad, productoInventario.stock);
                var cantidadFaltante = producto.cantidad - cantidadAgregar;
                
                // Verificar si el producto ya existe en el despacho
                var productoExistente = productosDespacho.find(function(p) {
                    return p.codigo === producto.codigo;
                });
                
                if(productoExistente) {
                    // PRODUCTO EXISTE: sumar cantidad y actualizar observación
                    var cantidadAnterior = productoExistente.cantidad;
                    var nuevaCantidad = cantidadAnterior + cantidadAgregar;
                    
                    // Verificar que no exceda el stock total disponible
                    if(nuevaCantidad <= productoInventario.stock) {
                        productoExistente.cantidad = nuevaCantidad;
                        
                        // Crear observación según si faltan productos o no
                        var observacionAnterior = productoExistente.observacion || "";
                        var nuevaObservacion = "";
                        
                        if(cantidadFaltante > 0) {
                            // STOCK PARCIAL: Agregar nota de faltantes
                            nuevaObservacion = observacionAnterior.length > 0 ? 
                                             `${observacionAnterior}, ${cantidadAgregar} de ${solicitudSeleccionada.numero_solicitud} (faltaron ${cantidadFaltante})` :
                                             `${cantidadAgregar} de ${solicitudSeleccionada.numero_solicitud} (faltaron ${cantidadFaltante})`;
                            
                            // Registrar en faltantes
                            productosConFaltantes.push({
                                codigo: producto.codigo,
                                faltantes: cantidadFaltante
                            });
                        } else {
                            // STOCK COMPLETO: Observación normal
                            nuevaObservacion = observacionAnterior.length > 0 ? 
                                             `${observacionAnterior}, ${cantidadAgregar} de ${solicitudSeleccionada.numero_solicitud}` :
                                             `${cantidadAgregar} de ${solicitudSeleccionada.numero_solicitud}`;
                        }
                        
                        productoExistente.observacion = nuevaObservacion;
                        productosActualizados++;
                        
                    } else {
                        // No se puede agregar toda la cantidad por exceder stock
                        var cantidadDisponible = productoInventario.stock - cantidadAnterior;
                        if(cantidadDisponible > 0) {
                            productoExistente.cantidad = productoInventario.stock;
                            var cantidadRealAgregada = cantidadDisponible;
                            var cantidadTotalFaltante = producto.cantidad - cantidadRealAgregada;
                            
                            var observacionAnterior = productoExistente.observacion || "";
                            var nuevaObservacion = observacionAnterior.length > 0 ? 
                                                   `${observacionAnterior}, ${cantidadRealAgregada} de ${solicitudSeleccionada.numero_solicitud} (faltaron ${cantidadTotalFaltante})` :
                                                   `${cantidadRealAgregada} de ${solicitudSeleccionada.numero_solicitud} (faltaron ${cantidadTotalFaltante})`;
                            
                            productoExistente.observacion = nuevaObservacion;
                            productosActualizados++;
                            
                            // Registrar en faltantes
                            productosConFaltantes.push({
                                codigo: producto.codigo,
                                faltantes: cantidadTotalFaltante
                            });
                        } else {
                            // No hay espacio para agregar nada
                            productosSinStock.push(producto.codigo);
                        }
                    }
                    
                } else {
                    // PRODUCTO NUEVO: agregar al despacho
                    var observacionProducto = "";
                    
                    if(cantidadFaltante > 0) {
                        // STOCK PARCIAL: Nota de faltantes
                        observacionProducto = `${cantidadAgregar} de ${solicitudSeleccionada.numero_solicitud} (faltaron ${cantidadFaltante})`;
                        
                        // Registrar en faltantes
                        productosConFaltantes.push({
                            codigo: producto.codigo,
                            faltantes: cantidadFaltante
                        });
                    } else {
                        // STOCK COMPLETO: Observación normal
                        observacionProducto = `${cantidadAgregar} de ${solicitudSeleccionada.numero_solicitud}`;
                    }
                    
                    productosDespacho.push({
                        codigo: producto.codigo,
                        descripcion: producto.descripcion,
                        cantidad: cantidadAgregar,
                        stock_disponible: productoInventario.stock,
                        observacion: observacionProducto
                    });
                    
                    productosAgregados++;
                }
                
            } else {
                // SIN STOCK: No agregar, solo registrar
                productosSinStock.push(producto.codigo);
            }
        });
        
// CREAR NOTA GENERAL PARA OBSERVACIONES DEL DESPACHO
var notaGeneral = "";
var tieneProblemas = productosSinStock.length > 0 || productosConFaltantes.length > 0;

if(tieneProblemas) {
    var detallesFaltantes = [];
    
    // Agregar productos sin stock
    if(productosSinStock.length > 0) {
        productosSinStock.forEach(function(codigo) {
            detallesFaltantes.push(codigo + "(sin stock)");
        });
    }
    
    // Agregar productos con faltantes
    if(productosConFaltantes.length > 0) {
        productosConFaltantes.forEach(function(item) {
            detallesFaltantes.push(item.codigo + "(" + item.faltantes + " faltantes)");
        });
    }
    
    notaGeneral = `FALTANTES ${solicitudSeleccionada.numero_solicitud}: ${detallesFaltantes.join(', ')}`;
    
    // CORRECCIÓN: Buscar el campo de observaciones con múltiples selectores
    var $campoObservaciones = $("#observacionesDespacho").length > 0 ? $("#observacionesDespacho") :
                             $("#observaciones").length > 0 ? $("#observaciones") :
                             $("#detalleAdicional").length > 0 ? $("#detalleAdicional") :
                             $("textarea[name*='observacion']").first();
    
    if($campoObservaciones.length > 0) {
// Agregar al campo observaciones del despacho (CORREGIDO)
var observacionesActuales = $("#detalleAdicional").val() || "";
var nuevasObservaciones = observacionesActuales.length > 0 ? 
                        `${observacionesActuales}\n${notaGeneral}` : 
                        notaGeneral;
$("#detalleAdicional").val(nuevasObservaciones);

console.log("✅ Nota agregada al campo detalleAdicional:", notaGeneral);
        
    } else {
        console.warn("⚠️ No se encontró campo de observaciones. Nota generada:", notaGeneral);
        
        // Mostrar la nota en la consola para que puedas verla
        console.log("📝 NOTA PARA OBSERVACIONES:", notaGeneral);
    }
}
        
        // Actualizar vista
        actualizarVistaProductosDespacho();
        
        // MOSTRAR RESULTADO DETALLADO
        var mensaje = "";
        var tipoMensaje = "success";
        
        // Productos procesados exitosamente
        if(productosAgregados > 0 || productosActualizados > 0) {
            mensaje += `✅ Procesados de ${solicitudSeleccionada.numero_solicitud}:\n`;
            if(productosAgregados > 0) {
                mensaje += `• Agregados: ${productosAgregados} productos\n`;
            }
            if(productosActualizados > 0) {
                mensaje += `• Actualizados: ${productosActualizados} productos\n`;
            }
        }
        
        // Productos con problemas
        if(tieneProblemas) {
            mensaje += `\n⚠️ Productos con problemas:\n`;
            
            if(productosSinStock.length > 0) {
                mensaje += `• Sin stock (${productosSinStock.length}): ${productosSinStock.join(', ')}\n`;
            }
            
            if(productosConFaltantes.length > 0) {
                mensaje += `• Con faltantes (${productosConFaltantes.length}): `;
                var listaFaltantes = productosConFaltantes.map(function(item) {
                    return `${item.codigo}(-${item.faltantes})`;
                });
                mensaje += listaFaltantes.join(', ') + '\n';
            }
            
            mensaje += `\nSe agregó detalle en "Observaciones"`;
            tipoMensaje = productosAgregados > 0 || productosActualizados > 0 ? "warning" : "error";
        }
        
        // Mostrar notificación
        swal({
            title: tieneProblemas ? "Carga completada con observaciones" : "¡Productos cargados!",
            text: mensaje,
            type: tipoMensaje,
            confirmButtonText: "Entendido"
        });
        
        // Limpiar campo de búsqueda para permitir agregar otra solicitud
        $("#numeroSolicitudBuscar").val("").focus();
        
    } catch(error) {
        console.error("Error procesando solicitud:", error);
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
CONFIRMAR AGREGAR PRODUCTO - VERSIÓN CORREGIDA
=============================================*/
function confirmarAgregarProducto() {
    
    var codigo = $("#codigoProductoDespachoModal").val();
    var descripcion = $("#descripcionProductoDespachoModal").val();
    var cantidadNueva = parseInt($("#cantidadProductoDespachoModal").val());
    var stock = parseInt($("#stockActualProductoDespachoModal").val());
    var observacionNueva = $("#observacionProductoDespachoModal").val();
    
    // Validar que no exista ya el producto
    var existente = productosDespacho.find(function(p) {
        return p.codigo === codigo;
    });
    
    if(existente) {
        // Producto existe: SUMAR cantidades y CONCATENAR observaciones
        
        var cantidadAnterior = existente.cantidad;
        var cantidadTotal = cantidadAnterior + cantidadNueva;
        
        // Verificar que no exceda el stock disponible
        if(cantidadTotal > stock) {
            swal({
                title: "Stock insuficiente",
                text: `No se puede agregar ${cantidadNueva} unidades.\n\nActual en despacho: ${cantidadAnterior}\nStock disponible: ${stock}\nMáximo a agregar: ${stock - cantidadAnterior}`,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: `Agregar ${stock - cantidadAnterior} (máximo)`,
                cancelButtonText: "Cancelar"
            }).then(function(result) {
                if(result.value) {
                    // Agregar cantidad máxima disponible
                    var cantidadMaxima = stock - cantidadAnterior;
                    if(cantidadMaxima > 0) {
                        existente.cantidad = stock;
                        
                        // Concatenar observaciones
                        var observacionAnterior = existente.observacion || "";
                        var nuevaObservacionCompleta = "";
                        
                        if(observacionAnterior.length > 0 && observacionNueva.length > 0) {
                            nuevaObservacionCompleta = `${observacionAnterior}, ${cantidadMaxima} (${observacionNueva})`;
                        } else if(observacionAnterior.length > 0) {
                            nuevaObservacionCompleta = `${observacionAnterior}, +${cantidadMaxima}`;
                        } else if(observacionNueva.length > 0) {
                            nuevaObservacionCompleta = `${cantidadMaxima} (${observacionNueva})`;
                        } else {
                            nuevaObservacionCompleta = `+${cantidadMaxima}`;
                        }
                        
                        existente.observacion = nuevaObservacionCompleta;
                        
                        actualizarVistaProductosDespacho();
                        $("#modalCantidadProductoDespacho").modal("hide");
                        
                        swal({
                            title: "¡Cantidad actualizada!",
                            text: `Se agregaron ${cantidadMaxima} unidades de ${descripcion}\nTotal en despacho: ${existente.cantidad}`,
                            type: "success",
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                }
            });
            return;
        }
        
        // Stock suficiente: preguntar si desea sumar
        swal({
            title: "Producto ya existe",
            text: `Este producto ya está en el despacho con ${cantidadAnterior} unidades.\n\n¿Desea agregar ${cantidadNueva} unidades más?\n\nTotal final: ${cantidadTotal} unidades`,
            type: "question",
            showCancelButton: true,
            confirmButtonText: `Sí, sumar (${cantidadTotal} total)`,
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                // Sumar cantidades
                existente.cantidad = cantidadTotal;
                
                // Concatenar observaciones
                var observacionAnterior = existente.observacion || "";
                var nuevaObservacionCompleta = "";
                
                if(observacionAnterior.length > 0 && observacionNueva.length > 0) {
                    nuevaObservacionCompleta = `${observacionAnterior}, +${cantidadNueva} (${observacionNueva})`;
                } else if(observacionAnterior.length > 0) {
                    nuevaObservacionCompleta = `${observacionAnterior}, +${cantidadNueva}`;
                } else if(observacionNueva.length > 0) {
                    nuevaObservacionCompleta = `${cantidadNueva} (${observacionNueva})`;
                } else {
                    nuevaObservacionCompleta = existente.observacion; // Mantener observación anterior
                }
                
                existente.observacion = nuevaObservacionCompleta;
                
                actualizarVistaProductosDespacho();
                $("#modalCantidadProductoDespacho").modal("hide");
                
                swal({
                    title: "¡Cantidad sumada!",
                    text: `Se agregaron ${cantidadNueva} unidades de ${descripcion}\nTotal en despacho: ${cantidadTotal}`,
                    type: "success",
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
        return;
    }
    
    // Producto nuevo: agregar al despacho
    var observacionFinal = observacionNueva.length > 0 ? 
                          `${cantidadNueva} (${observacionNueva})` : 
                          "";
    
    productosDespacho.push({
        codigo: codigo,
        descripcion: descripcion,
        cantidad: cantidadNueva,
        stock_disponible: stock,
        observacion: observacionFinal
    });
    
    actualizarVistaProductosDespacho();
    $("#modalCantidadProductoDespacho").modal("hide");
    
    swal({
        title: "¡Producto agregado!",
        text: `Se agregaron ${cantidadNueva} unidades de ${descripcion} al despacho`,
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
ACTUALIZAR CANTIDAD DE PRODUCTO - VERSIÓN MEJORADA
=============================================*/
function actualizarCantidadProducto(indice, nuevaCantidad) {
    
    var cantidad = parseInt(nuevaCantidad) || 0;
    var producto = productosDespacho[indice];
    
    if(cantidad <= 0) {
        swal({
            title: "Cantidad inválida",
            text: "La cantidad debe ser mayor a 0. ¿Desea eliminar este producto del despacho?",
            type: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "No, mantener"
        }).then(function(result) {
            if(result.value) {
                eliminarProductoDespacho(indice);
            } else {
                // Restaurar cantidad anterior
                $(`#productosDespachoSeleccionados tr:eq(${indice}) input`).val(producto.cantidad);
            }
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
    var cantidadAnterior = producto.cantidad;
    productosDespacho[indice].cantidad = cantidad;
    
    // Si cambió la cantidad y hay observación, actualizar observación para reflejar el cambio
    if(producto.observacion && producto.observacion.length > 0) {
        var diferencia = cantidad - cantidadAnterior;
        if(diferencia !== 0) {
            var textoAdicional = diferencia > 0 ? ` (+${diferencia} ajustado)` : ` (${diferencia} ajustado)`;
            if(!producto.observacion.includes("ajustado")) {
                productosDespacho[indice].observacion += textoAdicional;
            }
        }
    }
    
    // Actualizar vista
    actualizarVistaProductosDespacho();
    
    // Mostrar confirmación
    if(typeof toastr !== 'undefined') {
        toastr.success(`Cantidad actualizada: ${cantidad} unidades`, "✅ Actualizado", {
            timeOut: 2000
        });
    }
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
        
        if(typeof toastr !== 'undefined') {
            toastr.success("Inventario actualizado", "✅ Actualizado", {
                timeOut: 2000
            });
        }
    }, 1000);
}

/*=============================================
ENVIAR FORMULARIO DE DESPACHO
=============================================*/
function enviarFormularioDespacho() {
    
    if(productosDespacho.length === 0) {
        swal({
            title: "No hay productos",
            text: "Agregue productos al despacho antes de crear",
            type: "warning",
            confirmButtonText: "Entendido"
        });
        return false;
    }
    
    // Validar campos obligatorios y auto-detectar tipo
    var observaciones = $("#detalleAdicional").val();

    // AUTO-DETECCIÓN INTELIGENTE DEL TIPO DE DESPACHO
    var tipoDespacho = "libre"; // Por defecto
    var tieneSolicitudes = false;
    var tieneProductosLibres = false;

    // Verificar si hay productos agregados desde solicitudes
    productosDespacho.forEach(function(producto) {
        if(producto.observacion && producto.observacion.includes("SOL")) {
            tieneSolicitudes = true;
        } else {
            tieneProductosLibres = true;
        }
    });

    // Determinar tipo según el contenido
    if(tieneSolicitudes && tieneProductosLibres) {
        tipoDespacho = "hibrido"; // NUEVO: Despacho combinado
    } else if(tieneSolicitudes) {
        tipoDespacho = "solicitud"; // Solo desde solicitudes
    } else {
        tipoDespacho = "libre"; // Solo productos manuales
    }

    console.log("🎯 Tipo de despacho detectado:", tipoDespacho);
    console.log("📋 Análisis:", {
        tieneSolicitudes: tieneSolicitudes,
        tieneProductosLibres: tieneProductosLibres,
        totalProductos: productosDespacho.length
    });
    
    // Mostrar confirmación
    var tipoTexto = "";
    switch(tipoDespacho) {
        case "libre":
            tipoTexto = "Despacho libre (productos seleccionados manualmente)";
            break;
        case "solicitud":
            tipoTexto = "Despacho desde solicitud";
            break;
        case "hibrido":
            tipoTexto = "Despacho híbrido (solicitudes + productos manuales)";
            break;
    }

    var mensaje = `
        Se creará un despacho con:
        • ${productosDespacho.length} productos diferentes
        • ${productosDespacho.reduce((total, p) => total + p.cantidad, 0)} unidades totales
        • Tipo: ${tipoTexto}
    `;

    if(solicitudSeleccionada && (tipoDespacho === 'solicitud' || tipoDespacho === 'hibrido')) {
        mensaje += `\n• Última solicitud procesada: ${solicitudSeleccionada.numero_solicitud}`;
    }
    
    swal({
        title: "¿Crear despacho?",
        text: mensaje,
        type: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, crear despacho",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if(result.value) {
            procesarCreacionDespacho(tipoDespacho); // CORREGIDO: Pasar tipoDespacho
        }
    });
    
    return false; // Evitar envío normal del formulario
}

/*=============================================
PROCESAR CREACIÓN DE DESPACHO
=============================================*/
function procesarCreacionDespacho(tipoDespacho) {
    
    // Mostrar loading
    $("#btnCrearDespacho").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Creando despacho...');
    
    // Preparar datos (NOMBRES CORREGIDOS)
    var datosDespacho = {
        crearDespacho: true,
        productosDespacho: JSON.stringify(productosDespacho),    // Sin underscore
        totalProductos: productosDespacho.length,               // Sin underscore
        totalCantidad: productosDespacho.reduce((total, p) => total + p.cantidad, 0), // Sin underscore
        tipoDespacho: tipoDespacho,                             // Sin underscore
        detalleAdicional: $("#detalleAdicional").val(),         // Nombre correcto
        idSolicitudOrigen: solicitudSeleccionada ? solicitudSeleccionada.id : null // Sin underscore
    };
    
    console.log("📦 Datos del despacho a enviar:", datosDespacho);
    
    // Enviar datos al AJAX correcto
    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datosDespacho,
        dataType: "json",
        success: function(respuesta) {
            
            $("#btnCrearDespacho").prop("disabled", false).html('<i class="fa fa-truck"></i> Crear despacho');
            
            console.log("✅ Respuesta del servidor:", respuesta);
            
            if(respuesta.success) {
                
                swal({
                    title: "¡Despacho creado!",
                    text: `Se creó el despacho: ${respuesta.numero_despacho || 'Exitosamente'}`,
                    type: "success",
                    confirmButtonText: "Ver despachos"
                }).then(function() {
                    window.location.href = "index.php?ruta=despachos";
                });
                
            } else {
                swal({
                    title: "Error al crear despacho",
                    text: respuesta.error || "Error desconocido",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            
            $("#btnCrearDespacho").prop("disabled", false).html('<i class="fa fa-truck"></i> Crear despacho');
            
            console.error("Error AJAX:", error);
            console.error("Status:", status);
            console.error("Response Text:", xhr.responseText);
            
            swal({
                title: "Error de conexión",
                text: "No se pudo crear el despacho. Revise la consola para más detalles.",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
MOSTRAR SIN RESULTADOS DE SOLICITUDES
=============================================*/
function mostrarSinResultadosSolicitudes() {
    
    ocultarResultadosSolicitudes();
    
    $("#numeroSolicitudBuscar").after(`
        <div id="resultadosBusquedaSolicitudes" class="dropdown-menu" 
             style="display: block; position: relative; width: 100%; margin-top: 5px;">
            <div class="list-group-item text-center text-muted">
                <i class="fa fa-search"></i> No se encontraron solicitudes con ese criterio
            </div>
        </div>
    `);
}

/*=============================================
MOSTRAR ERROR BÚSQUEDA SOLICITUDES
=============================================*/
function mostrarErrorBusquedaSolicitudes() {
    
    ocultarResultadosSolicitudes();
    
    $("#numeroSolicitudBuscar").after(`
        <div id="resultadosBusquedaSolicitudes" class="dropdown-menu" 
             style="display: block; position: relative; width: 100%; margin-top: 5px;">
            <div class="list-group-item text-center text-danger">
                <i class="fa fa-exclamation-triangle"></i> Error buscando solicitudes
            </div>
        </div>
    `);
}

/*=============================================
OCULTAR RESULTADOS DE SOLICITUDES
=============================================*/
function ocultarResultadosSolicitudes() {
    $("#resultadosBusquedaSolicitudes").remove();
}

/*=============================================
LIMPIAR SOLICITUD SELECCIONADA - VERSIÓN MEJORADA
=============================================*/
function limpiarSolicitudSeleccionada() {
    
    solicitudSeleccionada = null;
    $("#numeroSolicitudBuscar").val("").focus();
    $("#infoSolicitudEncontrada").hide();
    $("#idSolicitudOrigenHidden").val("");
    ocultarResultadosSolicitudes();
    
    swal({
        title: "Solicitud limpiada",
        text: "Puede buscar y agregar otra solicitud al mismo despacho",
        type: "info",
        timer: 2000,
        showConfirmButton: false
    });
}

/*=============================================
CONFIGURAR FILTRO DE PRODUCTOS
=============================================*/
function configurarFiltroProductos() {
    
    // Configurar filtro con delay
    var timeoutFiltro;
    
    $("#filtroProductosLocal").on("input", function() {
        var $input = $(this);
        
        clearTimeout(timeoutFiltro);
        
        timeoutFiltro = setTimeout(function() {
            filtrarProductosLocal($input.val());
        }, 300);
    });
    
    // Limpiar filtro con Escape
    $("#filtroProductosLocal").on("keydown", function(e) {
        if(e.keyCode === 27) { // Escape
            $(this).val("");
            filtrarProductosLocal("");
        }
    });
}

/*=============================================
EXPORTAR LISTA DE PRODUCTOS DESPACHO
=============================================*/
function exportarListaProductosDespacho() {
    
    if(productosDespacho.length === 0) {
        swal({
            title: "No hay productos",
            text: "No hay productos en el despacho para exportar",
            type: "info",
            confirmButtonText: "Entendido"
        });
        return;
    }
    
    // Crear CSV
    var csv = "Código,Descripción,Cantidad,Stock Disponible,Observación\n";
    
    productosDespacho.forEach(function(producto) {
        csv += `"${producto.codigo}","${producto.descripcion}","${producto.cantidad}","${producto.stock_disponible}","${producto.observacion || ''}"\n`;
    });
    
    // Descargar archivo
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement("a");
    var url = URL.createObjectURL(blob);
    
    link.setAttribute("href", url);
    link.setAttribute("download", `productos-despacho-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    swal({
        title: "¡Exportado!",
        text: "Se descargó la lista de productos en formato CSV",
        type: "success",
        timer: 2000,
        showConfirmButton: false
    });
}

/*=============================================
IMPRIMIR LISTA DE PRODUCTOS DESPACHO
=============================================*/
function imprimirListaProductosDespacho() {
    
    if(productosDespacho.length === 0) {
        swal({
            title: "No hay productos",
            text: "No hay productos en el despacho para imprimir",
            type: "info",
            confirmButtonText: "Entendido"
        });
        return;
    }
    
    var fechaActual = new Date().toLocaleDateString();
    var horaActual = new Date().toLocaleTimeString();
    
    var contenidoImprimir = `
        <html>
        <head>
            <title>Lista de Productos - Despacho</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .info { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .text-center { text-align: center; }
                .total-row { background-color: #f9f9f9; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>LISTA DE PRODUCTOS PARA DESPACHO</h2>
                <p>Fecha: ${fechaActual} - Hora: ${horaActual}</p>
            </div>
            
            <div class="info">
                <p><strong>Total de productos:</strong> ${productosDespacho.length}</p>
                <p><strong>Total de unidades:</strong> ${productosDespacho.reduce((total, p) => total + p.cantidad, 0)}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Stock Disponible</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    productosDespacho.forEach(function(producto) {
        contenidoImprimir += `
            <tr>
                <td><strong>${producto.codigo}</strong></td>
                <td>${producto.descripcion}</td>
                <td class="text-center">${producto.cantidad}</td>
                <td class="text-center">${producto.stock_disponible}</td>
                <td>${producto.observacion || '-'}</td>
            </tr>
        `;
    });
    
    contenidoImprimir += `
                </tbody>
            </table>
        </body>
        </html>
    `;
    
    var ventanaImprimir = window.open('', '_blank');
    ventanaImprimir.document.write(contenidoImprimir);
    ventanaImprimir.document.close();
    ventanaImprimir.focus();
    ventanaImprimir.print();
}

/*=============================================
EVENTOS ESPECIALES
=============================================*/
$(document).ready(function() {
    
    // Cerrar resultados al hacer clic fuera
    $(document).on("click", function(e) {
        if (!$(e.target).closest("#numeroSolicitudBuscar, #resultadosBusquedaSolicitudes").length) {
            ocultarResultadosSolicitudes();
        }
    });
    
    // Atajo de teclado para limpiar filtro (Ctrl + L)
    $(document).on("keydown", function(e) {
        if(e.ctrlKey && e.keyCode === 76) {
            e.preventDefault();
            $("#filtroProductosLocal").val("").focus();
            filtrarProductosLocal("");
        }
    });
    
    // Atajo para validar stock (Ctrl + V)
    $(document).on("keydown", function(e) {
        if(e.ctrlKey && e.keyCode === 86) {
            e.preventDefault();
            validarStockProductos();
        }
    });
    
    // Prevenir envío del formulario con Enter en campos de texto
    $("#numeroSolicitudBuscar, #filtroProductosLocal").on("keydown", function(e) {
        if(e.keyCode === 13) {
            e.preventDefault();
        }
    });
    
});