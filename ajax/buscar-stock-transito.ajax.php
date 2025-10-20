<?php
// AJAX para búsqueda de productos en stock en tránsito
session_start();

require_once "../controladores/stock-transito.controlador.php";

class AjaxBuscarStockTransito {
    
    /*=============================================
    BUSCAR PRODUCTOS EN STOCK EN TRÁNSITO
    =============================================*/
    public function ajaxBuscarProductos() {
        
        if(isset($_POST["terminoBusqueda"]) && isset($_POST["filtroTransportador"])) {
            
            $terminoBusqueda = trim($_POST["terminoBusqueda"]);
            $filtroTransportador = $_POST["filtroTransportador"] ?: null;
            
            try {
                // Obtener productos con filtros
                $transportadores = ControladorStockTransito::ctrMostrarStockDisponibleUsuarios($filtroTransportador);
                
                $productosFiltrados = [];
                
                // Filtrar por término de búsqueda
                foreach($transportadores as $transportadorId => $productos) {
                    foreach($productos as $producto) {
                        $codigo = strtolower($producto['codigo_producto']);
                        $descripcion = strtolower($producto['descripcion_producto']);
                        $termino = strtolower($terminoBusqueda);
                        
                        // Si no hay término de búsqueda o coincide
                        if(empty($terminoBusqueda) || 
                           strpos($codigo, $termino) !== false || 
                           strpos($descripcion, $termino) !== false) {
                            
                            $productosFiltrados[] = [
                                'id' => $producto['id'],
                                'codigo_producto' => $producto['codigo_producto'],
                                'descripcion_producto' => $producto['descripcion_producto'],
                                'cantidad_disponible' => $producto['cantidad_disponible'],
                                'numero_despacho' => $producto['numero_despacho'],
                                'sucursal_origen' => $producto['sucursal_origen'],
                                'transportador_id' => $producto['transportador_id'],
                                'nombre_transportador' => $producto['nombre_transportador']
                            ];
                        }
                    }
                }
                
                // Agrupar por transportador
                $transportadoresAgrupados = [];
                foreach($productosFiltrados as $producto) {
                    $transportadoresAgrupados[$producto['transportador_id']][] = $producto;
                }
                
                // Generar HTML
                $html = $this->generarHTMLProductos($transportadoresAgrupados);
                
                echo json_encode([
                    "success" => true,
                    "html" => $html,
                    "totalProductos" => count($productosFiltrados),
                    "totalTransportadores" => count($transportadoresAgrupados)
                ]);
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error en la búsqueda: " . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Parámetros de búsqueda requeridos"
            ]);
        }
    }
    
    /*=============================================
    GENERAR HTML DE PRODUCTOS
    =============================================*/
    private function generarHTMLProductos($transportadores) {
        
        if(empty($transportadores)) {
            return '
                <div class="box box-warning">
                    <div class="box-body text-center">
                        <i class="fa fa-info-circle fa-3x text-muted"></i>
                        <h3 class="text-muted">No se encontraron productos</h3>
                        <p class="text-muted">Intenta con otros términos de búsqueda o filtros.</p>
                    </div>
                </div>
            ';
        }
        
        $html = '';
        
        foreach($transportadores as $transportadorId => $productos) {
            $primerProducto = $productos[0];
            $totalCantidad = array_sum(array_column($productos, 'cantidad_disponible'));
            $totalProductos = count($productos);
            
            $html .= '
                <div class="box box-info transportador-section">
                    <div class="box-header with-border">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="box-title">
                                    <i class="fa fa-truck"></i> 
                                    <strong>' . $primerProducto['nombre_transportador'] . '</strong>
                                </h3>
                                <p class="text-muted" style="margin: 5px 0 0 0;">
                                    <i class="fa fa-map-marker"></i> Transportador responsable
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="info-box-content" style="display: inline-block; text-align: right;">
                                    <span class="info-box-text">Productos</span>
                                    <span class="info-box-number" style="font-size: 24px; color: #17a2b8;">' . $totalProductos . '</span>
                                </div>
                                <div class="info-box-content" style="display: inline-block; text-align: right; margin-left: 20px;">
                                    <span class="info-box-text">Unidades</span>
                                    <span class="info-box-number" style="font-size: 24px; color: #28a745;">' . $totalCantidad . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover productos-table">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 100px;">
                                            <i class="fa fa-barcode"></i> Código
                                        </th>
                                        <th>
                                            <i class="fa fa-cube"></i> Descripción del Producto
                                        </th>
                                        <th style="width: 120px;">
                                            <i class="fa fa-shipping-fast"></i> Despacho
                                        </th>
                                        <th style="width: 120px;">
                                            <i class="fa fa-map-marker"></i> Origen
                                        </th>
                                        <th style="width: 100px; text-align: center;">
                                            <i class="fa fa-cubes"></i> Cantidad
                                        </th>
                                        <th style="width: 100px; text-align: center;">
                                            <i class="fa fa-flag"></i> Estado
                                        </th>
                                        <th style="width: 120px; text-align: center;">
                                            <i class="fa fa-cogs"></i> Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>';
            
            foreach($productos as $producto) {
                $html .= '
                    <tr class="producto-row" 
                        data-codigo="' . strtolower($producto['codigo_producto']) . '" 
                        data-descripcion="' . strtolower($producto['descripcion_producto']) . '">
                        <td>
                            <strong class="text-primary" style="font-size: 16px;">
                                ' . $producto['codigo_producto'] . '
                            </strong>
                        </td>
                        <td>
                            <div style="max-width: 300px;">
                                <strong>' . $producto['descripcion_producto'] . '</strong>
                            </div>
                        </td>
                        <td>
                            <span class="label label-default" style="font-size: 12px;">
                                ' . $producto['numero_despacho'] . '
                            </span>
                        </td>
                        <td>
                            <span class="text-muted">
                                <i class="fa fa-building"></i> 
                                ' . $producto['sucursal_origen'] . '
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge bg-blue" style="font-size: 16px; padding: 8px 12px;">
                                ' . $producto['cantidad_disponible'] . '
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="label label-success" style="font-size: 12px;">
                                <i class="fa fa-check"></i> Disponible
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button class="btn btn-success btn-sm btnDescargaDirecta" 
                                    data-id="' . $producto['id'] . '"
                                    data-codigo="' . $producto['codigo_producto'] . '"
                                    data-descripcion="' . $producto['descripcion_producto'] . '"
                                    data-cantidad="' . $producto['cantidad_disponible'] . '"
                                    data-despacho="' . $producto['numero_despacho'] . '"
                                    data-origen="' . $producto['sucursal_origen'] . '"
                                    data-transportador="' . $producto['nombre_transportador'] . '">
                                <i class="fa fa-download"></i> Descargar
                            </button>
                        </td>
                    </tr>';
            }
            
            $html .= '
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>';
        }
        
        return $html;
    }
}

// Procesar solicitud AJAX
if(isset($_POST["buscarProductos"])) {
    $buscar = new AjaxBuscarStockTransito();
    $buscar->ajaxBuscarProductos();
}
?>
