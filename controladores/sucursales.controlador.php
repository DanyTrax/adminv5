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
    CONFIGURAR SUCURSAL LOCAL
    =============================================*/
    static public function ctrConfigurarSucursalLocal() {
        
        if (isset($_POST["codigoLocal"])) {
            
            if (preg_match('/^[a-zA-Z0-9]{3,10}$/', $_POST["codigoLocal"]) &&
                preg_match('/^[a-zA-Z0-9ÁÉÍÓÚáéíóúñÑ ]+$/', $_POST["nombreLocal"])) {
                
                $tabla = "sucursal_local";
                
                $datos = array(
                    "codigo_sucursal" => $_POST["codigoLocal"],
                    "nombre" => $_POST["nombreLocal"],
                    "direccion" => $_POST["direccionLocal"],
                    "telefono" => $_POST["telefonoLocal"],
                    "email" => $_POST["emailLocal"],
                    "url_base" => $_POST["urlBaseLocal"],
                    "url_api" => $_POST["urlApiLocal"],
                    "usuario_bd" => $_POST["usuarioBdLocal"] ?? '',
                    "password_bd" => $_POST["passwordBdLocal"] ?? '',
                    "nombre_bd" => $_POST["nombreBdLocal"] ?? '',
                    "host_bd" => $_POST["hostBdLocal"] ?? 'localhost',
                    "puerto_bd" => $_POST["puertoBdLocal"] ?? 3306,
                    "es_principal" => isset($_POST["esPrincipal"]) ? 1 : 0,
                    "activo" => 1,
                    "registrada_en_central" => 0
                );

                $respuesta = ModeloSucursales::mdlConfigurarSucursalLocal($tabla, $datos);

                if ($respuesta && $respuesta['success']) {
                    
                    // Actualizar config.php
                    self::actualizarConfigPHP($_POST["codigoLocal"]);
                    
                    // Sincronizar con el sistema central
                    self::sincronizarConCentral($datos);
                    
                    // Actualizar campo empresa en usuarios locales
                    self::actualizarEmpresaUsuarios($datos['nombre']);
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Configuración guardada!",
                            text: "Los datos de esta sucursal han sido configurados y sincronizados correctamente.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "sucursales";
                            }
                        });
                    </script>';
                    
                } else {
                    
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "Error al guardar la configuración. Intente nuevamente.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error en los datos!",
                        text: "Verifique que el código y nombre sean válidos.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    REGISTRAR ESTA SUCURSAL EN BD CENTRAL
    =============================================*/
    static public function ctrRegistrarSucursal() {
        
        if (isset($_POST["accionRegistrar"]) && $_POST["accionRegistrar"] == "registrarEsta") {
            
            try {
                
                // Obtener datos locales
                $datosLocales = ModeloSucursales::mdlObtenerConfiguracionLocal();
                
                if (!$datosLocales) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "Primero debe configurar los datos de esta sucursal.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }

                // Generar consecutivo automático
                $siguienteCodigo = ModeloSucursales::mdlGenerarConsecutivoSucursal();
                
                // Preparar datos para BD central
                $datos = array(
                    "codigo_sucursal" => $datosLocales["codigo_sucursal"],
                    "nombre" => $datosLocales["nombre"],
                    "direccion" => $datosLocales["direccion"],
                    "telefono" => $datosLocales["telefono"],
                    "email" => $datosLocales["email"],
                    "url_base" => $datosLocales["url_base"],
                    "url_api" => $datosLocales["url_api"],
                    "es_principal" => $datosLocales["es_principal"],
                    "activo" => 1
                );

                $respuesta = ModeloSucursales::mdlCrearSucursalCentral($datos);

                if ($respuesta["success"]) {
                    
                    // Actualizar estado local
                    ModeloSucursales::mdlActualizarEstadoRegistro($datosLocales["id"], 1);
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Sucursal Registrada!",
                            text: "Esta sucursal ha sido agregada al directorio central correctamente.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "sucursales";
                            }
                        });
                    </script>';
                    
                } else {
                    
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error al registrar!",
                            text: "' . $respuesta["message"] . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
                
            } catch (Exception $e) {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "Error interno: ' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    ACTUALIZAR SUCURSAL EN BD CENTRAL
    =============================================*/
    static public function ctrActualizarSucursal() {
        
        // Debug temporal
        if (isset($_POST["editarId"])) {
            error_log("DEBUG ctrActualizarSucursal - POST recibido: " . print_r($_POST, true));
            
            if (preg_match('/^[a-zA-Z0-9ÁÉÍÓÚáéíóúñÑ ]+$/', $_POST["editarNombre"])) {
                
                $datos = array(
                    "id" => $_POST["editarId"],
                    "codigo_sucursal" => $_POST["editarCodigo"],
                    "nombre" => $_POST["editarNombre"],
                    "direccion" => $_POST["editarDireccion"],
                    "telefono" => $_POST["editarTelefono"],
                    "email" => $_POST["editarEmail"],
                    "url_base" => $_POST["editarUrlBase"] ?? '',
                    "url_api" => $_POST["editarUrlApi"] ?? '',
                    "usuario_bd" => $_POST["editarUsuarioBd"] ?? '',
                    "password_bd" => $_POST["editarPasswordBd"] ?? '',
                    "nombre_bd" => $_POST["editarNombreBd"] ?? '',
                    "host_bd" => $_POST["editarHostBd"] ?? 'localhost',
                    "puerto_bd" => $_POST["editarPuertoBd"] ?? 3306,
                    "activo" => isset($_POST["editarActivo"]) ? 1 : 0
                );

                $respuesta = ModeloSucursales::mdlActualizarSucursalCentral($datos);

                if ($respuesta["success"]) {
                    
                    // Sincronizar con configuración local si es la sucursal actual
                    $codigoActual = defined('CODIGO_SUCURSAL') ? CODIGO_SUCURSAL : '';
                    if ($datos["codigo_sucursal"] === $codigoActual) {
                        // Actualizar configuración local
                        $datosLocal = [
                            'codigo_sucursal' => $datos["codigo_sucursal"],
                            'nombre' => $datos["nombre"],
                            'direccion' => $datos["direccion"],
                            'telefono' => $datos["telefono"],
                            'email' => $datos["email"],
                            'usuario_bd' => $datos["usuario_bd"],
                            'password_bd' => $datos["password_bd"],
                            'nombre_bd' => $datos["nombre_bd"],
                            'host_bd' => $datos["host_bd"],
                            'puerto_bd' => $datos["puerto_bd"],
                            'activo' => $datos["activo"]
                        ];
                        
                        ModeloSucursales::mdlConfigurarSucursalLocal('sucursal_local', $datosLocal);
                    }
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Sucursal actualizada!",
                            text: "Los datos han sido actualizados correctamente y sincronizados con la configuración local.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "sucursales";
                            }
                        });
                    </script>';
                    
                } else {
                    
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "' . $respuesta["message"] . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error en los datos!",
                        text: "El nombre contiene caracteres no permitidos.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    static public function ctrEliminarSucursal() {
        
        if (isset($_GET["idSucursal"])) {
            
            $id = $_GET["idSucursal"];

            $respuesta = ModeloSucursales::mdlEliminarSucursalCentral($id);

            if ($respuesta["success"]) {
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Sucursal eliminada!",
                        text: "La sucursal ha sido eliminada del directorio.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
                
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "' . $respuesta["message"] . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    OBTENER CONFIGURACIÓN LOCAL PARA VISTA
    =============================================*/
    static public function ctrObtenerConfiguracionLocal() {
        return ModeloSucursales::mdlObtenerConfiguracionLocal();
    }

    /*=============================================
    VERIFICAR SI ESTÁ REGISTRADA EN BD CENTRAL
    =============================================*/
    static public function ctrVerificarRegistroEnCentral() {
        $datosLocales = ModeloSucursales::mdlObtenerConfiguracionLocal();
        
        if (!$datosLocales) {
            return ["registrada" => false, "configurada" => false];
        }

        $registrada = ModeloSucursales::mdlVerificarSucursalEnCentral($datosLocales["codigo_sucursal"]);
        
        return [
            "registrada" => $registrada["success"],
            "configurada" => true,
            "datos" => $datosLocales
        ];
    }

    /*=============================================
    SINCRONIZAR CATÁLOGO MAESTRO
    =============================================*/
    static public function ctrSincronizarCatalogoMaestro() {
        
        if (isset($_POST["accionSincronizar"]) && $_POST["accionSincronizar"] == "catalogo") {
            
            try {
                
                // 1. Obtener todas las sucursales activas
                $sucursales = ModeloSucursales::mdlObtenerSucursalesCentral(true);
                
                if (!$sucursales["success"] || empty($sucursales["data"])) {
                    echo '<script>
                        swal({
                            type: "warning",
                            title: "Sin sucursales",
                            text: "No hay sucursales registradas para sincronizar.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }

                // 2. Obtener catálogo maestro desde BD central
                require_once "modelos/catalogo-maestro.modelo.php";
                $catalogoMaestro = ModeloCatalogoMaestro::mdlObtenerCatalogoCompleto();

                // 3. Sincronizar con cada sucursal
                $resultados = ModeloSucursales::mdlSincronizarCatalogoConSucursales($catalogoMaestro, $sucursales["data"]);

                if ($resultados["success"]) {
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Sincronización completada!",
                            html: "' . $resultados["mensaje_detallado"] . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    
                } else {
                    
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error en sincronización",
                            text: "' . $resultados["message"] . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
                
            } catch (Exception $e) {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "Error interno: ' . $e->getMessage() . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    ACTUALIZAR CONFIG.PHP CON NUEVO CÓDIGO
    =============================================*/
    private static function actualizarConfigPHP($codigoSucursal) {
        
        try {
            
            $archivoConfig = 'config.php';
            
            if (!file_exists($archivoConfig)) {
                return false;
            }

            $contenido = file_get_contents($archivoConfig);
            
            // Actualizar o agregar CODIGO_SUCURSAL
            if (strpos($contenido, "define('CODIGO_SUCURSAL'") !== false) {
                // Reemplazar existente
                $contenido = preg_replace(
                    "/define\('CODIGO_SUCURSAL',\s*'[^']*'\);/",
                    "define('CODIGO_SUCURSAL', '$codigoSucursal');",
                    $contenido
                );
            } else {
                // Agregar nuevo
                $nuevaLinea = "\ndefine('CODIGO_SUCURSAL', '$codigoSucursal');\n";
                $contenido = str_replace('<?php', '<?php' . $nuevaLinea, $contenido);
            }

            // Actualizar o agregar NOMBRE_SUCURSAL si no existe
            if (strpos($contenido, "define('NOMBRE_SUCURSAL'") === false) {
                $nombreSucursal = ModeloSucursales::mdlObtenerNombrePorCodigo($codigoSucursal);
                $nuevaLinea = "define('NOMBRE_SUCURSAL', '$nombreSucursal');\n";
                $contenido = str_replace('<?php', '<?php' . "\n" . $nuevaLinea, $contenido);
            }

            return file_put_contents($archivoConfig, $contenido) !== false;
            
        } catch (Exception $e) {
            error_log("Error actualizando config.php: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    GENERAR CÓDIGO AUTOMÁTICO
    =============================================*/
    static public function ctrGenerarCodigoAutomatico() {
        return ModeloSucursales::mdlGenerarConsecutivoSucursal();
    }

    /*=============================================
    OBTENER TODAS LAS SUCURSALES PARA DATATABLE
    =============================================*/
    static public function ctrMostrarSucursal($item, $valor) {
        return ModeloSucursales::mdlMostrarSucursal($item, $valor);
    }

    /*=============================================
    ACTUALIZAR CAMPO EMPRESA EN USUARIOS LOCALES
    =============================================*/
    static public function actualizarEmpresaUsuarios($nombreSucursal) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar si existe la columna empresa
            $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios LIKE 'empresa'");
            $stmt->execute();
            $columnaEmpresa = $stmt->fetch();
            
            if ($columnaEmpresa) {
                // Actualizar campo empresa en todos los usuarios activos
                $stmt = $conexion->prepare("
                    UPDATE usuarios 
                    SET empresa = :nombre_sucursal 
                    WHERE activo = 1
                ");
                $stmt->bindParam(":nombre_sucursal", $nombreSucursal, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    $filasAfectadas = $stmt->rowCount();
                    error_log("Campo empresa actualizado en {$filasAfectadas} usuarios locales con: {$nombreSucursal}");
                    return true;
                } else {
                    error_log("Error actualizando campo empresa en usuarios locales");
                    return false;
                }
            } else {
                error_log("Columna 'empresa' no existe en tabla usuarios local");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error en actualizarEmpresaUsuarios: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    SINCRONIZAR CON SISTEMA CENTRAL
    =============================================*/
    static public function sincronizarConCentral($datos) {
        try {
            // Obtener sucursal central por código
            $sucursalesCentral = ModeloSucursales::mdlObtenerSucursales();
            $sucursalCentral = null;
            
            if ($sucursalesCentral && $sucursalesCentral['success']) {
                foreach ($sucursalesCentral['data'] as $sucursal) {
                    if ($sucursal['codigo_sucursal'] === $datos['codigo_sucursal']) {
                        $sucursalCentral = $sucursal;
                        break;
                    }
                }
            }
            
            if ($sucursalCentral) {
                // Preparar datos para sincronización
                $datosSincronizacion = [
                    'id' => $sucursalCentral['id'],
                    'nombre' => $datos['nombre'],
                    'direccion' => $datos['direccion'],
                    'telefono' => $datos['telefono'],
                    'email' => $datos['email'],
                    'url_base' => $datos['url_base'] ?? '',
                    'url_api' => $datos['url_api'] ?? '',
                    'activo' => $datos['activo']
                ];
                
                // Agregar campos de BD
                if (isset($datos['usuario_bd'])) {
                    $datosSincronizacion['usuario_bd'] = $datos['usuario_bd'];
                }
                if (isset($datos['password_bd'])) {
                    $datosSincronizacion['password_bd'] = $datos['password_bd'];
                }
                if (isset($datos['nombre_bd'])) {
                    $datosSincronizacion['nombre_bd'] = $datos['nombre_bd'];
                }
                if (isset($datos['host_bd'])) {
                    $datosSincronizacion['host_bd'] = $datos['host_bd'];
                }
                if (isset($datos['puerto_bd'])) {
                    $datosSincronizacion['puerto_bd'] = $datos['puerto_bd'];
                }
                
                // Ejecutar sincronización
                $resultado = ModeloSucursales::mdlActualizarSucursalCentral($datosSincronizacion);
                
                if ($resultado && $resultado['success']) {
                    error_log("Sincronización Local → Central exitosa para sucursal: " . $datos['codigo_sucursal']);
                    return true;
                } else {
                    error_log("Error en sincronización Local → Central: " . ($resultado['error'] ?? 'Error desconocido'));
                    return false;
                }
            } else {
                error_log("No se encontró sucursal central para sincronizar: " . $datos['codigo_sucursal']);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error en sincronizarConCentral: " . $e->getMessage());
            return false;
        }
    }
}

?>