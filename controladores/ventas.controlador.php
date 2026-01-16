<?php

require_once __DIR__ . "/../src/Utils.php";
require_once __DIR__ . "/../modelos/ventas.modelo.php";
require_once __DIR__ . "/../modelos/productos.modelo.php";
require_once __DIR__ . "/../modelos/clientes.modelo.php";
require_once __DIR__ . "/../modelos/contabilidad.modelo.php";
class ControladorVentas {

    /*=============================================
    HELPERS
    =============================================*/
    private static function mostrarAlerta($tipo, $mensaje, $redir = null) {
        echo "<script>
            swal({
                type: '$tipo',
                title: '$mensaje',
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            }).then((result) => {
                if (result.value && '$redir') window.location = '$redir';
            });
        </script>";
    }

    /**
     * ===================================================================
     * FUNCIÓN AUXILIAR PARA CONVERTIR MONEDA (VERSIÓN CORREGIDA)
     * ===================================================================
     * Convierte un string de moneda (ej: "1,390,760") a un float (ej: 1390760.00)
     */
    private static function convertirMonedaAFloat($valor) {
        // 1. Quitar el separador de miles (,)
        $sinComas = str_replace(',', '', $valor);
        // 2. Convertir a float. PHP maneja el punto decimal si lo hubiera.
        return (float)$sinComas;
    }
    /*=============================================
    FUNCIONES GENERALES: Mostrar, Filtrar, Totales (Tu código original)
    =============================================*/
    public static function filterBy($fechaInicial, $fechaFinal, $medioPago, $formaPago) {
        return ModeloVentas::mdlFilterBy("ventas", $fechaInicial, $fechaFinal, $medioPago, $formaPago);
    }
    public static function ctrMostrarVentas($item, $valor) {
        return ModeloVentas::mdlMostrarVentas("ventas", $item, $valor);
    }
    public static function ctrRangoFechasVentas($fechaInicial, $fechaFinal) {
        return ModeloVentas::mdlRangoFechasVentas("ventas", $fechaInicial, $fechaFinal);
    }

    public static function ctrSumaTotalVentas() {
        $total = ModeloVentas::mdlSumaTotalVentas("ventas");
        return $total["total"] ?? 0;
    }

    public static function ctrSumaTotalVentas1() {
        $total = ModeloVentas::mdlSumaTotalVentas1("ventas");
        return $total["total"] ?? 0;
    }

    public static function ctrSumaTotalVentas2() {
        $total = ModeloVentas::mdlSumaTotalVentas2("ventas");
        return $total["total"] ?? 0;
    }

    public static function ctrSumaTotalVentas3() {
        $total = ModeloVentas::mdlSumaTotalVentas3("ventas");
        return $total["total"] ?? 0;
    }

    /*=============================================
    CREAR VENTA (FUNCIÓN CORREGIDA)
    =============================================*/
    static public function ctrCrearVenta() {
        
        if (isset($_POST["nuevaVenta"])) {

            if ($_POST["listaProductos"] == "") {
                self::mostrarAlerta("error", "La venta no puede ejecutarse sin productos", "crear-venta");
                return;
            }

            // --- LÓGICA DE ACTUALIZACIÓN DE PRODUCTOS Y CLIENTES ---
            // CAMBIO: Identificar productos por código en lugar de id != "libre"
            $listaProductos = json_decode($_POST["listaProductos"], true);
            $productosComprados = [];
            foreach ($listaProductos as $item) {
                // Verificar si es producto de stock (tiene ID numérico, no "libre")
                if (isset($item["id"]) && $item["id"] != "libre" && is_numeric($item["id"])) {
                    $productoActual = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id"], "id");
                    if($productoActual){
                        // Verificar si tiene código (producto de stock) o no (entrada manual)
                        $tieneCodigo = !empty($productoActual["codigo"]) || !empty($productoActual["codigo_maestro"]);
                        
                        if($tieneCodigo) {
                            // Es producto de stock: actualizar ventas y stock
                            $productosComprados[] = $item["cantidad"];
                            $nuevasVentas = $item["cantidad"] + $productoActual["ventas"];
                            $nuevoStock = $productoActual["stock"] - $item["cantidad"]; // CORRECCIÓN: Calcular stock correctamente
                            
                            ModeloProductos::mdlActualizarCampo("productos", "ventas", $nuevasVentas, $item["id"]);
                            ModeloProductos::mdlActualizarCampo("productos", "stock", $nuevoStock, $item["id"]);
                        }
                        // Si no tiene código, es entrada manual y no se actualiza stock
                    }
                }
            }
            $cliente = ModeloClientes::mdlMostrarClientes("clientes", "id", $_POST["seleccionarCliente"]);
            if($cliente){
                $nuevasCompras = array_sum($productosComprados) + $cliente["compras"];
                ModeloClientes::mdlActualizarCliente("clientes", "compras", $nuevasCompras, $_POST["seleccionarCliente"]);
                $fechaHora = date("Y-m-d H:i:s");
                ModeloClientes::mdlActualizarCliente("clientes", "ultima_compra", $fechaHora, $_POST["seleccionarCliente"]);
            }
            
            // --- PREPARACIÓN DE DATOS PARA GUARDAR ---
            $totalVenta = self::convertirMonedaAFloat($_POST["totalVenta"]);
            $valorAbono = 0;

            if ($_POST["nuevoMetodoPago"] == "Abono" && isset($_POST["nuevoValorEfectivo"])) {
                $valorAbono = self::convertirMonedaAFloat($_POST["nuevoValorEfectivo"]);
            } else if ($_POST["nuevoMetodoPago"] == "Completo") {
                $valorAbono = $totalVenta;
            }
            
            $metodoPagoFinal = $_POST["nuevoMetodoPago"];
            if ($metodoPagoFinal == "Abono" && $valorAbono >= $totalVenta) {
                $metodoPagoFinal = "Completo";
            }

            $datosVenta = [
                "id_vendedor"   => $_POST["idVendedor"],
                "id_cliente"    => $_POST["seleccionarCliente"],
                "codigo"        => $_POST["nuevaVenta"],
                "productos"     => $_POST["listaProductos"],
                "impuesto"      => self::convertirMonedaAFloat($_POST["nuevoPrecioImpuesto"]),
                "descuento"     => self::convertirMonedaAFloat($_POST["nuevoPrecioDescuento"]),
                "neto"          => self::convertirMonedaAFloat($_POST["nuevoPrecioNeto"]),
                "total"         => $totalVenta,
                "detalle"       => $_POST["detalle"],
                "metodo_pago"   => $metodoPagoFinal,
                "fecha_venta"   => date("Y-m-d H:i:s"),
                "id_vend_abono" => $_POST["idVendedor"],
                "abono"         => $valorAbono,
                "fecha_abono"   => date("Y-m-d H:i:s"),
                "pago"          => $_POST["pago"] ?? "",
                "medio_pago"    => $_POST["nuevoMedioPago"] ?? ""
            ];

            $respuesta = ModeloVentas::mdlIngresarVenta("ventas", $datosVenta);

            if ($respuesta === "ok") {
                
                // --- INICIO DE LA CORRECCIÓN: SE RESTAURA EL GUARDADO EN CONTABILIDAD ---
                $detalleEntrada = "Venta factura No. " . $_POST["nuevaVenta"] . " por " . $_SESSION["nombre"];
                if ($metodoPagoFinal === "Completo") {
                    $detalleEntrada .= " - pago completo";
                }

                $valorContable = ($_POST["nuevoMetodoPago"] === "Se Debe") ? 0 : $valorAbono;

                ModeloContabilidad::save([
                    "id_vendedor" => $_POST["idVendedor"], 
                    "fecha" => $datosVenta["fecha_venta"],
                    "detalle"     => $detalleEntrada, 
                    "valor" => $valorContable,
                    "medio_pago"  => $_POST["nuevoMedioPago"] ?? "", 
                    "forma_pago"  => $_POST["nuevoMetodoPago"],
                    "factura"     => $_POST["nuevaVenta"], 
                    "tipo" => "Entrada"
                ]);
                // --- FIN DE LA CORRECCIÓN ---
                
                // Registrar pago inicial en historial de abonos (si hay pago)
                if ($valorAbono > 0) {
                    $usuarioVendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $_POST["idVendedor"]);
                    
                    $datosHistorial = array(
                        "id_venta" => null, // Se obtendrá después de insertar la venta
                        "codigo_venta" => $_POST["nuevaVenta"],
                        "monto_abono" => $valorAbono,
                        "fecha_abono" => $datosVenta["fecha_venta"],
                        "id_vendedor_abono" => $_POST["idVendedor"],
                        "nombre_vendedor_abono" => $usuarioVendedor["nombre"],
                        "medio_pago" => $_POST["nuevoMedioPago"] ?? "",
                        "observaciones" => $metodoPagoFinal === "Completo" ? "Pago completo inicial" : "Abono inicial"
                    );
                    
                    // Obtener el ID de la venta recién creada
                    $ventaCreada = ModeloVentas::mdlMostrarVentas("ventas", "codigo", $_POST["nuevaVenta"]);
                    if ($ventaCreada) {
                        $datosHistorial["id_venta"] = $ventaCreada["id"];
                        ModeloVentas::mdlRegistrarAbonoHistorial($datosHistorial);
                    }
                }

                echo '<script>
                    localStorage.removeItem("rango");
                    swal({
                        type: "success", title: "La venta ha sido guardada correctamente",
                        showConfirmButton: true, confirmButtonText: "Cerrar"
                    }).then(function(result){ if (result.value) window.location = "ventas"; });
                </script>';
            }
        }
    }

    /*=============================================
    EDITAR VENTA (FUNCIÓN CORREGIDA)
    =============================================*/
// En: controladores/ventas.controlador.php

static public function ctrEditarVenta() {
    
    if (isset($_POST["editarVenta"])) {

        $ventaAnterior = ModeloVentas::mdlMostrarVentas("ventas", "codigo", $_POST["editarVenta"]);
        if(!$ventaAnterior) { return "error_venta_no_encontrada"; }

        $listaOriginal = json_decode($ventaAnterior["productos"], true) ?: [];
        $listaNueva = json_decode($_POST["listaProductos"], true) ?: [];

        if ($listaOriginal != $listaNueva || $ventaAnterior["id_cliente"] != $_POST["seleccionarCliente"]) {
            // CAMBIO: Identificar productos por código y calcular stock correctamente
            // Lógica de revertir y aplicar stock y compras
            $clienteOriginal = ModeloClientes::mdlMostrarClientes("clientes", "id", $ventaAnterior["id_cliente"]);
            $totalProductosOriginales = 0;
            
            // REVERTIR: Restaurar stock de productos originales (solo si tienen código)
            foreach ($listaOriginal as $item) {
                if(isset($item["id"]) && $item["id"] != "libre" && is_numeric($item["id"])){
                    $producto = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id"], "id");
                    if($producto){
                        // Verificar si tiene código (producto de stock)
                        $tieneCodigo = !empty($producto["codigo"]) || !empty($producto["codigo_maestro"]);
                        
                        if($tieneCodigo) {
                            // Es producto de stock: revertir ventas y restaurar stock
                            $totalProductosOriginales += $item["cantidad"];
                            $nuevasVentas = $producto["ventas"] - $item["cantidad"];
                            $nuevoStock = $producto["stock"] + $item["cantidad"]; // CORRECCIÓN: Sumar al stock al revertir
                            
                            ModeloProductos::mdlActualizarCampo("productos", "ventas", $nuevasVentas, $item["id"]);
                            ModeloProductos::mdlActualizarCampo("productos", "stock", $nuevoStock, $item["id"]);
                        }
                        // Si no tiene código, es entrada manual y no se actualiza stock
                    }
                }
            }
            if($clienteOriginal) {
                 ModeloClientes::mdlActualizarCliente("clientes", "compras", $clienteOriginal["compras"] - $totalProductosOriginales, $ventaAnterior["id_cliente"]);
            }
           
            // APLICAR: Actualizar stock de productos nuevos (solo si tienen código)
            $clienteNuevo = ModeloClientes::mdlMostrarClientes("clientes", "id", $_POST["seleccionarCliente"]);
            $totalProductosNuevos = 0;
            foreach ($listaNueva as $item) {
                if(isset($item["id"]) && $item["id"] != "libre" && is_numeric($item["id"])){
                    $producto = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id"], "id");
                    if($producto){
                        // Verificar si tiene código (producto de stock)
                        $tieneCodigo = !empty($producto["codigo"]) || !empty($producto["codigo_maestro"]);
                        
                        if($tieneCodigo) {
                            // Es producto de stock: actualizar ventas y reducir stock
                            $totalProductosNuevos += $item["cantidad"];
                            $nuevasVentas = $producto["ventas"] + $item["cantidad"];
                            $nuevoStock = $producto["stock"] - $item["cantidad"]; // CORRECCIÓN: Calcular stock correctamente
                            
                            ModeloProductos::mdlActualizarCampo("productos", "ventas", $nuevasVentas, $item["id"]);
                            ModeloProductos::mdlActualizarCampo("productos", "stock", $nuevoStock, $item["id"]);
                        }
                        // Si no tiene código, es entrada manual y no se actualiza stock
                    }
                }
            }
            if($clienteNuevo){
                ModeloClientes::mdlActualizarCliente("clientes", "compras", $clienteNuevo["compras"] + $totalProductosNuevos, $_POST["seleccionarCliente"]);
            }
        }
        
        // CAMBIO: Calcular y validar abono correctamente
        $nuevoTotal = self::convertirMonedaAFloat($_POST["totalVenta"] ?? 0);
        $abonoEditado = self::convertirMonedaAFloat($_POST["nuevoAbono"] ?? $ventaAnterior["abono"]);
        
        // Validar que el abono no exceda el nuevo total
        if($abonoEditado > $nuevoTotal) {
            $abonoEditado = $nuevoTotal; // Ajustar abono al total si excede
        }
        
        // Determinar método de pago según el abono
        $metodoPagoEditado = $_POST["nuevoMetodoPago"] ?? $ventaAnterior["metodo_pago"];
        if($abonoEditado >= $nuevoTotal && $nuevoTotal > 0) {
            $metodoPagoEditado = "Completo";
        } else if($abonoEditado > 0 && $abonoEditado < $nuevoTotal) {
            $metodoPagoEditado = "Abono";
        } else if($abonoEditado == 0 && $nuevoTotal > 0) {
            $metodoPagoEditado = "Se Debe";
        }
        
        $datosEditados = [
            "codigo" => $_POST["editarVenta"],
            "id_cliente" => $_POST["seleccionarCliente"],
            "id_vendedor" => $_POST["idVendedor"],
            "productos" => $_POST["listaProductos"],
            "impuesto" => self::convertirMonedaAFloat($_POST["nuevoPrecioImpuesto"] ?? 0),
            "neto" => self::convertirMonedaAFloat($_POST["nuevoPrecioNeto"] ?? 0),
            "total" => $nuevoTotal,
            "detalle" => $_POST["detalle"] ?? "",
            "metodo_pago" => $metodoPagoEditado,
            "pago" => $_POST["pago"] ?? "",
            "medio_pago" => $_POST["nuevoMedioPago"] ?? $ventaAnterior["medio_pago"],
            "abono" => $abonoEditado,
            "id_vend_abono" => $_SESSION["id"] ?? $ventaAnterior["id_vend_abono"],
            "fecha_abono" => date("Y-m-d H:i:s"), // Actualizar fecha de abono
            "fecha_venta" => $ventaAnterior["fecha_venta"]
        ];

        $respuesta = ModeloVentas::mdlEditarVenta("ventas", $datosEditados);

        if ($respuesta === "ok") {
            // CAMBIO: Actualizar solo la entrada original de la venta en contabilidad
            // Mantener abonos adicionales independientes (no se eliminan)
            $detalleEntrada = "Venta (editada) factura No. " . $_POST["editarVenta"] . " por " . ($_SESSION["nombre"] ?? 'N/A');
            if ($metodoPagoEditado === "Completo") {
                $detalleEntrada .= " - pago completo";
            }
            
            // Usar el abono real de la venta editada
            $valorContable = ($metodoPagoEditado === "Se Debe") ? 0 : $abonoEditado;

            // Actualizar solo la primera entrada (la original de la venta)
            // Los abonos adicionales posteriores se mantienen independientes
            ModeloContabilidad::updateFirstEntryByFactura($_POST["editarVenta"], [
                "id_vendedor" => $_SESSION["id"] ?? 0,
                "fecha" => date("Y-m-d H:i:s"),
                "detalle" => $detalleEntrada,
                "valor" => $valorContable,
                "medio_pago" => $datosEditados["medio_pago"],
                "forma_pago" => $datosEditados["metodo_pago"]
            ]);
        }

        return $respuesta;
    }
    return "error_post_no_recibido";
}

/*=============================================
LÓGICA PRIVADA PARA REVERTIR CAMBIOS DE VENTA (VERSIÓN FINAL)
=============================================*/
private static function _revertirCambiosVenta($idVenta){

    $venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);
    if(!$venta){ return false; }

    $idCliente = $venta["id_cliente"];
    $productos = json_decode($venta["productos"], true);

    // CAMBIO: Revertir stock solo para productos con código
    if (is_array($productos)) {
        foreach ($productos as $item) {
            if(isset($item["id"]) && $item["id"] != "libre" && is_numeric($item["id"])){
                $producto = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id"], "id");
                if($producto){
                    // Verificar si tiene código (producto de stock)
                    $tieneCodigo = !empty($producto["codigo"]) || !empty($producto["codigo_maestro"]);
                    
                    if($tieneCodigo) {
                        // Es producto de stock: revertir ventas y restaurar stock
                        ModeloProductos::mdlActualizarCampo("productos", "ventas", $producto["ventas"] - $item["cantidad"], $item["id"]);
                        ModeloProductos::mdlActualizarCampo("productos", "stock", $producto["stock"] + $item["cantidad"], $item["id"]);
                    }
                    // Si no tiene código, es entrada manual y no se actualiza stock
                }
            }
        }
    }

    // 2. Actualizar cliente
    $cliente = ModeloClientes::mdlMostrarClientes("clientes", "id", $idCliente);
    if($cliente && is_array($productos)){
        $totalCompradoEnVenta = array_sum(array_column($productos, "cantidad"));
        ModeloClientes::mdlActualizarCliente("clientes", "compras", $cliente["compras"] - $totalCompradoEnVenta, $idCliente);

        // Recalcular la última compra de forma segura
        $ventasCliente = ModeloVentas::mdlMostrarVentas("ventas", "id_cliente", $idCliente);
        $ultimaFecha = "0000-00-00 00:00:00"; 

        if (is_array($ventasCliente) && count($ventasCliente) > 1) {
            $otrasVentas = array_filter($ventasCliente, function($v) use ($idVenta) {
                return is_array($v) && $v['id'] != $idVenta;
            });
            if(!empty($otrasVentas)){
                $fechas = array_column($otrasVentas, "fecha_venta");
                rsort($fechas);
                $ultimaFecha = $fechas[0];
            }
        }
        ModeloClientes::mdlActualizarCliente("clientes", "ultima_compra", $ultimaFecha, $idCliente);
    }
    
    return true;
}

/*=============================================
ELIMINAR VENTA (PARA AJAX - VERSIÓN FINAL)
=============================================*/
static public function ctrEliminarVentaAjax($idVenta){
    self::_revertirCambiosVenta($idVenta);
    $respuesta = ModeloVentas::mdlEliminarVenta("ventas", $idVenta);
    echo $respuesta;
}

/*=============================================
ELIMINAR VENTA (PARA RECARGA DE PÁGINA - VERSIÓN FINAL)
=============================================*/
static public function ctrEliminarVenta() {
    if (isset($_GET["idVenta"])) {
        self::_revertirCambiosVenta($_GET["idVenta"]);
        $respuesta = ModeloVentas::mdlEliminarVenta("ventas", $_GET["idVenta"]);
        if ($respuesta === "ok") {
            echo '<script>
                swal({
                    type: "success",
                    title: "La venta ha sido borrada correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "ventas";
                    }
                });
            </script>';
        }
    }
}
    
    /*=============================================
    DESCARGAR XML (estructura básica estilo DIAN)
    =============================================*/
    static public function ctrDescargarXML() {
        if (!isset($_GET["xml"])) return;
        $venta = ModeloVentas::mdlMostrarVentas("ventas", "codigo", $_GET["xml"]);
        $productos = json_decode($venta["productos"], true);
        $xml = new XMLWriter();
        $xml->openURI($_GET["xml"] . ".xml");
        $xml->setIndent(true);
        $xml->setIndentString("\t");
        $xml->startDocument('1.0', 'utf-8');
        $xml->writeRaw('<fe:Invoice xmlns:fe="http://www.dian.gov.co/contratos/facturaelectronica/v1">');
        $xml->writeRaw('<ext:UBLExtensions>');
        foreach ($productos as $item) {
            $xml->text($item["descripcion"] . ", ");
        }
        $xml->writeRaw('</ext:UBLExtensions>');
        $xml->writeRaw('</fe:Invoice>');
        $xml->endDocument();
        return true;
    }

    /*=============================================
    CREAR ABONO (VERSIÓN FINAL CON DETALLE CORREGIDO)
    =============================================*/
    static public function ctrCrearAbono(){
    
        if(isset($_POST["nuevoAbono"]) && !empty($_POST["nuevoAbono"])){
            date_default_timezone_set('America/Bogota');
    
            $valorAbonoLimpio = str_replace(",", "", $_POST["nuevoAbono"]);
            $tablaVentas = "ventas";
            $ventaActual = ModeloVentas::mdlMostrarVentas($tablaVentas, "id", $_POST["idVentaAbo"]);
            $dineroRestante = $ventaActual["total"] - $ventaActual["abono"];
    
            if($valorAbonoLimpio > $dineroRestante){
                echo'<script>
                    swal({
                          type: "error",
                          title: "El abono no puede ser mayor a la deuda",
                          showConfirmButton: true,
                          confirmButtonText: "Cerrar"
                          }).then(function(result){
                            if (result.value) {
                                window.history.back();
                            }
                        })
                </script>';
                return;
            }
    
            $abonoTotal = $ventaActual["abono"] + $valorAbonoLimpio;
            $metodoPagoFinal = ($abonoTotal >= $ventaActual["total"]) ? "Completo" : "Abono";
            
            // El medio de pago se mantiene siempre original para no dañar los filtros.
            $medioPagoOriginal = $_POST["nuevoMedioPagoAbono"];
    
            $datosVenta = array(
                "id" => $_POST["idVentaAbo"],
                "abono" => $abonoTotal,
                "Ult_abono" => $valorAbonoLimpio,
                "id_vend_abono" => $_POST["idUsuarioAbo"],
                "fecha_abono" => date('Y-m-d H:i:s'),
                "medio_pago" => $medioPagoOriginal, 
                "metodo_pago" => $metodoPagoFinal
            );
    
            $respuestaVenta = ModeloVentas::mdlActualizarAbono($tablaVentas, $datosVenta);
    
            if($respuestaVenta == "ok"){
    
                $usuarioAbono = ControladorUsuarios::ctrMostrarUsuarios("id", $_POST["idUsuarioAbo"]);
                
                // --- LÓGICA CORREGIDA ---
                // 1. Se crea el detalle base de la entrada.
                $detalleEntrada = "Abono a factura No. " . $ventaActual["codigo"] . " por " . $usuarioAbono["nombre"];
    
                // 2. Si el pago completó la venta, se añade el texto al DETALLE.
                if($metodoPagoFinal == "Completo"){
                    $detalleEntrada .= " - pago completo";
                }
                // --- FIN DE LA CORRECCIÓN ---
    
                $datosEntrada = array(
                    "id_vendedor" => $_POST["idUsuarioAbo"],
                    "factura"     => $ventaActual["codigo"],
                    "fecha"       => date('Y-m-d H:i:s'),
                    "detalle"     => $detalleEntrada, // Se usa el detalle modificado
                    "valor"       => $valorAbonoLimpio,
                    "medio_pago"  => $medioPagoOriginal, // Se usa el medio de pago original
                    "forma_pago"  => "Abono",
                    "tipo"        => "Entrada"
                );
    
                ModeloContabilidad::save($datosEntrada);
                
                // Registrar en historial de abonos
                $datosHistorial = array(
                    "id_venta" => $_POST["idVentaAbo"],
                    "codigo_venta" => $ventaActual["codigo"],
                    "monto_abono" => $valorAbonoLimpio,
                    "fecha_abono" => date('Y-m-d H:i:s'),
                    "id_vendedor_abono" => $_POST["idUsuarioAbo"],
                    "nombre_vendedor_abono" => $usuarioAbono["nombre"],
                    "medio_pago" => $medioPagoOriginal,
                    "observaciones" => $metodoPagoFinal == "Completo" ? "Pago completo" : "Abono parcial"
                );
                
                ModeloVentas::mdlRegistrarAbonoHistorial($datosHistorial);
    
                echo'<script>
                    swal({
                          type: "success",
                          title: "El abono ha sido guardado correctamente",
                          showConfirmButton: true,
                          confirmButtonText: "Cerrar"
                          }).then(function(result){
                                if (result.value) {
                                window.location = "ventas";
                                }
                            })
                    </script>';
            }
        }
    }

    /*=============================================
    REPORTE FILTRADO EN EXCEL
    =============================================*/
    public static function reporteVenta() {
        $fechaInicial = $_GET["fechaInicial"] ?? null;
        $fechaFinal   = $_GET["fechaFinal"] ?? null;
        $medioPago    = $_GET["medioPago"] ?? null;
        $formaPago    = $_GET["formaPago"] ?? null;
        $ventas = self::filterBy($fechaInicial, $fechaFinal, $medioPago, $formaPago);
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=reporte.xls");
        echo "<table border='1'>
            <tr>
                <th>#</th><th>FACTURA</th><th>CLIENTE</th><th>EMP</th><th>VENDEDOR</th>
                <th>ABONADOR</th><th>FORMA DE PAGO</th><th>NETO</th><th>TOTAL</th>
                <th>FECHA VENTA</th><th>ABONO</th><th>ÚLTIMO ABONO</th><th>PAGO</th><th>MEDIO PAGO</th>
            </tr>";
        foreach ($ventas as $i => $v) {
            $cliente = ControladorClientes::ctrMostrarClientes("id", $v["id_cliente"]);
            $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $v["id_vendedor"]);
            $abonador = ControladorUsuarios::ctrMostrarUsuarios("id", $v["id_vend_abono"]);
            echo "<tr>
                <td>" . ($i + 1) . "</td>
                <td>{$v['codigo']}</td>
                <td>{$cliente['nombre']}</td>
                <td>{$abonador['empresa']}</td>
                <td>{$vendedor['nombre']}</td>
                <td>{$abonador['nombre']}</td>
                <td>{$v['metodo_pago']}</td>
                <td>$ " . numberFormat($v['neto'], 0) . "</td>
                <td>$ " . numberFormat($v['total'], 0) . "</td>
                <td>{$v['fecha_abono']}</td>
                <td>$ " . numberFormat($v['abono'], 0) . "</td>
                <td>$ " . number_format($value["Ult_abono"] ?? 0, 0) . "</td>
                <td>{$v['pago']}</td>
                <td>{$v['medio_pago']}</td>
            </tr>";
        }
        echo "</table>";
    }
    /*=============================================
    SUMAR VENTAS POR VENDEDOR
    =============================================*/
    static public function ctrSumaVentasPorVendedor($fechaInicial, $fechaFinal){
    
        $tablaVentas = "ventas";
        $tablaUsuarios = "usuarios";
    
        $respuesta = ModeloVentas::mdlSumaVentasPorVendedor($tablaVentas, $tablaUsuarios, $fechaInicial, $fechaFinal);
    
        return $respuesta;
    
    }
    /*=============================================
    SUMAR TOTAL DE DEUDA (POR COBRAR) (CORREGIDO)
    =============================================*/
    static public function ctrSumaTotalDeuda($fechaInicial, $fechaFinal){
        $tabla = "ventas";
        $respuesta = ModeloVentas::mdlSumaTotalDeuda($tabla, $fechaInicial, $fechaFinal);
    
        // Se asegura de devolver 0 si no hay resultados
        if(!$respuesta){
            return ["total_deuda" => 0];
        }
    
        return $respuesta;
    }
    
/*=============================================
SUMAR TOTAL DE VENTAS (GENERAL) (LÓGICA CORREGIDA)
=============================================*/
static public function ctrSumaTotalVentasGeneral($fechaInicial, $fechaFinal){

    // Para asegurar la consistencia, el Total Vendido SIEMPRE será
    // la suma de lo que ha entrado más lo que está pendiente por cobrar.

    // 1. Obtenemos el total de entradas (dinero recibido)
    $totalEntradas = ControladorContabilidad::ctrSumaTotalEntradas($fechaInicial, $fechaFinal);

    // 2. Obtenemos el total de la deuda (dinero por cobrar)
    $totalDeuda = ControladorVentas::ctrSumaTotalDeuda($fechaInicial, $fechaFinal);

    // 3. Calculamos el total vendido sumando los dos valores anteriores
    $totalVendidoCalculado = ($totalEntradas["total"] ?? 0) + ($totalDeuda["total_deuda"] ?? 0);

    // 4. Devolvemos el resultado en el formato que el resto del sistema espera
    return array("total_ventas" => $totalVendidoCalculado);
}
    /*=============================================
    DESCARGAR REPORTE EN EXCEL (CORREGIDO)
    =============================================*/
    public function ctrDescargarReporte($fechaInicial, $fechaFinal){
    
    	// Se establece el nombre del archivo
    	$nombreArchivo = 'reporte-general';
    	if ($fechaInicial != null) {
    		$nombreArchivo .= '_' . $fechaInicial . '_a_' . $fechaFinal;
    	}
    
    	header('Expires: 0');
    	header('Cache-control: private');
    	header("Content-type: application/vnd.ms-excel; charset=utf-8");
    	header("Cache-Control: cache, must-revalidate"); 
    	header('Content-Description: File Transfer');
    	header('Last-Modified: ' . date('D, d M Y H:i:s'));
    	header("Pragma: public"); 
    	header('Content-Disposition:; filename="' . $nombreArchivo . '.xls"');
    	header("Content-Transfer-Encoding: binary");
    
    	// --- SE OBTIENEN LOS DATOS USANDO LOS FILTROS DE FECHA ---
    	$totalEntradas = self::ctrSumaTotalEntradas($fechaInicial, $fechaFinal);
    	$totalDeuda = self::ctrSumaTotalDeuda($fechaInicial, $fechaFinal);
    	$totalVendido = self::ctrSumaTotalVentasGeneral($fechaInicial, $fechaFinal);
    	$ventasPorVendedor = self::ctrSumaVentasPorVendedor($fechaInicial, $fechaFinal);
    	// ... (puedes agregar aquí las demás consultas de reportes que necesites)
    
    	// Se empieza a construir la tabla HTML que se convertirá en Excel
    	echo utf8_decode("
    	<table border='1'>
    		<tr><td colspan='3' style='font-weight:bold; background-color:#3c8dbc; color:white;'>RESUMEN GENERAL</td></tr>
    		<tr>
    			<td style='font-weight:bold;'>Total Entradas (Dinero Ingresado)</td>
    			<td style='font-weight:bold;'>Total por Cobrar (Deuda)</td>
    			<td style='font-weight:bold;'>Total Vendido (Generado en Ventas)</td>
    		</tr>
    		<tr>
    			<td>" . number_format($totalEntradas["total"] ?? 0, 0) . "</td>
    			<td>" . number_format($totalDeuda["total_deuda"] ?? 0, 0) . "</td>
    			<td>" . number_format($totalVendido["total_ventas"] ?? 0, 0) . "</td>
    		</tr>
    		
    		<tr></tr> <tr><td colspan='2' style='font-weight:bold; background-color:#00a65a; color:white;'>TOTAL DE VENTAS POR VENDEDOR</td></tr>
    		<tr>
    			<td style='font-weight:bold;'>Vendedor</td>
    			<td style='font-weight:bold;'>Total Vendido</td>
    		</tr>
    	");
    
    	foreach ($ventasPorVendedor as $vendedor) {
    		echo utf8_decode("
    		<tr>
    			<td>" . $vendedor['vendedor'] . "</td>
    			<td>" . number_format($vendedor['total_vendido'], 0) . "</td>
    		</tr>
    		");
    	}
    
    	echo "</table>";
    }
    
    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA
    =============================================*/
    public static function ctrObtenerProductosVenta($idVenta) {
        return ModeloVentas::mdlObtenerProductosVenta($idVenta);
    }
    
    /*=============================================
    OBTENER HISTORIAL DE ABONOS DE UNA VENTA
    =============================================*/
    public static function ctrObtenerHistorialAbonos($idVenta) {
        return ModeloVentas::mdlObtenerHistorialAbonos($idVenta);
    }
}