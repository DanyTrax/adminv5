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
                    "es_principal" => isset($_POST["esPrincipal"]) ? 1 : 0,
                    "activo" => 1
                );

                $respuesta = ModeloSucursales::mdlConfigurarSucursalLocal($tabla, $datos);

                if ($respuesta == "ok") {
                    
                    // Actualizar config.php
                    self::actualizarConfigPHP($_POST["codigoLocal"]);
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Configuración guardada!",
                            text: "Los datos de esta sucursal han sido configurados correctamente.",
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
        
        if (isset($_POST["editarId"])) {
            
            if (preg_match('/^[a-zA-Z0-9ÁÉÍÓÚáéíóúñÑ ]+$/', $_POST["editarNombre"])) {
                
                $datos = array(
                    "id" => $_POST["editarId"],
                    "nombre" => $_POST["editarNombre"],
                    "direccion" => $_POST["editarDireccion"],
                    "telefono" => $_POST["editarTelefono"],
                    "email" => $_POST["editarEmail"],
                    "url_base" => $_POST["editarUrlBase"],
                    "url_api" => $_POST["editarUrlApi"],
                    "activo" => isset($_POST["editarActivo"]) ? 1 : 0
                );

                $respuesta = ModeloSucursales::mdlActualizarSucursalCentral($datos);

                if ($respuesta["success"]) {
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Sucursal actualizada!",
                            text: "Los datos han sido actualizados correctamente.",
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
}

?>