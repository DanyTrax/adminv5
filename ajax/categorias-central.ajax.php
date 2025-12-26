<?php

require_once __DIR__ . "/../controladores/categorias-central.controlador.php";

class AjaxCategoriasCentral {

    public $idCategoria;

    /*=============================================
    CREAR CATEGORÍA CENTRAL
    =============================================*/
    public function ajaxCrearCategoriaCentral() {
        
        $respuesta = ControladorCategoriasCentral::ctrCrearCategoriaCentral();
        
        echo json_encode($respuesta);
    }

    /*=============================================
    EDITAR CATEGORÍA CENTRAL
    =============================================*/
    public function ajaxEditarCategoriaCentral() {
        
        $respuesta = ControladorCategoriasCentral::ctrEditarCategoriaCentral();
        
        echo json_encode($respuesta);
    }

    /*=============================================
    ELIMINAR CATEGORÍA CENTRAL
    =============================================*/
    public function ajaxEliminarCategoriaCentral() {
        
        $respuesta = ControladorCategoriasCentral::ctrEliminarCategoriaCentral();
        
        echo json_encode($respuesta);
    }

    /*=============================================
    SINCRONIZAR CATEGORÍAS CON SUCURSALES
    =============================================*/
    public function ajaxSincronizarCategoriasSucursales() {
        
        $respuesta = ControladorCategoriasCentral::ctrSincronizarCategoriasSucursales();
        
        echo json_encode($respuesta);
    }

    /*=============================================
    SINCRONIZAR BIDIRECCIONAL
    =============================================*/
    public function ajaxSincronizarBidireccional() {
        
        $respuesta = ControladorCategoriasCentral::ctrSincronizarBidireccional();
        
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER SUCURSALES ACTIVAS
    =============================================*/
    public function ajaxObtenerSucursalesActivas() {
        
        require_once __DIR__ . "/../modelos/sucursales.modelo.php";
        $respuesta = ModeloSucursales::mdlObtenerSucursales(true);
        
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER CATEGORÍAS CENTRALES
    =============================================*/
    public function ajaxObtenerCategoriasCentral() {
        
        $soloActivas = isset($_POST["soloActivas"]) ? (bool)$_POST["soloActivas"] : false;
        $respuesta = ControladorCategoriasCentral::ctrObtenerCategoriasCentral($soloActivas);
        
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER CATEGORÍA ESPECÍFICA
    =============================================*/
    public function ajaxObtenerCategoriaCentral() {
        
        if (!isset($this->idCategoria)) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de categoría requerido'
            ]);
            return;
        }
        
        $categoria = ControladorCategoriasCentral::ctrObtenerCategoriaCentral($this->idCategoria);
        
        if ($categoria) {
            echo json_encode([
                'success' => true,
                'data' => $categoria
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ]);
        }
    }
}

/*=============================================
CREAR CATEGORÍA CENTRAL
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "crear") {
    $ajax = new AjaxCategoriasCentral();
    $ajax->ajaxCrearCategoriaCentral();
}

/*=============================================
EDITAR CATEGORÍA CENTRAL
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "editar") {
    $ajax = new AjaxCategoriasCentral();
    $ajax->ajaxEditarCategoriaCentral();
}

/*=============================================
ELIMINAR CATEGORÍA CENTRAL
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "eliminar") {
    $ajax = new AjaxCategoriasCentral();
    $ajax->ajaxEliminarCategoriaCentral();
}

/*=============================================
SINCRONIZAR CATEGORÍAS CON SUCURSALES
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar") {
    $ajax = new AjaxCategoriasCentral();
    $ajax->ajaxSincronizarCategoriasSucursales();
}

/*=============================================
SINCRONIZAR BIDIRECCIONAL
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar_bidireccional") {
    $ajax = new AjaxCategoriasCentral();
    $ajax->ajaxSincronizarBidireccional();
}

/*=============================================
OBTENER SUCURSALES ACTIVAS
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_sucursales") {
    $ajax = new AjaxCategoriasCentral();
    $ajax->ajaxObtenerSucursalesActivas();
}

/*=============================================
OBTENER CATEGORÍAS CENTRALES
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "obtener") {
    $ajax = new AjaxCategoriasCentral();
    $ajax->ajaxObtenerCategoriasCentral();
}

/*=============================================
OBTENER CATEGORÍA ESPECÍFICA
=============================================*/
else if (isset($_POST["idCategoria"])) {
    $ajax = new AjaxCategoriasCentral();
    $ajax->idCategoria = $_POST["idCategoria"];
    $ajax->ajaxObtenerCategoriaCentral();
}
?>
