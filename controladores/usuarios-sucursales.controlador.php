<?php

require_once "../modelos/usuarios.modelo.php";
require_once "../modelos/sucursales.modelo.php";

class ControladorUsuariosSucursales {

    /*=============================================
    CREAR USUARIO CON SUCURSAL
    =============================================*/
    static public function ctrCrearUsuarioConSucursal() {
        
        if (isset($_POST["nuevoUsuario"])) {
            
            // Validaciones básicas
            if (
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])
            ) {
                
                // Validar que se seleccionó una sucursal
                if (empty($_POST["nuevaSucursal"])) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "Debe seleccionar una sucursal para el usuario",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
                
                // Encriptar contraseña
                $encriptar = crypt($_POST["nuevoPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                
                // Manejar foto
                $ruta = "vistas/img/usuarios/default/anonymous.png";
                
                if (isset($_FILES["nuevaFoto"]["tmp_name"]) && $_FILES["nuevaFoto"]["tmp_name"] !== '') {
                    
                    list($ancho, $alto) = getimagesize($_FILES["nuevaFoto"]["tmp_name"]);
                    
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;
                    
                    $directorio = "vistas/img/usuarios/".$_POST["nuevoUsuario"];
                    
                    mkdir($directorio, 0755);
                    
                    if ($_FILES["nuevaFoto"]["type"] == "image/jpeg") {
                        
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/".$_POST["nuevoUsuario"]."/".$aleatorio.".jpg";
                        
                        $origen = imagecreatefromjpeg($_FILES["nuevaFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                        
                    }
                    
                    if ($_FILES["nuevaFoto"]["type"] == "image/png") {
                        
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/".$_POST["nuevoUsuario"]."/".$aleatorio.".png";
                        
                        $origen = imagecreatefrompng($_FILES["nuevaFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        
                        imagealphablending($destino, FALSE);
                        imagesavealpha($destino, TRUE);
                        
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                        
                    }
                }
                
                // Determinar si es transportador
                $esTransportador = ($_POST["nuevoPerfil"] == "Transportador") ? 1 : 0;
                
                // Preparar sucursales permitidas para transportadores
                $sucursalesPermitidas = null;
                if ($esTransportador && isset($_POST["sucursalesPermitidas"])) {
                    $sucursalesPermitidas = json_encode($_POST["sucursalesPermitidas"]);
                }
                
                // Datos del usuario
                $datos = array(
                    "nombre" => $_POST["nuevoNombre"],
                    "usuario" => $_POST["nuevoUsuario"],
                    "password" => $encriptar,
                    "perfil" => $_POST["nuevoPerfil"],
                    "foto" => $ruta,
                    "sucursal_id" => $_POST["nuevaSucursal"],
                    "es_transportador" => $esTransportador,
                    "sucursales_permitidas" => $sucursalesPermitidas,
                    "telefono" => $_POST["nuevoTelefono"] ?? "",
                    "direccion" => $_POST["nuevaDireccion"] ?? ""
                );
                
                // Crear usuario
                $respuesta = ModeloUsuarios::mdlCrearUsuarioConSucursal("usuarios", $datos);
                
                if ($respuesta == "ok") {
                    
                    // Si es transportador, crear relaciones con sucursales
                    if ($esTransportador && isset($_POST["sucursalesPermitidas"])) {
                        foreach ($_POST["sucursalesPermitidas"] as $sucursalId) {
                            ModeloUsuarios::mdlAsignarSucursalUsuario($_POST["nuevoUsuario"], $sucursalId);
                        }
                    }
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡El usuario ha sido guardado correctamente!",
                            text: "Usuario creado con sucursal asignada",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "usuarios";
                            }
                        });
                    </script>';
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "No se pudo crear el usuario",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "Los datos contienen caracteres no permitidos",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DISPONIBLES
    =============================================*/
    static public function ctrObtenerSucursalesDisponibles() {
        
        try {
            // Obtener sucursales de la BD central
            $respuesta = ModeloSucursales::mdlObtenerSucursales(true); // Solo activas
            
            if ($respuesta['success']) {
                return $respuesta['data'];
            } else {
                return [];
            }
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerSucursalesDisponibles: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER USUARIOS POR SUCURSAL
    =============================================*/
    static public function ctrObtenerUsuariosPorSucursal($sucursalId = null) {
        
        try {
            $tabla = "usuarios";
            $item = $sucursalId ? "sucursal_id" : null;
            $valor = $sucursalId;
            
            $usuarios = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);
            
            return $usuarios ?: [];
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerUsuariosPorSucursal: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    VALIDAR ACCESO A SUCURSAL
    =============================================*/
    static public function ctrValidarAccesoSucursal($usuarioId, $sucursalId) {
        
        try {
            // Obtener datos del usuario
            $usuario = ModeloUsuarios::mdlMostrarUsuarios("usuarios", "id", $usuarioId);
            
            if (!$usuario) {
                return false;
            }
            
            // Si es transportador, verificar sucursales permitidas
            if ($usuario['es_transportador']) {
                $sucursalesPermitidas = json_decode($usuario['sucursales_permitidas'], true);
                return in_array($sucursalId, $sucursalesPermitidas ?: []);
            }
            
            // Si no es transportador, solo puede acceder a su sucursal
            return $usuario['sucursal_id'] == $sucursalId;
            
        } catch (Exception $e) {
            error_log("Error en ctrValidarAccesoSucursal: " . $e->getMessage());
            return false;
        }
    }
    
    /*=============================================
    OBTENER SUCURSAL DEL USUARIO ACTUAL
    =============================================*/
    static public function ctrObtenerSucursalUsuario($usuarioId) {
        
        try {
            $usuario = ModeloUsuarios::mdlMostrarUsuarios("usuarios", "id", $usuarioId);
            
            if ($usuario && $usuario['sucursal_id']) {
                // Obtener datos de la sucursal desde BD central
                $sucursales = ModeloSucursales::mdlObtenerSucursales();
                
                if ($sucursales['success']) {
                    foreach ($sucursales['data'] as $sucursal) {
                        if ($sucursal['id'] == $usuario['sucursal_id']) {
                            return $sucursal;
                        }
                    }
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerSucursalUsuario: " . $e->getMessage());
            return null;
        }
    }
}
?>
