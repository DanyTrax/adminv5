<?php

session_start();
require_once "../controladores/despachos.controlador.php";
require_once "../modelos/despachos.modelo.php";
require_once "../controladores/sucursales.controlador.php";

class AjaxCrearDespachosSucursales {

    /*=============================================
    CREAR DESPACHOS POR SUCURSAL
    =============================================*/
    public $accion;
    public $despachos_data;

    public function ajaxCrearDespachosSucursales() {
        
        if(!isset($_SESSION['perfil']) || !in_array($_SESSION['perfil'], ['Vendedor', 'Especial', 'Administrador'])) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para crear despachos']);
            return;
        }
        
        try {
            $despachosData = json_decode($this->despachos_data, true);
            
            if(!$despachosData || !is_array($despachosData)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudieron procesar los datos de despachos'
                ]);
                return;
            }
            
            $despachosCreados = [];
            $errores = [];
            
            foreach($despachosData as $sucursalCodigo => $despachoData) {
                
                try {
                    // Obtener información de la sucursal
                    $sucursal = $this->obtenerSucursal($sucursalCodigo);
                    if(!$sucursal) {
                        $errores[] = "Sucursal {$sucursalCodigo} no encontrada";
                        continue;
                    }
                    
                    // Preparar datos del despacho (id_solicitud_origen para que al aceptar la solicitud pase a finalizado)
                    $idSol = isset($despachoData['id_solicitud_origen']) ? (int)$despachoData['id_solicitud_origen'] : null;
                    $datosDespacho = [
                        'id_solicitud_origen' => ($idSol > 0) ? $idSol : null,
                        'id_usuario_creador' => $_SESSION['id'],
                        'nombre_usuario_creador' => $_SESSION['nombre'],
                        'productos_despacho' => json_encode($despachoData['productos']),
                        'total_productos' => count($despachoData['productos']),
                        'total_cantidad' => array_sum(array_column($despachoData['productos'], 'cantidad')),
                        'detalle_adicional' => "Despacho automático desde solicitud - Sucursal: {$sucursal['nombre']}",
                        'sucursal_destino' => $sucursal['nombre']
                    ];
                    
                    // Crear despacho
                    $respuesta = ControladorDespachos::ctrCrearDespacho($datosDespacho);
                    
                    if($respuesta == "ok") {
                        $despachosCreados[] = [
                            'sucursal' => $sucursal['nombre'],
                            'codigo' => $sucursalCodigo,
                            'productos' => count($despachoData['productos']),
                            'cantidad_total' => $datosDespacho['total_cantidad']
                        ];
                    } else {
                        $errores[] = "Error creando despacho para {$sucursal['nombre']}: {$respuesta}";
                    }
                    
                } catch(Exception $e) {
                    $errores[] = "Error procesando sucursal {$sucursalCodigo}: " . $e->getMessage();
                }
            }
            
            // Preparar respuesta
            $response = [
                'success' => count($despachosCreados) > 0,
                'despachos_creados' => $despachosCreados,
                'errores' => $errores,
                'total_despachos' => count($despachosCreados),
                'total_errores' => count($errores)
            ];
            
            if(count($errores) > 0) {
                $response['message'] = "Se crearon " . count($despachosCreados) . " despachos, pero hubo " . count($errores) . " errores";
            } else {
                $response['message'] = "Se crearon " . count($despachosCreados) . " despachos exitosamente";
            }
            
            echo json_encode($response);
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error general: ' . $e->getMessage()
            ]);
        }
    }
    
    /*=============================================
    OBTENER INFORMACIÓN DE SUCURSAL
    =============================================*/
    private function obtenerSucursal($codigoSucursal) {
        
        try {
            $sucursales = ControladorSucursales::ctrMostrarSucursales(null, null);
            
            foreach($sucursales as $sucursal) {
                if($sucursal['codigo_sucursal'] == $codigoSucursal) {
                    return $sucursal;
                }
            }
            
            return null;
            
        } catch(Exception $e) {
            error_log("Error obteniendo sucursal {$codigoSucursal}: " . $e->getMessage());
            return null;
        }
    }
}

/*=============================================
EJECUTAR AJAX
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "crear_despachos_sucursales") {
    
    $ajax = new AjaxCrearDespachosSucursales();
    $ajax->accion = $_POST["accion"];
    $ajax->despachos_data = $_POST["despachos_data"];
    $ajax->ajaxCrearDespachosSucursales();
}
