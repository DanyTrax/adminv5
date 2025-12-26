<?php

require_once __DIR__ . "/../modelos/categorias-central.modelo.php";

class ControladorCategoriasCentral {

    /*=============================================
    CREAR CATEGORÍA CENTRAL
    =============================================*/
    static public function ctrCrearCategoriaCentral() {
        
        if (isset($_POST["categoria"])) {
            
            // Validar datos
            if (empty($_POST["categoria"])) {
                return [
                    'success' => false,
                    'message' => 'El nombre de la categoría es requerido'
                ];
            }
            
            // Verificar si la categoría ya existe
            if (ModeloCategoriasCentral::mdlVerificarCategoriaExistente($_POST["categoria"])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe una categoría con ese nombre'
                ];
            }
            
            $datos = [
                'categoria' => $_POST["categoria"],
                'descripcion' => $_POST["descripcion"] ?? '',
                'activo' => isset($_POST["activo"]) ? (bool)$_POST["activo"] : true
            ];
            
            $respuesta = ModeloCategoriasCentral::mdlCrearCategoriaCentral($datos);
            
            return $respuesta;
        }
    }

    /*=============================================
    EDITAR CATEGORÍA CENTRAL
    =============================================*/
    static public function ctrEditarCategoriaCentral() {
        
        if (isset($_POST["id"]) && isset($_POST["categoria"])) {
            
            // Validar datos
            if (empty($_POST["categoria"])) {
                return [
                    'success' => false,
                    'message' => 'El nombre de la categoría es requerido'
                ];
            }
            
            // Verificar si la categoría ya existe (excluyendo la actual)
            if (ModeloCategoriasCentral::mdlVerificarCategoriaExistente($_POST["categoria"], $_POST["id"])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe otra categoría con ese nombre'
                ];
            }
            
            $datos = [
                'categoria' => $_POST["categoria"]
            ];
            
            $respuesta = ModeloCategoriasCentral::mdlEditarCategoriaCentral($_POST["id"], $datos);
            
            return $respuesta;
        }
        
        return [
            'success' => false,
            'message' => 'Datos insuficientes para editar la categoría'
        ];
    }

    /*=============================================
    ELIMINAR CATEGORÍA CENTRAL
    =============================================*/
    static public function ctrEliminarCategoriaCentral() {
        
        if (isset($_POST["id"])) {
            
            $respuesta = ModeloCategoriasCentral::mdlEliminarCategoriaCentral($_POST["id"]);
            
            return $respuesta;
        }
        
        return [
            'success' => false,
            'message' => 'ID de categoría requerido'
        ];
    }

    /*=============================================
    SINCRONIZAR CATEGORÍAS CON SUCURSALES
    =============================================*/
    static public function ctrSincronizarCategoriasSucursales() {
        
        $respuesta = ModeloCategoriasCentral::mdlSincronizarCategoriasSucursales();
        
        return $respuesta;
    }

    /*=============================================
    SINCRONIZAR BIDIRECCIONAL
    =============================================*/
    static public function ctrSincronizarBidireccional() {
        
        if (!isset($_POST["direccion"])) {
            return [
                'success' => false,
                'message' => 'Dirección de sincronización no especificada'
            ];
        }
        
        $direccion = $_POST["direccion"];
        
        switch ($direccion) {
            case 'central_a_actual':
                return ModeloCategoriasCentral::mdlSincronizarCentralASucursalActual();
                
            case 'actual_a_central':
                return ModeloCategoriasCentral::mdlSincronizarSucursalActualACentral();
                
            case 'central_a_multiples':
                if (!isset($_POST["sucursales"]) || empty($_POST["sucursales"])) {
                    return [
                        'success' => false,
                        'message' => 'Debe seleccionar al menos una sucursal'
                    ];
                }
                $idsSucursales = is_array($_POST["sucursales"]) ? $_POST["sucursales"] : json_decode($_POST["sucursales"], true);
                return ModeloCategoriasCentral::mdlSincronizarCentralAMultiplesSucursales($idsSucursales);
                
            case 'multiples_a_central':
                if (!isset($_POST["sucursales"]) || empty($_POST["sucursales"])) {
                    return [
                        'success' => false,
                        'message' => 'Debe seleccionar al menos una sucursal'
                    ];
                }
                $idsSucursales = is_array($_POST["sucursales"]) ? $_POST["sucursales"] : json_decode($_POST["sucursales"], true);
                return ModeloCategoriasCentral::mdlSincronizarMultiplesSucursalesACentral($idsSucursales);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Dirección de sincronización inválida'
                ];
        }
    }

    /*=============================================
    OBTENER CATEGORÍAS CENTRALES
    =============================================*/
    static public function ctrObtenerCategoriasCentral($soloActivas = false) {
        
        $respuesta = ModeloCategoriasCentral::mdlObtenerCategoriasCentral($soloActivas);
        
        return $respuesta;
    }

    /*=============================================
    OBTENER CATEGORÍA ESPECÍFICA
    =============================================*/
    static public function ctrObtenerCategoriaCentral($id) {
        
        $respuesta = ModeloCategoriasCentral::mdlObtenerCategoriaCentral($id);
        
        return $respuesta;
    }
}
?>
