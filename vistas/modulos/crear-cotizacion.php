<?php
if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
        window.location = "inicio";
    </script>';
    return;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Crear cotización</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Crear cotización</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-7 col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border"></div>
                    <form role="form" method="post" class="formularioVenta" enctype="multipart/form-data">
                        <input type="hidden" name="nuevaCotizacion">
                        <div class="box-body">
                            <div class="box">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                        <input type="text" class="form-control" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>
                                        <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["id"]; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                        <?php
                                        $item = null;
                                        $valor = null;
                                        $ventas = ControladorVentas::ctrMostrarVentas($item, $valor);
                                        if (!$ventas) {
                                            echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="10001" readonly>';
                                        } else {
                                            foreach ($ventas as $key => $value) {
                                            }
                                            $codigo = $value["codigo"] + 1;
                                            echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="' . $codigo . '" readonly>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group" style="position: relative;">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                        <input type="text" class="form-control" id="buscarCliente" name="buscarCliente" placeholder="Buscar cliente por nombre o documento..." autocomplete="off" required>
                                        <input type="hidden" id="idClienteSeleccionado" name="seleccionarCliente">
                                        <span class="input-group-addon"><button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">Agregar cliente</button></span>
                                    </div>
                                    <div id="sugerenciasClientes" class="sugerencias-clientes" style="display: none;"></div>
                                </div>
                                <div class="form-group row nuevoProducto"></div>
                                <input type="hidden" id="listaProductos" name="listaProductos">
                                <button type="button" class="btn btn-default hidden-lg btnAgregarProducto">Agregar producto Lista</button>
                                <button type="button" class="btn btn-default  btnAgregarProducto1">Agregar producto</button>
                                <hr>
                                <div class="row">
                                    <div class="col-xs-12 pull-right">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Impuesto</th>
                                                    <th>Descuento</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 33%">
                                                        <div class="input-group">
                                                            <input type="number" class="form-control input-lg" min="0" id="nuevoImpuestoVenta" name="nuevoImpuestoVenta" value="0" required>
                                                            <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto" required>
                                                            <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto" required>
                                                            <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                                        </div>
                                                    </td>
                                                    <td style="width: 33%">
                                                        <div class="input-group">
                                                            <input type="number" class="form-control input-lg" min="0" id="nuevoDescuentoVenta" name="nuevoDescuentoVenta" value="0" required>
                                                            <input type="hidden" name="nuevoPrecioDescuento" id="nuevoPrecioDescuento" required>
                                                            <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                                        </div>
                                                    </td>
                                                    <td style="width: 33%">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                                            <input type="text" class="form-control input-lg" id="nuevoTotalVenta" name="nuevoTotalVenta" total="" placeholder="00000" readonly required>
                                                            <input type="hidden" name="totalVenta" id="totalVenta">
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="basic-url">Nota</label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="basic-addon3">Detalle o Descripción</span>
                                        <textarea class="form-control" aria-label="With textarea" id="detalle" name="detalle"></textarea>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="basic-url">Imagenes de referencia (opcional)</label>
                                    <input type="file" name="images[]" id="images" multiple>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right">Guardar cotización</button>
                        </div>
                    </form>
                    <?php
                    ControladorCotizaciones::crear();
                    ?>
                </div>
            </div>
            <div class="col-lg-5 hidden-md hidden-sm hidden-xs">
                <div class="box box-warning">
                    <div class="box-header with-border"></div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped dt-responsive tablaVentas">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Imagen</th>
                                    <th>Código</th>
                                    <th>Descripcion</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->
<div id="modalAgregarCliente" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <input type="hidden" name="origen" value="crear-cotizacion">
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Agregar cliente</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control input-lg" name="nuevoCliente" placeholder="Ingresar nombre" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                <input type="text" class="form-control input-lg" name="nuevoDocumentoId" placeholder="NIT o Documento (máximo 11 dígitos)" maxlength="11" pattern="[0-9]{1,11}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono (7-10 dígitos)" pattern="[0-9]{7,10}" minlength="7" maxlength="10" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección (acepta caracteres especiales)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="submit" class="btn btn-primary">Guardar cliente</button>
                </div>
            </form>
            <?php
            $crearCliente = new ControladorClientes();
            $crearCliente->ctrCrearCliente();
            ?>
        </div>
    </div>
</div>

<style>
/* Estilos para el autocompletado de clientes */
.sugerencias-clientes {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 0 0 4px 4px;
}

.sugerencia-cliente {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.sugerencia-cliente:hover {
    background-color: #f5f5f5;
}

.sugerencia-cliente:last-child {
    border-bottom: none;
}

.sugerencia-cliente .nombre {
    font-weight: bold;
    color: #333;
}

.sugerencia-cliente .documento {
    color: #666;
    font-size: 12px;
}

.sugerencia-cliente .email {
    color: #999;
    font-size: 11px;
}
</style>

<script>
// Autocompletado de clientes
$(document).ready(function() {
    let timeoutId;
    
    $('#buscarCliente').on('input', function() {
        const query = $(this).val().trim();
        
        // Limpiar timeout anterior
        clearTimeout(timeoutId);
        
        if (query.length >= 2) {
            // Esperar 300ms antes de hacer la búsqueda
            timeoutId = setTimeout(function() {
                buscarClientes(query);
            }, 300);
        } else {
            $('#sugerenciasClientes').hide();
        }
    });
    
    // Ocultar sugerencias al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#buscarCliente, #sugerenciasClientes').length) {
            $('#sugerenciasClientes').hide();
        }
    });
    
    // Manejar tecla Enter en el campo de búsqueda
    $('#buscarCliente').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const primeraSugerencia = $('#sugerenciasClientes .sugerencia-cliente').first();
            if (primeraSugerencia.length > 0) {
                primeraSugerencia.click();
            }
        }
    });
    
    function buscarClientes(query) {
        console.log('Buscando clientes con query:', query);
        $.ajax({
            url: 'ajax/buscar-clientes.ajax.php',
            type: 'POST',
            data: { buscarCliente: query },
            success: function(response) {
                console.log('Respuesta AJAX:', response);
                try {
                    const clientes = JSON.parse(response);
                    console.log('Clientes parseados:', clientes);
                    mostrarSugerencias(clientes);
                } catch (e) {
                    console.error('Error parsing response:', e);
                    console.error('Response was:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
            }
        });
    }
    
    function mostrarSugerencias(clientes) {
        const container = $('#sugerenciasClientes');
        container.empty();
        
        if (clientes.length === 0) {
            container.html('<div class="sugerencia-cliente">No se encontraron clientes</div>');
        } else {
            clientes.forEach(function(cliente) {
                const sugerencia = $(`
                    <div class="sugerencia-cliente" data-id="${cliente.id}" data-nombre="${cliente.nombre}">
                        <div class="nombre">${cliente.nombre}</div>
                        <div class="documento">Documento: ${cliente.documento}</div>
                        ${cliente.email ? `<div class="email">${cliente.email}</div>` : ''}
                    </div>
                `);
                container.append(sugerencia);
            });
        }
        
        container.show();
        
        // Debug: verificar que los elementos se crearon correctamente
        console.log('Sugerencias creadas:', container.find('.sugerencia-cliente').length);
    }
    
    // Seleccionar cliente - versión simplificada
    $(document).on('click', '.sugerencia-cliente', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Obtener datos directamente del elemento
        const elemento = $(this);
        const id = elemento.attr('data-id');
        const nombre = elemento.attr('data-nombre');
        
        console.log('Cliente seleccionado:', { id: id, nombre: nombre });
        
        // Establecer valores
        $('#buscarCliente').val(nombre);
        $('#idClienteSeleccionado').val(id);
        
        // Ocultar sugerencias
        $('#sugerenciasClientes').hide();
        
        // Verificar que los valores se establecieron
        setTimeout(function() {
            console.log('Valores verificados:', {
                buscarCliente: $('#buscarCliente').val(),
                idClienteSeleccionado: $('#idClienteSeleccionado').val()
            });
        }, 100);
    });
    
    // Función para actualizar lista de clientes (llamada desde el controlador)
    window.actualizarListaClientes = function(cliente) {
        $('#buscarCliente').val(cliente.nombre);
        $('#idClienteSeleccionado').val(cliente.id);
        $('#sugerenciasClientes').hide();
    };
});
</script>