<?php

require_once __DIR__ . "/../controladores/categorias-central.controlador.php";

class AjaxCategoriasOriginal {

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
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxCrearCategoriaCentral();
}

/*=============================================
EDITAR CATEGORÍA CENTRAL
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "editar") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxEditarCategoriaCentral();
}

/*=============================================
ELIMINAR CATEGORÍA CENTRAL
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "eliminar") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxEliminarCategoriaCentral();
}

/*=============================================
SINCRONIZAR CATEGORÍAS CON SUCURSALES
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxSincronizarCategoriasSucursales();
}

/*=============================================
OBTENER CATEGORÍAS CENTRALES
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "obtener") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->ajaxObtenerCategoriasCentral();
}

/*=============================================
OBTENER CATEGORÍA ESPECÍFICA
=============================================*/
else if (isset($_POST["accion"]) && $_POST["accion"] == "obtenerCategoria") {
    $ajax = new AjaxCategoriasOriginal();
    $ajax->idCategoria = $_POST["idCategoria"];
    $ajax->ajaxObtenerCategoriaCentral();
}

/*=============================================
ACCIONES NO RECONOCIDAS
=============================================*/
else {
    echo json_encode([
        'success' => false,
        'message' => 'Acción no reconocida'
    ]);
}
