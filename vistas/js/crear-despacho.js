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
    
    // Configurar evento del botón de confirmar agregar producto
    $("#confirmarAgregarProductoDespacho").on("click", function() {
        console.log("🔍 DEBUG: Botón confirmarAgregarProductoDespacho clickeado");
        confirmarAgregarProducto();
    });
    
    // CARGAR DESDE SOLICITUD SI ES NECESARIO
    if(typeof window.cargarDesdeSolicitud !== 'undefined' && window.cargarDesdeSolicitud) {
        console.log("🚛 Cargando despacho desde solicitud...");
        // Usar timeout para evitar ejecución inmediata que causa duplicación
        setTimeout(function() {
            cargarProductosDesdeSolicitud();
        }, 500);
    }
    
    // PRUEBA INMEDIATA DEL ELEMENTO
    setTimeout(function() {
        console.log("🧪 PRUEBA INMEDIATA - Verificando elemento numeroSolicitudBuscar");
        console.log("🧪 Elemento existe:", $("#numeroSolicitudBuscar").length > 0);
        console.log("🧪 Elemento visible:", $("#numeroSolicitudBuscar").is(":visible"));
        console.log("🧪 Elemento habilitado:", !$("#numeroSolicitudBuscar").prop("disabled"));
        console.log("🧪 Valor actual:", $("#numeroSolicitudBuscar").val());
        
        if($("#numeroSolicitudBuscar").length > 0) {
            console.log("✅ Elemento encontrado - configurando eventos de prueba");
            
            // Agregar evento de prueba simple
            $("#numeroSolicitudBuscar").on("click", function() {
                console.log("🖱️ CLICK detectado en numeroSolicitudBuscar");
            });
            
            // Probar función de búsqueda directamente
            window.probarBusqueda = function() {
                console.log("🧪 Probando búsqueda con 'SOL000005'");
                buscarSolicitudesStock("SOL000005");
            };
            
            // Probar función de búsqueda con término corto
            window.probarBusquedaCorta = function() {
                console.log("🧪 Probando búsqueda con 'SOL'");
                buscarSolicitudesStock("SOL");
            };
            
            console.log("🧪 Funciones de prueba disponibles:");
            console.log("   - probarBusqueda() - busca 'SOL000005'");
            console.log("   - probarBusquedaCorta() - busca 'SOL'");
            
            // NO probar automáticamente al cargar - solo configurar eventos
            console.log("🧪 Eventos configurados - esperando input del usuario");
        }
    }, 1000);
    
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
    
    // Búsqueda de solicitudes - configurar después de un delay
    setTimeout(function() {
        console.log("⏰ Ejecutando configurarBusquedaSolicitudes después de delay");
        configurarBusquedaSolicitudes();
    }, 500);
    
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
CONFIGURAR BÚSQUEDA DE SOLICITUDES
=============================================*/
function configurarBusquedaSolicitudes() {
    console.log("🔍 Configurando event listener para numeroSolicitudBuscar...");
    console.log("🔍 Elemento existe:", $("#numeroSolicitudBuscar").length > 0);
    console.log("🔍 Elemento HTML:", $("#numeroSolicitudBuscar")[0]);
    
    if ($("#numeroSolicitudBuscar").length > 0) {
        // Remover TODOS los event listeners anteriores
        $("#numeroSolicitudBuscar").off();
        
        // Variable para controlar timeout
        var timeoutBusqueda;
        
        // Event listener para input y keyup
        $("#numeroSolicitudBuscar").on("input keyup", function(e) {
            console.log("⌨️ EVENTO DETECTADO:", e.type, "Tecla:", e.keyCode);
        var termino = $(this).val();
            console.log("⌨️ Término actual:", termino, "Longitud:", termino.length);
            
            // Limpiar timeout anterior
            clearTimeout(timeoutBusqueda);
            
        if(termino.length >= 3) {
                // Esperar 300ms antes de buscar
                timeoutBusqueda = setTimeout(function() {
                    console.log("🔍 Iniciando búsqueda con término:", termino);
            buscarSolicitudesStock(termino);
                }, 300);
        } else {
                console.log("❌ Término muy corto, ocultando resultados");
            ocultarResultadosSolicitudes();
        }
    });
    
        // Event listener específico para Enter
        $("#numeroSolicitudBuscar").on("keydown", function(e) {
            console.log("⌨️ Keydown detectado, tecla:", e.keyCode);
            if(e.keyCode === 13) { // Enter
                console.log("⏎ ENTER presionado - ejecutando búsqueda");
                e.preventDefault();
                clearTimeout(timeoutBusqueda); // Cancelar timeout
                var termino = $(this).val();
                if(termino.length >= 3) {
                    buscarSolicitudesStock(termino);
                } else {
                    console.log("❌ Término muy corto para buscar");
                    ocultarResultadosSolicitudes();
                }
            }
        });
        
        // Eventos de debugging
        $("#numeroSolicitudBuscar").on("focus", function() {
            console.log("🎯 Campo numeroSolicitudBuscar recibió focus");
        });
        
        $("#numeroSolicitudBuscar").on("blur", function() {
            console.log("👋 Campo numeroSolicitudBuscar perdió focus");
        });
        
        $("#numeroSolicitudBuscar").on("click", function() {
            console.log("🖱️ CLICK detectado en numeroSolicitudBuscar");
        });
        
        console.log("✅ Event listeners configurados para numeroSolicitudBuscar");
    } else {
        console.log("❌ Elemento numeroSolicitudBuscar no encontrado");
        console.log("🔍 Buscando elementos similares...");
        console.log("🔍 Inputs con 'solicitud':", $("input[id*='solicitud']").length);
        console.log("🔍 Inputs con 'numero':", $("input[id*='numero']").length);
        console.log("🔍 Todos los inputs:", $("input[type='text']").length);
    }
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
    console.log("🔍 buscarSolicitudesStock llamada con término:", termino);
    
    // Validar término
    if(!termino || termino.length < 3) {
        console.log("❌ Término inválido o muy corto");
        ocultarResultadosSolicitudes();
        return;
    }
    
    // Mostrar indicador de carga
    $("#numeroSolicitudBuscar").after(`
        <div id="cargandoSolicitudes" class="text-center" style="margin-top: 5px;">
            <i class="fa fa-spinner fa-spin"></i> Buscando solicitudes...
        </div>
    `);
    
    $.ajax({
        url: "ajax/productos-despacho.ajax.php",
        method: "POST",
        data: {
            buscarSolicitudes: true,
            termino: termino
        },
        dataType: "json",
        beforeSend: function() {
            console.log("📤 Enviando petición AJAX...");
            console.log("📤 URL:", "ajax/productos-despacho.ajax.php");
            console.log("📤 Data:", {buscarSolicitudes: true, termino: termino});
        },
        success: function(respuesta) {
            console.log("✅ Respuesta recibida:", respuesta);
            
            // Ocultar indicador de carga
            $("#cargandoSolicitudes").remove();
            
            if(respuesta.success && respuesta.solicitudes && respuesta.solicitudes.length > 0) {
                console.log("📋 Mostrando resultados:", respuesta.solicitudes.length, "solicitudes");
                console.log("📋 Datos de solicitudes:", respuesta.solicitudes);
                mostrarResultadosSolicitudes(respuesta.solicitudes);
            } else {
                console.log("❌ No hay resultados o error en respuesta");
                console.log("❌ Respuesta completa:", respuesta);
                console.log("❌ success:", respuesta.success);
                console.log("❌ solicitudes:", respuesta.solicitudes);
                console.log("❌ total:", respuesta.total);
                mostrarSinResultadosSolicitudes();
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX buscando solicitudes:", error);
            console.error("❌ Status:", status);
            console.error("❌ Response Text:", xhr.responseText);
            console.error("❌ Response Headers:", xhr.getAllResponseHeaders());
            
            // Ocultar indicador de carga
            $("#cargandoSolicitudes").remove();
            
            mostrarErrorBusquedaSolicitudes();
        }
    });
}

/*=============================================
MOSTRAR RESULTADOS DE SOLICITUDES - SIMPLIFICADO
=============================================*/
function mostrarResultadosSolicitudes(solicitudes) {
    console.log("📋 mostrarResultadosSolicitudes llamada con:", solicitudes.length, "solicitudes");
    
    // Eliminar resultados anteriores
    ocultarResultadosSolicitudes();
    
    var html = '<div style="border: 1px solid #ccc; background: white; max-height: 400px; overflow-y: auto; z-index: 1000; position: relative; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">';
    
    solicitudes.forEach(function(solicitud) {
        var fecha = new Date(solicitud.fecha_solicitud).toLocaleDateString();
        var estadoColor = solicitud.estado === 'aprobado' ? '#5cb85c' : '#f0ad4e';
        
        // Parsear productos de la solicitud
        var productos = [];
        try {
            productos = JSON.parse(solicitud.productos_solicitados || '[]');
        } catch(e) {
            console.warn("Error parseando productos de solicitud:", e);
            productos = [];
        }
        
        html += `
            <div onclick="seleccionarSolicitud(${solicitud.id})" 
                 style="padding: 12px; border-bottom: 1px solid #eee; cursor: pointer; background: #f9f9f9;"
                 onmouseover="this.style.background='#e9e9e9'" 
                 onmouseout="this.style.background='#f9f9f9'">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div>
                        <strong style="color: #333; font-size: 14px;">${solicitud.numero_solicitud}</strong>
                        <span style="background: ${estadoColor}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 5px;">
                            ${solicitud.estado.toUpperCase()}
                        </span>
                </div>
                    <small style="color: #666;">${fecha}</small>
                </div>
                
                <div style="margin-bottom: 8px; font-size: 12px; color: #666;">
                    <strong>Usuario:</strong> ${solicitud.nombre_usuario_solicitante} | 
                    <strong>Sucursal:</strong> ${solicitud.nombre_sucursal_solicitante}
                </div>
                
                <div style="margin-bottom: 8px; font-size: 12px; color: #666;">
                    <strong>Resumen:</strong> ${solicitud.total_productos} productos (${solicitud.total_cantidad} unidades)
                </div>
                
                <div style="background: white; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                    <strong style="font-size: 11px; color: #333; display: block; margin-bottom: 5px;">📦 Productos solicitados:</strong>
                    <div style="max-height: 80px; overflow-y: auto;">
        `;
        
        if(productos.length > 0) {
            productos.forEach(function(producto, index) {
                if(index < 5) { // Mostrar máximo 5 productos
                    html += `
                        <div style="font-size: 11px; padding: 2px 0; border-bottom: 1px solid #f0f0f0;">
                            <span style="color: #666;">${producto.codigo}</span> - 
                            <span style="color: #333;">${producto.descripcion}</span> 
                            <span style="color: #007bff; font-weight: bold;">(${producto.cantidad})</span>
                        </div>
                    `;
                }
            });
            
            if(productos.length > 5) {
                html += `<div style="font-size: 10px; color: #999; text-align: center; padding: 5px;">
                    ... y ${productos.length - 5} productos más
                </div>`;
            }
        } else {
            html += `<div style="font-size: 11px; color: #999; text-align: center; padding: 5px;">
                No se pudieron cargar los productos
            </div>`;
        }
        
        html += `
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 8px;">
                    <span style="background: #007bff; color: white; padding: 4px 8px; border-radius: 3px; font-size: 11px;">
                        👆 Click para seleccionar esta solicitud
                    </span>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    // Insertar después del campo de búsqueda
    $("#numeroSolicitudBuscar").after(`
        <div id="resultadosBusquedaSolicitudes" style="margin-top: 5px;">
            ${html}
        </div>
    `);
    
    console.log("✅ Resultados insertados en el DOM");
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
                
                // Parsear productos de la solicitud
                var productos = [];
                try {
                    productos = JSON.parse(solicitudSeleccionada.productos_solicitados || '[]');
                } catch(e) {
                    console.warn("Error parseando productos de solicitud seleccionada:", e);
                    productos = [];
                }
                
                var estadoColor = solicitudSeleccionada.estado === 'aprobado' ? '#5cb85c' : '#f0ad4e';
                var fecha = new Date(solicitudSeleccionada.fecha_solicitud).toLocaleDateString();
                
                var infoHtml = `
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid ${estadoColor};">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <h4 style="margin: 0; color: #333;">
                                <i class="fa fa-file-text"></i> ${solicitudSeleccionada.numero_solicitud}
                                <span style="background: ${estadoColor}; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; margin-left: 8px;">
                                    ${solicitudSeleccionada.estado.toUpperCase()}
                                </span>
                            </h4>
                            <small style="color: #666;">${fecha}</small>
                        </div>
                        
                        <div style="margin-bottom: 10px; font-size: 13px; color: #666;">
                            <strong><i class="fa fa-user"></i> Usuario:</strong> ${solicitudSeleccionada.nombre_usuario_solicitante}<br>
                            <strong><i class="fa fa-building"></i> Sucursal:</strong> ${solicitudSeleccionada.nombre_sucursal_solicitante}<br>
                            <strong><i class="fa fa-cubes"></i> Resumen:</strong> ${solicitudSeleccionada.total_productos} productos (${solicitudSeleccionada.total_cantidad} unidades)
                        </div>
                        
                        <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;">
                            <strong style="color: #333; font-size: 13px; display: block; margin-bottom: 8px;">
                                <i class="fa fa-list"></i> Productos solicitados:
                            </strong>
                            <div style="max-height: 150px; overflow-y: auto;">
                `;
                
                if(productos.length > 0) {
                    productos.forEach(function(producto, index) {
                        infoHtml += `
                            <div style="padding: 6px 0; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                                <div style="flex: 1;">
                                    <span style="color: #666; font-family: monospace; font-size: 12px;">${producto.codigo}</span>
                                    <span style="color: #333; font-size: 12px; margin-left: 8px;">${producto.descripcion}</span>
                                </div>
                                <div style="text-align: right;">
                                    <span style="background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold;">
                                        ${producto.cantidad} unidades
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    infoHtml += `
                        <div style="text-align: center; padding: 20px; color: #999; font-style: italic;">
                            <i class="fa fa-exclamation-triangle"></i> No se pudieron cargar los productos
                        </div>
                    `;
                }
                
                infoHtml += `
                            </div>
                        </div>
                        
                        <div style="margin-top: 10px; text-align: center;">
                            <span style="background: #28a745; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-right: 10px;">
                                <i class="fa fa-check"></i> Solicitud seleccionada - Lista para agregar al despacho
                            </span>
                            <button type="button" class="btn btn-sm btn-warning" onclick="limpiarSolicitudSeleccionada()" style="padding: 4px 8px; font-size: 11px;">
                                <i class="fa fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
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
    
    // Prevenir ejecución duplicada con bandera más robusta
    if(window.cargandoProductosSolicitud === true) {
        console.log("⚠️ Ya se están cargando productos de solicitud, evitando duplicación");
        return;
    }
    
    console.log("🚀 Iniciando carga de productos de solicitud...");
    window.cargandoProductosSolicitud = true;
    
    if(!solicitudSeleccionada) {
        window.cargandoProductosSolicitud = false;
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
        
        console.log("🔍 Inventario local disponible:", inventarioLocal.length, "productos");
        console.log("🔍 Productos de solicitud:", productos.length, "productos");
        
        productos.forEach(function(producto) {
            console.log("🔍 Buscando producto:", producto.codigo, "en inventario local");
            
            // Verificar stock disponible
            var productoInventario = inventarioLocal.find(function(p) {
                return p.codigo === producto.codigo;
            });
            
            console.log("🔍 Producto encontrado en inventario:", productoInventario ? "SÍ" : "NO");
            if(productoInventario) {
                console.log("🔍 Stock disponible:", productoInventario.stock);
            }
            
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
    
    console.log("🔍 Nota general creada:", notaGeneral);
    console.log("🔍 Productos sin stock:", productosSinStock);
    console.log("🔍 Productos con faltantes:", productosConFaltantes);
    
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
    
    // Resetear bandera de carga
    window.cargandoProductosSolicitud = false;
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
    
    console.log("🔍 DEBUG: Iniciando confirmarAgregarProducto");
    
    var codigo = $("#codigoProductoDespachoModal").val();
    var descripcion = $("#descripcionProductoDespachoModal").val();
    var cantidadNueva = parseInt($("#cantidadProductoDespachoModal").val());
    var stock = parseInt($("#stockActualProductoDespachoModal").val());
    var observacionNueva = $("#observacionProductoDespachoModal").val();
    
    console.log("🔍 DEBUG: Datos del producto:", {
        codigo: codigo,
        descripcion: descripcion,
        cantidadNueva: cantidadNueva,
        stock: stock,
        observacionNueva: observacionNueva
    });
    
    // Validar que no exista ya el producto
    var existente = productosDespacho.find(function(p) {
        return p.codigo === codigo;
    });
    
    console.log("🔍 DEBUG: Producto existente:", existente);
    console.log("🔍 DEBUG: Array productosDespacho actual:", productosDespacho);
    
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
    console.log("🔍 DEBUG: Agregando producto nuevo al despacho");
    
    var observacionFinal = observacionNueva.length > 0 ? 
                          `${cantidadNueva} (${observacionNueva})` : 
                          "";
    
    var nuevoProducto = {
        codigo: codigo,
        descripcion: descripcion,
        cantidad: cantidadNueva,
        stock_disponible: stock,
        observacion: observacionFinal
    };
    
    console.log("🔍 DEBUG: Nuevo producto a agregar:", nuevoProducto);
    
    productosDespacho.push(nuevoProducto);
    
    console.log("🔍 DEBUG: Array productosDespacho después de agregar:", productosDespacho);
    
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
    
    console.log("🔍 DEBUG: actualizarVistaProductosDespacho - productosDespacho.length:", productosDespacho.length);
    console.log("🔍 DEBUG: productosDespacho:", productosDespacho);
    
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
ENVIAR FORMULARIO DE DESPACHO - VERSIÓN CORREGIDA
=============================================*/
function enviarFormularioDespacho() {
    
    console.log("🚀 Enviando formulario de despacho...");
    
    // Verificar si hay productos
    if(productosDespacho.length === 0) {
        swal({
            title: "Sin productos",
            text: "Debe agregar al menos un producto al despacho",
            type: "warning",
            confirmButtonText: "Entendido"
        });
        return false;
    }
    
    // Actualizar campos ocultos antes de enviar
    $("#productosDespachoHidden").val(JSON.stringify(productosDespacho));
    $("#totalProductosHidden").val(productosDespacho.length);
    
    var totalCantidad = productosDespacho.reduce(function(sum, producto) {
        return sum + parseInt(producto.cantidad);
    }, 0);
    $("#totalCantidadHidden").val(totalCantidad);
    
    // DETECCIÓN MEJORADA DEL MODO EDICIÓN
    var esEdicion = false;
    var idDespachoEditar = null;
    
    // Método 1: Buscar campo oculto
    if($("#formCrearDespacho input[name='editarDespacho']").length > 0) {
        esEdicion = true;
        idDespachoEditar = $("#formCrearDespacho input[name='idDespachoEditar']").val();
        console.log("✅ Modo edición detectado por campo oculto - ID:", idDespachoEditar);
    }
    
    // Método 2: Variable global
    if(typeof window.modoEdicionActivo !== 'undefined' && window.modoEdicionActivo === true) {
        esEdicion = true;
        idDespachoEditar = window.idDespachoEditando || null;
        console.log("✅ Modo edición detectado por variable global - ID:", idDespachoEditar);
    }
    
    // Método 3: URL con parámetro editar
    var urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('editar')) {
        esEdicion = true;
        idDespachoEditar = urlParams.get('editar');
        console.log("✅ Modo edición detectado por URL - ID:", idDespachoEditar);
        
        // Asegurar que los campos ocultos existan
        if($("#formCrearDespacho input[name='editarDespacho']").length === 0) {
            $("#formCrearDespacho").append('<input type="hidden" name="editarDespacho" value="1">');
        }
        if($("#formCrearDespacho input[name='idDespachoEditar']").length === 0) {
            $("#formCrearDespacho").append('<input type="hidden" name="idDespachoEditar" value="' + idDespachoEditar + '">');
        }
    }
    
    if(esEdicion) {
        console.log("📝 MODO EDICIÓN CONFIRMADO");
        console.log("- ID del despacho:", idDespachoEditar);
        console.log("- Productos a actualizar:", productosDespacho.length);
        console.log("- Total unidades:", totalCantidad);
        
        // Confirmar edición
        swal({
            title: "¿Guardar cambios en el despacho?",
            html: `
                <p>Se actualizará el despacho con:</p>
                <ul style="text-align: left; display: inline-block;">
                    <li><strong>${productosDespacho.length}</strong> productos diferentes</li>
                    <li><strong>${totalCantidad}</strong> unidades totales</li>
                    <li>Despacho ID: <strong>${idDespachoEditar}</strong></li>
                </ul>
            `,
            type: "question",
            showCancelButton: true,
            confirmButtonColor: "#3c8dbc",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, guardar cambios",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                console.log("🔄 Enviando formulario de edición...");
                $("#formCrearDespacho")[0].submit();
            }
        });
        
    } else {
        console.log("📝 MODO CREACIÓN");
        
        var tipoDespacho = solicitudSeleccionada ? "Despacho desde solicitud" : "Despacho libre";
        
        // Confirmar creación
        swal({
            title: "¿Crear despacho?",
            html: `
                <p>Se creará un despacho con:</p>
                <ul style="text-align: left; display: inline-block;">
                    <li><strong>${productosDespacho.length}</strong> productos diferentes</li>
                    <li><strong>${totalCantidad}</strong> unidades totales</li>
                    <li><strong>Tipo:</strong> ${tipoDespacho}</li>
                </ul>
            `,
            type: "question",
            showCancelButton: true,
            confirmButtonColor: "#3c8dbc",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, crear despacho",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if(result.value) {
                console.log("🔄 Enviando formulario de creación...");
                $("#formCrearDespacho")[0].submit();
            }
        });
    }
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

/*=============================================
CARGAR PRODUCTOS DESDE SOLICITUD
=============================================*/
function cargarProductosDesdeSolicitud() {
    
    if(!window.solicitudOrigen || !window.productosDesdeSolicitud) {
        console.log("❌ No hay datos de solicitud para cargar");
        return;
    }
    
    console.log("🚛 Cargando productos desde solicitud:", window.solicitudOrigen.numero_solicitud);
    console.log("📦 Productos a cargar:", window.productosDesdeSolicitud);
    
    // Llenar el campo de búsqueda con el número de solicitud
    $("#numeroSolicitudBuscar").val(window.solicitudOrigen.numero_solicitud);
    
    // Simular la selección de la solicitud
    solicitudSeleccionada = window.solicitudOrigen;
    
    // Mostrar información de la solicitud
    var estadoColor = solicitudSeleccionada.estado === 'aprobado' ? '#5cb85c' : '#f0ad4e';
    var fecha = new Date(solicitudSeleccionada.fecha_solicitud).toLocaleDateString();
    
    var infoHtml = `
        <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid ${estadoColor};">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="margin: 0; color: #333;">
                    <i class="fa fa-file-text"></i> ${solicitudSeleccionada.numero_solicitud}
                    <span style="background: ${estadoColor}; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; margin-left: 8px;">
                        ${solicitudSeleccionada.estado.toUpperCase()}
                    </span>
                </h4>
                <small style="color: #666;">${fecha}</small>
            </div>
            
            <div style="margin-bottom: 10px; font-size: 13px; color: #666;">
                <strong><i class="fa fa-user"></i> Usuario:</strong> ${solicitudSeleccionada.nombre_usuario_solicitante}<br>
                <strong><i class="fa fa-building"></i> Sucursal:</strong> ${solicitudSeleccionada.nombre_sucursal_solicitante}<br>
                <strong><i class="fa fa-cubes"></i> Resumen:</strong> ${solicitudSeleccionada.total_productos} productos (${solicitudSeleccionada.total_cantidad} unidades)
            </div>
            
            <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;">
                <strong style="color: #333; font-size: 13px; display: block; margin-bottom: 8px;">
                    <i class="fa fa-list"></i> Productos solicitados:
                </strong>
                <div style="max-height: 150px; overflow-y: auto;">
    `;
    
    if(window.productosDesdeSolicitud.length > 0) {
        window.productosDesdeSolicitud.forEach(function(producto, index) {
            infoHtml += `
                <div style="padding: 6px 0; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <span style="color: #666; font-family: monospace; font-size: 12px;">${producto.codigo}</span>
                        <span style="color: #333; font-size: 12px; margin-left: 8px;">${producto.descripcion}</span>
                    </div>
                    <div style="text-align: right;">
                        <span style="background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold;">
                            ${producto.cantidad} unidades
                        </span>
                    </div>
                </div>
            `;
        });
    }
    
    infoHtml += `
                </div>
            </div>
            
            <div style="margin-top: 10px; text-align: center;">
                <span style="background: #28a745; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; margin-right: 10px;">
                    <i class="fa fa-check"></i> Solicitud cargada automáticamente - Lista para agregar al despacho
                </span>
                <button type="button" class="btn btn-sm btn-warning" onclick="limpiarSolicitudSeleccionada()" style="padding: 4px 8px; font-size: 11px;">
                    <i class="fa fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    `;
    
    $("#datosSolicitudEncontrada").html(infoHtml);
    $("#infoSolicitudEncontrada").show();
    $("#idSolicitudOrigenHidden").val(solicitudSeleccionada.id);
    
    // Cargar productos automáticamente después de que el inventario esté cargado
    if(inventarioLocal.length > 0) {
        console.log("✅ Inventario ya cargado, procediendo con solicitud");
        console.log("🔍 Bandera actual:", window.cargandoProductosSolicitud);
        // Usar timeout para evitar ejecución inmediata duplicada
        setTimeout(function() {
            console.log("🔍 Ejecutando cargarProductosDeSolicitud después de timeout");
            cargarProductosDeSolicitud();
        }, 100);
    } else {
        console.log("⏳ Inventario no cargado, cargando primero...");
        cargarProductosInventario();
        
        // Esperar a que se cargue el inventario y luego cargar la solicitud
        setTimeout(function() {
            if(inventarioLocal.length > 0) {
                console.log("✅ Inventario cargado, procediendo con solicitud");
                console.log("🔍 Bandera actual:", window.cargandoProductosSolicitud);
                cargarProductosDeSolicitud();
            } else {
                console.log("❌ No se pudo cargar el inventario local");
            }
        }, 2000);
    }
    
    console.log("✅ Solicitud cargada automáticamente desde:", window.solicitudOrigen.numero_solicitud);
}

/*=============================================
LIMPIAR SOLICITUD SELECCIONADA
=============================================*/
function limpiarSolicitudSeleccionada() {
    
    console.log("🧹 Limpiando solicitud seleccionada...");
    
    // Limpiar variable global
    solicitudSeleccionada = null;
    
    // Limpiar campo de búsqueda
    $("#numeroSolicitudBuscar").val("");
    
    // Ocultar sección de información de solicitud
    $("#infoSolicitudEncontrada").hide();
    
    // Limpiar contenido de la sección
    $("#datosSolicitudEncontrada").html("");
    
    // Limpiar campo oculto
    $("#idSolicitudOrigenHidden").val("");
    
    // Ocultar resultados de búsqueda si están visibles
    ocultarResultadosSolicitudes();
    
    console.log("✅ Solicitud seleccionada limpiada correctamente");
}

/*=============================================
BUSCAR PRODUCTOS EN TODAS LAS SUCURSALES
=============================================*/
function buscarProductosEnTodasLasSucursales() {
    
    if(!solicitudSeleccionada) {
        console.log("❌ No hay solicitud seleccionada");
        return;
    }
    
    console.log("🔍 Buscando productos en todas las sucursales para solicitud:", solicitudSeleccionada.numero_solicitud);
    
    // Mostrar loading
    swal({
        title: "Buscando productos...",
        text: "Consultando inventario en todas las sucursales",
        type: "info",
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    $.ajax({
        url: "ajax/buscar-productos-solicitud-sucursales.ajax.php",
        method: "POST",
        data: {
            buscarProductosSolicitudSucursales: true,
            idSolicitud: solicitudSeleccionada.id
        },
        dataType: "json",
        success: function(respuesta) {
            swal.close();
            
            if(respuesta.success) {
                mostrarResumenDisponibilidadSucursales(respuesta);
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudieron consultar las sucursales",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function() {
            swal.close();
            swal({
                title: "Error de conexión",
                text: "No se pudo conectar con las sucursales",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
MOSTRAR RESUMEN DE DISPONIBILIDAD EN SUCURSALES
=============================================*/
function mostrarResumenDisponibilidadSucursales(datos) {
    
    var resumen = datos.resumen;
    var productos = datos.productos;
    var solicitud = datos.solicitud;
    
    // Crear HTML del resumen
    var htmlResumen = `
        <div style="text-align: left; max-height: 400px; overflow-y: auto;">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 10px 0; color: #333;">
                    <i class="fa fa-file-text"></i> ${solicitud.numero_solicitud}
                </h4>
                <p style="margin: 0; color: #666;">
                    <strong>Usuario:</strong> ${solicitud.nombre_usuario_solicitante} | 
                    <strong>Sucursal:</strong> ${solicitud.nombre_sucursal_solicitante}
                </p>
            </div>
            
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <div style="flex: 1; background: #d4edda; padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #155724;">${resumen.disponibles}</div>
                    <div style="font-size: 12px; color: #155724;">Completos</div>
                </div>
                <div style="flex: 1; background: #fff3cd; padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #856404;">${resumen.parciales}</div>
                    <div style="font-size: 12px; color: #856404;">Parciales</div>
                </div>
                <div style="flex: 1; background: #f8d7da; padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #721c24;">${resumen.faltantes}</div>
                    <div style="font-size: 12px; color: #721c24;">Faltantes</div>
                </div>
            </div>
            
            <div style="max-height: 300px; overflow-y: auto;">
    `;
    
    // Agregar cada producto
    productos.forEach(function(producto) {
        var estadoColor = producto.estado === 'completo' ? '#d4edda' : 
                         producto.estado === 'parcial' ? '#fff3cd' : '#f8d7da';
        var estadoIcon = producto.estado === 'completo' ? 'fa-check-circle' : 
                        producto.estado === 'parcial' ? 'fa-exclamation-triangle' : 'fa-times-circle';
        var estadoText = producto.estado === 'completo' ? 'Completo' : 
                        producto.estado === 'parcial' ? 'Parcial' : 'Faltante';
        
        htmlResumen += `
            <div style="background: ${estadoColor}; padding: 10px; margin-bottom: 8px; border-radius: 5px; border-left: 4px solid ${estadoColor.replace('d', '6')}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <div>
                        <strong>${producto.codigo}</strong> - ${producto.descripcion}
                        <span style="background: ${estadoColor.replace('d', '6')}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 8px;">
                            <i class="fa ${estadoIcon}"></i> ${estadoText}
                        </span>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: #666;">Solicitado: ${producto.cantidad_solicitada}</div>
                        <div style="font-size: 12px; color: #666;">Disponible: ${producto.disponible_total}</div>
                    </div>
                </div>
        `;
        
        if(producto.sucursales.length > 0) {
            htmlResumen += `<div style="font-size: 11px; color: #666; margin-top: 5px;">`;
            producto.sucursales.forEach(function(sucursal, index) {
                if(index > 0) htmlResumen += " | ";
                htmlResumen += `${sucursal.nombre}: ${sucursal.stock}`;
            });
            htmlResumen += `</div>`;
        }
        
        htmlResumen += `</div>`;
    });
    
    htmlResumen += `
            </div>
        </div>
    `;
    
    // Mostrar modal con opciones
    swal({
        title: "Disponibilidad en Sucursales",
        html: htmlResumen,
        width: "800px",
        showCancelButton: true,
        confirmButtonText: "Crear Despachos Inteligentes",
        cancelButtonText: "Cerrar",
        confirmButtonColor: "#5cb85c",
        cancelButtonColor: "#d33"
    }).then(function(result) {
        if(result.value) {
            // Aquí implementaríamos la creación de despachos inteligentes
            swal({
                title: "Próximamente",
                text: "La creación de despachos inteligentes estará disponible en la próxima versión",
                type: "info",
                confirmButtonText: "Entendido"
            });
        }
        // Limpiar solicitud después de mostrar el resumen
        limpiarSolicitudSeleccionada();
    });
}