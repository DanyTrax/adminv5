<?php

require_once __DIR__ . "/../modelos/usuarios-central.modelo.php";

class ControladorUsuariosCentral {

    /*=============================================
    MOSTRAR VISTA DE USUARIOS CENTRALES
    =============================================*/
    static public function ctrMostrarUsuariosCentral() {
        if ($_SESSION["perfil"] == "Administrador") {
            include "vistas/modulos/usuarios-central.php";
        } else {
            include "vistas/modulos/404.php";
        }
    }

    /*=============================================
    CREAR USUARIO EN BD CENTRAL
    =============================================*/
    static public function ctrCrearUsuarioCentral() {
        
        if (isset($_POST["nuevoUsuarioCentral"])) {
            
            // Validaciones básicas
            if (
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"]) &&
                !empty($_POST["nuevaSucursal"])
            ) {
                
                // Encriptar contraseña
                $encriptar = crypt($_POST["nuevoPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                
                // Manejar foto
                $ruta = "vistas/img/usuarios/default/anonymous.png";
                
                if (isset($_FILES["nuevaFoto"]["tmp_name"]) && $_FILES["nuevaFoto"]["tmp_name"] !== '') {
                    
                    list($ancho, $alto) = getimagesize($_FILES["nuevaFoto"]["tmp_name"]);
                    
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;
                    
                    $directorio = "vistas/img/usuarios/".$_POST["nuevoUsuario"];
                    
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0755, true);
                    }
                    
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
                
                // Datos del usuario
                $datos = array(
                    "nombre" => $_POST["nuevoNombre"],
                    "usuario" => $_POST["nuevoUsuario"],
                    "password" => $encriptar,
                    "perfil" => $_POST["nuevoPerfil"],
                    "foto" => $ruta,
                    "sucursal_id" => $_POST["nuevaSucursal"],
                    "telefono" => $_POST["nuevoTelefono"] ?? "",
                    "direccion" => $_POST["nuevaDireccion"] ?? "",
                    "observaciones" => $_POST["nuevasObservaciones"] ?? ""
                );
                
                // Crear usuario en BD central
                $respuesta = ModeloUsuariosCentral::mdlCrearUsuarioCentral("usuarios_central", $datos);
                
                if ($respuesta == "ok") {
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Usuario creado correctamente!",
                            text: "El usuario ha sido creado en la base de datos central",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "usuarios-central";
                            }
                        });
                    </script>';
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "No se pudo crear el usuario: ' . $respuesta . '",
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
                        text: "Los datos contienen caracteres no permitidos o están vacíos",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    SINCRONIZAR USUARIOS CON SUCURSALES
    =============================================*/
    static public function ctrSincronizarUsuarios() {
        
        if (isset($_POST["sincronizarUsuarios"])) {
            
            try {
                // Obtener usuarios pendientes de sincronización
                $usuariosPendientes = ModeloUsuariosCentral::mdlObtenerUsuariosPendientesSincronizacion();
                
                $sincronizados = 0;
                $errores = 0;
                $mensajes = [];
                
                foreach ($usuariosPendientes as $usuario) {
                    
                    // Obtener sucursales de destino
                    $sucursalesDestino = ModeloUsuariosCentral::mdlObtenerSucursalesDestino($usuario["sucursal_id"]);
                    
                    foreach ($sucursalesDestino as $sucursal) {
                        
                        $resultado = ModeloUsuariosCentral::mdlSincronizarUsuarioSucursal($usuario, $sucursal);
                        
                        if ($resultado["success"]) {
                            $sincronizados++;
                            $mensajes[] = "✅ Usuario '{$usuario['usuario']}' sincronizado en {$sucursal['nombre']}";
                        } else {
                            $errores++;
                            $mensajes[] = "❌ Error sincronizando '{$usuario['usuario']}' en {$sucursal['nombre']}: {$resultado['error']}";
                        }
                    }
                    
                    // Marcar usuario como sincronizado
                    ModeloUsuariosCentral::mdlMarcarUsuarioSincronizado($usuario["id"]);
                }
                
                $mensajeFinal = "Sincronización completada: $sincronizados exitosos, $errores errores";
                
                echo '<script>
                    swal({
                        type: "' . ($errores == 0 ? 'success' : 'warning') . '",
                        title: "Sincronización Completada",
                        text: "' . $mensajeFinal . '",
                        html: true,
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "usuarios-central";
                        }
                    });
                </script>';
                
            } catch (Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error en Sincronización",
                        text: "Error: ' . $e->getMessage() . '",
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
            $respuesta = ModeloUsuariosCentral::mdlObtenerSucursalesCentral();
            return $respuesta;
            
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
            $estadisticas = ModeloUsuariosCentral::mdlObtenerEstadisticasSincronizacion();
            return $estadisticas;
            
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasSincronizacion: " . $e->getMessage());
            return [
                'total_usuarios' => 0,
                'sincronizados' => 0,
                'pendientes' => 0,
                'errores' => 0
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
}
?>
