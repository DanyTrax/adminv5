<?php

if ($_SESSION["perfil"] == "Especial") {

  echo '<script>

    window.location = "inicio";

  </script>';

  return;
}

$xml = ControladorVentas::ctrDescargarXML();

if ($xml) {

  rename($_GET["xml"] . ".xml", "xml/" . $_GET["xml"] . ".xml");

  echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/' . $_GET["xml"] . '.xml" href="ventas">Se ha creado correctamente el archivo XML <span class="fa fa-times pull-right"></span></a>';
}

?>
<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Administrar ventas

    </h1>

    <ol class="breadcrumb">

      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>

      <li class="active">Administrar ventas</li>

    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <a href="crear-venta">

          <button class="btn btn-primary">

            Agregar venta

          </button>

        </a>

<?php
$formaPago = isset($_GET['formaPago']) ? $_GET['formaPago'] : null;
?>
<select class="btn pull-right" name="filter-formaPago" id="filter-formaPago" style="margin-left: 10px;">
    <option value="" <?= $formaPago === null ? 'selected' : '' ?>>Forma de Pago</option>
    <option value="">Todos</option>
    <?php foreach (FormaPago::ALL as $value) : ?>
        <option value="<?= $value ?>" <?= $formaPago === $value ? 'selected' : '' ?>><?= $value ?></option>
    <?php endforeach; ?>
</select>

<?php 
    // Asegúrate de que la ruta sea correcta desde el archivo donde lo uses
    // (ej: ventas.php, gastos.php, etc.)
    include "componentes/filtro-medio-pago.php"; 
?>
<button type="button" class="btn btn-default pull-right" id="daterange-btn">
    <span>
        <i class="fa fa-calendar"></i>
        <?php
        if (isset($_GET["fechaInicial"])) {
            echo $_GET["fechaInicial"] . " - " . $_GET["fechaFinal"];
        } else {
            echo 'Rango de fecha';
        }
        ?>
    </span>
    <i class="fa fa-caret-down"></i>
</button>
      
      <div class="box-body">

        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">

          <thead>

            <tr>

              <th style="width:3px">#</th>
              <th style="width:20px">Cod.factura</th>
              <th>Cliente</th>
              <th>Vendedor</th>
              <th style="width:80px">Forma de pago</th>
              <th>Total</th>
              <th>Fecha Venta</th>
              <th>Abono</th>
              <th>Medio Pago</th>
              <th>Acciones</th>

            </tr>

          </thead>

          <tbody>

            <?php
            
            $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
            $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;
            $medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : null;
            $formaPago = isset($_GET["formaPago"]) ? $_GET["formaPago"] : null;
            
            // CORRECCIÓN: Si el valor es 0, mostramos todo. Si no, usamos el valor o 150 por defecto.
            $valor_limite = isset($_GET["minimo"]) ? (int)$_GET["minimo"] : 150;

            $respuesta = ControladorVentas::filterBy($fechaInicial, $fechaFinal, $medioPago, $formaPago);
            
            $contador = 0;
            
 foreach ($respuesta as $key => $value) {
              if ($valor_limite > 0 && $contador >= $valor_limite) {
                  break; 
              }
              
              /*=============================================
              SEPARAR LÓGICA DE LA PRESENTACIÓN
              =============================================*/
              
              // 1. OBTENER DATOS RELACIONADOS
              $respuestaCliente = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);
              $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios("id", $value["id_vendedor"]);
              $respuestaUsuario_ab = ControladorUsuarios::ctrMostrarUsuarios("id", $value["id_vend_abono"]);

              // 2. CONSTRUIR LOS BOTONES DE ACCIONES
              $botones = '<div class="btn-group">';
              
              // Botón Ver Detalle (siempre)
              $botones .= '<button class="btn btn-success btn-xs btnVerDetalle" idVenta="' . $value["id"] . '" codigoVenta="' . $value["codigo"] . '" title="Ver productos"><i class="fa fa-eye"></i></button>';
              
              // Botón Imprimir (siempre)
              $botones .= '<button class="btn btn-info btn-xs btnImprimirFactura" codigoVenta="' . $value["codigo"] . '"><i class="fa fa-print"></i></button>';
              
              // Botones de Administrador
              if ($_SESSION["perfil"] == "Administrador") {
                  $botones .= '<button class="btn btn-warning btn-xs btnEditarVenta" idVenta="' . $value["id"] . '"><i class="fa fa-pencil"></i></button>';
                  $botones .= '<button class="btn btn-danger btn-xs btnEliminarVenta" idVenta="' . $value["id"] . '"><i class="fa fa-times"></i></button>';
              }
              
              // Botón de Abonar
              $perfilesPermitidos = ["Administrador", "Vendedor", "Contador"];
              if ($value["metodo_pago"] != "Completo" && in_array($_SESSION["perfil"], $perfilesPermitidos)) {
                  $botones .= '<button class="btn btn-primary btn-xs btnAbonar" idVenta="' . $value["id"] . '" idUsuarioAbo="' . $_SESSION["id"] . '" data-toggle="modal" data-target="#modalAbonar" title="Abonar"><i class="fa fa-money"></i></button>';
              }
              
              $botones .= '</div>';

              // 3. IMPRIMIR LA FILA COMPLETA
              echo '<tr>
                      <td>' . ($key + 1) . '</td>
                      <td>' . $value["codigo"] . '</td>
                      <td>' . $respuestaCliente["nombre"] . '</td>
                      <td>' . ($respuestaUsuario["nombre"] ?? '') . '</td>
                      <td>' . $value["metodo_pago"] . '</td>
                      <td>$ ' . number_format($value["total"] ?? 0, 2, ',', '.') . '</td>
                      <td>' . $value["fecha_abono"] . '</td>
                      <td>$ ' . number_format($value["abono"] ?? 0, 2, ',', '.') . '</td>
                      <td>' . $value["medio_pago"] . '</td>
                      <td>' . $botones . '</td>
                    </tr>';
                    
              $contador++;
            }
            ?>
          </tbody>
        </table>
        <?php
        $eliminarVenta = new ControladorVentas();
        $eliminarVenta->ctrEliminarVenta();
        ?>
      </div>
    </div>
      
    <div style="margin-bottom: 20px;">
        <form method="GET" style="display: inline-block;">
            <input type="hidden" name="ruta" value="ventas">
            <label for="bt_minimo">Mostrar Registros:</label>
            <input type="number" id="bt_minimo" name="minimo" value="<?php echo $valor_limite; ?>" min="0" required style="width: 100px; margin-left: 10px;">
            <button type="submit" class="btn btn-primary">Aplicar</button>
        </form>
        <a href="index.php?ruta=ventas&minimo=0" class="btn btn-default" style="display: inline-block; vertical-align: top;">Ver Todos</a>
    </div>
      
  </section>
</div>

<div id="modalAbonar" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Agregar Abono</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        
                        <div class="form-group">
                            <label>Dinero Restante:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                <input type="text" class="form-control dinRestante" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Valor del Abono:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                <input type="text" class="form-control nuevoAbono" name="nuevoAbono" placeholder="Ingresar valor del abono" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Medio de Pago del Abono:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                
                                <select class="form-control" name="nuevoMedioPagoAbono" required>
                                    <option value="">Seleccione Medio de Pago</option>
                                    <?php foreach (MedioPago::ALL as $value) : ?>
                                        <option value="<?= $value ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <input type="hidden" class="idVentaAbo" name="idVentaAbo">
                        <input type="hidden" class="idUsuarioAbo" name="idUsuarioAbo">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="submit" class="btn btn-primary">Guardar Abono</button>
                </div>
                <?php
                    // Esta parte llama al controlador para que procese el formulario
                    $crearAbono = new ControladorVentas();
                    $crearAbono -> ctrCrearAbono();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de productos de la venta -->
<div id="modalDetalleVenta" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#28a745; color:white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-eye"></i> Detalle de Factura: <span id="codigoFacturaDetalle"></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><strong>Información de la Venta</strong></h5>
                        <table class="table table-condensed">
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td id="clienteDetalle"></td>
                            </tr>
                            <tr>
                                <td><strong>Vendedor:</strong></td>
                                <td id="vendedorDetalle"></td>
                            </tr>
                            <tr>
                                <td><strong>Fecha:</strong></td>
                                <td id="fechaDetalle"></td>
                            </tr>
                            <tr>
                                <td><strong>Forma de Pago:</strong></td>
                                <td id="formaPagoDetalle"></td>
                            </tr>
                            <tr>
                                <td><strong>Medio de Pago:</strong></td>
                                <td id="medioPagoDetalle"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5><strong>Resumen Financiero</strong></h5>
                        <table class="table table-condensed">
                            <tr>
                                <td><strong>Subtotal:</strong></td>
                                <td id="subtotalDetalle"></td>
                            </tr>
                            <tr>
                                <td><strong>Impuestos:</strong></td>
                                <td id="impuestosDetalle"></td>
                            </tr>
                            <tr>
                                <td><strong>Descuento:</strong></td>
                                <td id="descuentoDetalle"></td>
                            </tr>
                            <tr class="success">
                                <td><strong>Total:</strong></td>
                                <td id="totalDetalle"></td>
                            </tr>
                            <tr>
                                <td><strong>Abono:</strong></td>
                                <td id="abonoDetalle"></td>
                            </tr>
                            <tr class="warning">
                                <td><strong>Saldo Pendiente:</strong></td>
                                <td id="saldoDetalle"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <h5><strong>Productos de la Venta</strong></h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaProductosDetalle">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 60%">Descripción del Producto</th>
                                <th style="width: 15%">Cantidad</th>
                                <th style="width: 20%">Total</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoProductosDetalle">
                            <!-- Los productos se cargarán aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>
                
                <div id="sinProductos" class="alert alert-info" style="display: none;">
                    <i class="fa fa-info-circle"></i> No se encontraron productos para esta venta.
                </div>
                
                <hr>
                
                <h5><strong>Historial de Transacciones/Abonos</strong></h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tablaHistorialAbonos">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 20%">Fecha</th>
                                <th style="width: 15%">Monto</th>
                                <th style="width: 25%">Vendedor</th>
                                <th style="width: 20%">Medio de Pago</th>
                                <th style="width: 15%">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoHistorialAbonos">
                            <!-- El historial se cargará aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>
                
                <div id="sinHistorial" class="alert alert-info" style="display: none;">
                    <i class="fa fa-info-circle"></i> No se encontraron transacciones registradas para esta venta.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-info" id="btnImprimirDetalle">
                    <i class="fa fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para el modal de detalle de venta */
#modalDetalleVenta .modal-dialog {
    width: 90%;
    max-width: 1000px;
}

#modalDetalleVenta .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

#tablaProductosDetalle {
    margin-bottom: 0;
}

#tablaProductosDetalle th {
    background-color: #f5f5f5;
    font-weight: bold;
    border-bottom: 2px solid #ddd;
}

#tablaProductosDetalle td {
    vertical-align: middle;
}

#tablaProductosDetalle tbody tr:hover {
    background-color: #f9f9f9;
}

#tablaHistorialAbonos {
    margin-bottom: 0;
}

#tablaHistorialAbonos th {
    background-color: #f5f5f5;
    font-weight: bold;
    border-bottom: 2px solid #ddd;
}

#tablaHistorialAbonos td {
    vertical-align: middle;
}

#tablaHistorialAbonos tbody tr:hover {
    background-color: #f9f9f9;
}

#tablaHistorialAbonos tbody tr.success {
    background-color: #dff0d8;
}

#tablaHistorialAbonos tbody tr.warning {
    background-color: #fcf8e3;
}

.table-condensed td {
    padding: 5px 8px;
}

.table-condensed .success td {
    background-color: #dff0d8;
    font-weight: bold;
}

.table-condensed .warning td {
    background-color: #fcf8e3;
    font-weight: bold;
}

/* Estilos para los botones de acción */
.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Mejorar la tabla principal de ventas */
.tablas tbody tr:hover {
    background-color: #f5f5f5;
}

.tablas .btn-group {
    white-space: nowrap;
}

/* Responsive para el modal */
@media (max-width: 768px) {
    #modalDetalleVenta .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
    
    #modalDetalleVenta .modal-body {
        max-height: 60vh;
    }
    
    .table-responsive {
        font-size: 12px;
    }
}
</style>
