<?php

require_once __DIR__ . "/../modelos/usuarios-central.modelo.php";

class ControladorUsuariosCentral {

    /*=============================================
    MOSTRAR USUARIOS CENTRALES
    =============================================*/
    static public function ctrMostrarUsuariosCentral($item, $valor) {
        $tabla = "usuarios_central";
        return ModeloUsuariosCentral::mdlMostrarUsuariosCentral($tabla, $item, $valor);
    }

    /*=============================================
    OBTENER SUCURSALES DISPONIBLES
    =============================================*/
    static public function ctrObtenerSucursalesDisponibles() {
        try {
            return ModeloUsuariosCentral::mdlObtenerSucursalesCentral();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerSucursalesDisponibles: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE SINCRONIZACIÓN
    =============================================*/
    static public function ctrObtenerEstadisticasSincronizacion() {
        try {
            return ModeloUsuariosCentral::mdlObtenerEstadisticasSincronizacion();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasSincronizacion: " . $e->getMessage());
            return [
                'total_usuarios_central' => 0,
                'sucursales_activas' => 0,
                'sincronizaciones_hoy' => 0,
                'errores_hoy' => 0
            ];
        }
    }

    /*=============================================
    CONSULTAR USUARIOS DE SUCURSALES
    =============================================*/
    static public function ctrConsultarUsuariosSucursales($sucursalId = null) {
        return ModeloUsuariosCentral::mdlConsultarUsuariosSucursales($sucursalId);
    }

    /*=============================================
    OBTENER USUARIOS DE LA SUCURSAL LOCAL
    =============================================*/
    static public function ctrObtenerUsuariosLocal() {
        return ModeloUsuariosCentral::mdlObtenerUsuariosLocal();
    }

    /*=============================================
    OBTENER USUARIOS CENTRALES
    =============================================*/
    static public function ctrObtenerUsuariosCentral() {
        try {
            return ModeloUsuariosCentral::mdlObtenerUsuariosCentral();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerUsuariosCentral: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    CREAR USUARIO CENTRAL
    =============================================*/
    static public function ctrCrearUsuarioCentral($datos) {
        try {
            // Encriptar contraseña
            $datos['password'] = crypt($datos['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            
            // Agregar foto por defecto
            $datos['foto'] = "vistas/img/usuarios/default/anonymous.png";
            $datos['activo'] = 1;
            $datos['sincronizado'] = 0;
            
            return ModeloUsuariosCentral::mdlIngresarUsuarioCentral("usuarios_central", $datos);
        } catch (Exception $e) {
            error_log("Error en ctrCrearUsuarioCentral: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al crear usuario: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    SINCRONIZAR USUARIOS CENTRALES
    =============================================*/
    static public function ctrSincronizarUsuariosCentral() {
        try {
            return ModeloUsuariosCentral::mdlSincronizarUsuariosCentral();
        } catch (Exception $e) {
            error_log("Error en ctrSincronizarUsuariosCentral: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al sincronizar usuarios: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    IMPORTAR USUARIOS DE SUCURSALES
    =============================================*/
    static public function ctrImportarUsuariosSucursales() {
        try {
            return ModeloUsuariosCentral::mdlImportarUsuariosSucursales();
        } catch (Exception $e) {
            error_log("Error en ctrImportarUsuariosSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al importar usuarios: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    IMPORTAR USUARIO INDIVIDUAL
    =============================================*/
    static public function ctrImportarUsuarioIndividual($usuario) {
        try {
            return ModeloUsuariosCentral::mdlImportarUsuarioIndividual($usuario);
        } catch (Exception $e) {
            error_log("Error en ctrImportarUsuarioIndividual: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al importar usuario: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    EDITAR USUARIO CENTRAL
    =============================================*/
    static public function ctrEditarUsuarioCentral($datos) {
        try {
            return ModeloUsuariosCentral::mdlEditarUsuarioCentral($datos);
        } catch (Exception $e) {
            error_log("Error en ctrEditarUsuarioCentral: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al editar usuario: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ELIMINAR USUARIO CENTRAL
    =============================================*/
    static public function ctrEliminarUsuarioCentral($id) {
        try {
            return ModeloUsuariosCentral::mdlEliminarUsuarioCentral($id);
        } catch (Exception $e) {
            error_log("Error en ctrEliminarUsuarioCentral: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al eliminar usuario: ' . $e->getMessage()
            ];
        }
    }
    
    /*=============================================
    SINCRONIZAR USUARIOS A SUCURSALES
    =============================================*/
    static public function ctrSincronizarUsuariosSucursales($sucursales) {
        try {
            return ModeloUsuariosCentral::mdlSincronizarUsuariosSucursales($sucursales);
        } catch (Exception $e) {
            error_log("Error en ctrSincronizarUsuariosSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno del servidor'
            ];
        }
    }
}
?>
