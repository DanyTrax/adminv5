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
              <th style="width:3px">Emp</th>
              <th>Vendedor</th>
              <th>V_Abono</th>
              <th style="width:80px">Forma de pago</th>
              <th>Neto</th>
              <th>Total</th>
              <th>Fecha Venta</th>
              <th>Abono</th>
              <th>Ult_Abono</th>
              <th>Pago</th>
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
                      <td>' . ($respuestaUsuario["empresa"] ?? '') . '</td>
                      <td>' . ($respuestaUsuario["nombre"] ?? '') . '</td>
                      <td>' . ($respuestaUsuario_ab["nombre"] ?? '') . '</td>
                      <td>' . $value["metodo_pago"] . '</td>
                      <td>$ ' . number_format($value["neto"] ?? 0, 2, ',', '.') . '</td>
                      <td>$ ' . number_format($value["total"] ?? 0, 2, ',', '.') . '</td>
                      <td>' . $value["fecha_abono"] . '</td>
                      <td>$ ' . number_format($value["abono"] ?? 0, 2, ',', '.') . '</td>
                      <td>$ ' . number_format($value["Ult_abono"] ?? 0, 2, ',', '.') . '</td>
                      <td>' . $value["pago"] . '</td>
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
