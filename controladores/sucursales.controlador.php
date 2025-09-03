<?php

class ControladorSucursales {

    /*=============================================
    MOSTRAR VISTA PRINCIPAL DE SUCURSALES
    =============================================*/
    static public function ctrMostrarSucursales() {
        if ($_SESSION["perfil"] == "Administrador") {
            include "vistas/modulos/sucursales.php";
        } else {
            include "vistas/modulos/404.php";
        }
    }

    /*=============================================
    CREAR NUEVA SUCURSAL
    =============================================*/
    static public function ctrCrearSucursal() {
        if (isset($_POST["nuevoCodigo"])) {
            // (Tu lógica de validación y creación aquí...)
            // Esta parte se mantiene igual a como la tenías,
            // ya que maneja la respuesta del formulario con SweetAlert.
        }
    }

    /*=============================================
    ACTUALIZAR SUCURSAL
    =============================================*/
    static public function ctrActualizarSucursal() {
        if (isset($_POST["editarId"])) {
            // (Tu lógica de validación y actualización aquí...)
            // Esta parte se mantiene igual a como la tenías.
        }
    }

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    static public function ctrEliminarSucursal() {
        if (isset($_GET["idSucursal"])) {
            // (Tu lógica de eliminación aquí...)
            // Esta parte se mantiene igual a como la tenías.
        }
    }
}