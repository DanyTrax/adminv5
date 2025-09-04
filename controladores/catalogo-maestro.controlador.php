<?php

class ControladorCatalogoMaestro {
    
    /*=============================================
    MOSTRAR CATÁLOGO MAESTRO
    =============================================*/
    static public function ctrMostrarCatalogoMaestro($item, $valor) {
        $respuesta = ModeloCatalogoMaestro::mdlMostrarCatalogoMaestro($item, $valor);
        return $respuesta;
    }
    
/*=============================================
CREAR PRODUCTO MAESTRO
=============================================*/

public function ctrCrearProductoMaestro() {
    
    if(isset($_POST["nuevaDescripcionMaestro"])) {
        
        // Validar imagen si se sube
        if(isset($_FILES["nuevaImagenMaestro"]["tmp_name"]) && !empty($_FILES["nuevaImagenMaestro"]["tmp_name"])) {
            
            list($ancho, $alto) = getimagesize($_FILES["nuevaImagenMaestro"]["tmp_name"]);
            $nuevoAncho = 500;
            $nuevoAlto = 500;
            
            // Crear directorio si no existe
            $directorio = "vistas/img/productos/".$_POST["nuevoCodigoMaestro"];
            
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            // Procesar según el tipo de imagen
            if($_FILES["nuevaImagenMaestro"]["type"] == "image/jpeg") {
                $aleatorio = mt_rand(100,999);
                $ruta = "vistas/img/productos/".$_POST["nuevoCodigoMaestro"]."/".$aleatorio.".jpg";
                $origen = imagecreatefromjpeg($_FILES["nuevaImagenMaestro"]["tmp_name"]);
                $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                imagejpeg($destino, $ruta);
            }
            
            if($_FILES["nuevaImagenMaestro"]["type"] == "image/png") {
                $aleatorio = mt_rand(100,999);
                $ruta = "vistas/img/productos/".$_POST["nuevoCodigoMaestro"]."/".$aleatorio.".png";
                $origen = imagecreatefrompng($_FILES["nuevaImagenMaestro"]["tmp_name"]);
                $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                imagepng($destino, $ruta);
            }
            
        } else {
            $ruta = "vistas/img/productos/default/anonymous.png";
        }
        
        // Preparar datos
        $datos = array(
            "codigo" => $_POST["nuevoCodigoMaestro"],
            "descripcion" => $_POST["nuevaDescripcionMaestro"],
            "id_categoria" => $_POST["nuevaCategoriaMaestro"],
            "precio_venta" => $_POST["nuevoPrecioVentaMaestro"],
            "imagen" => $ruta,
            "es_divisible" => isset($_POST["esDivisibleMaestro"]) ? 1 : 0,
            "codigo_hijo_mitad" => $_POST["codigoHijoMitad"] ?? "",
            "codigo_hijo_tercio" => $_POST["codigoHijoTercio"] ?? "",
            "codigo_hijo_cuarto" => $_POST["codigoHijoCuarto"] ?? ""
        );
        
        $respuesta = ModeloCatalogoMaestro::mdlCrearProductoMaestro($datos);
        
        if($respuesta == "ok") {
            
            echo '<script>
                swal({
                    type: "success",
                    title: "Producto agregado",
                    text: "El producto ha sido guardado en el catálogo maestro"
                }).then(function() {
                    window.location = "catalogo-maestro";
                });
            </script>';
            
        } else {
            
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudo guardar el producto"
                });
            </script>';
        }
    }
}
    
/*=============================================
EDITAR PRODUCTO MAESTRO - CON LIMPIEZA DE CAMPOS BORRADOS
=============================================*/

public function ctrEditarProductoMaestro() {
    
    if(isset($_POST["editarDescripcionMaestro"])) {
        
        // Código existente de imagen...
        if(isset($_FILES["editarImagenMaestro"]["tmp_name"]) && !empty($_FILES["editarImagenMaestro"]["tmp_name"])) {
            // ... código de imagen existente
        } else {
            $ruta = $_POST["imagenActualMaestro"];
        }
        
        // ✅ MANEJAR DIVISIÓN CON DETECCIÓN DE CAMPOS BORRADOS
        $esDivisible = isset($_POST["editarEsDivisibleMaestro"]) ? 1 : 0;

        if($esDivisible == 0) {
            // Si NO es divisible, limpiar TODOS los campos
            $codigoHijoMitad = "";
            $codigoHijoTercio = "";
            $codigoHijoCuarto = "";
        } else {
            // Si ES divisible, procesar cada campo individualmente
            
            // ✅ LIMPIAR CAMPOS QUE ESTÁN VACÍOS O FUERON BORRADOS
            $codigoHijoMitad = isset($_POST["editarCodigoHijoMitad"]) ? trim($_POST["editarCodigoHijoMitad"]) : "";
            $codigoHijoTercio = isset($_POST["editarCodigoHijoTercio"]) ? trim($_POST["editarCodigoHijoTercio"]) : "";
            $codigoHijoCuarto = isset($_POST["editarCodigoHijoCuarto"]) ? trim($_POST["editarCodigoHijoCuarto"]) : "";
            
            // ✅ CONVERTIR MARCADORES DE CAMPO VACÍO A CADENA VACÍA
            if($codigoHijoMitad === "EMPTY_FIELD") $codigoHijoMitad = "";
            if($codigoHijoTercio === "EMPTY_FIELD") $codigoHijoTercio = "";
            if($codigoHijoCuarto === "EMPTY_FIELD") $codigoHijoCuarto = "";
        }

        // Debug mejorado
        error_log("=== GUARDANDO PRODUCTO ===");
        error_log("ID: " . $_POST["idProductoMaestro"]);
        error_log("Es divisible: " . $esDivisible);
        error_log("Código mitad: '" . $codigoHijoMitad . "' (length: " . strlen($codigoHijoMitad) . ")");
        error_log("Código tercio: '" . $codigoHijoTercio . "' (length: " . strlen($codigoHijoTercio) . ")");
        error_log("Código cuarto: '" . $codigoHijoCuarto . "' (length: " . strlen($codigoHijoCuarto) . ")");
        
        // Preparar datos
        $datos = array(
            "id" => $_POST["idProductoMaestro"],
            "descripcion" => $_POST["editarDescripcionMaestro"],
            "id_categoria" => $_POST["editarCategoriaMaestro"],
            "precio_venta" => $_POST["editarPrecioVentaMaestro"],
            "imagen" => $ruta,
            "es_divisible" => $esDivisible,
            "codigo_hijo_mitad" => $codigoHijoMitad,
            "codigo_hijo_tercio" => $codigoHijoTercio,
            "codigo_hijo_cuarto" => $codigoHijoCuarto
        );
        
        $respuesta = ModeloCatalogoMaestro::mdlEditarProductoMaestro($datos);
        
        if($respuesta == "ok") {
            
            echo '<script>
                swal({
                    type: "success",
                    title: "Producto actualizado",
                    text: "El producto ha sido actualizado correctamente"
                }).then(function() {
                    window.location = "catalogo-maestro";
                });
            </script>';
            
        } else {
            
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudo actualizar el producto"
                });
            </script>';
        }
    }
}

/*=============================================
SINCRONIZAR CATÁLOGO A PRODUCTOS LOCALES
=============================================*/
static public function ctrSincronizarCatalogo() {
    if(isset($_POST["sincronizarCatalogo"])) {
            
            $respuesta = ModeloCatalogoMaestro::mdlSincronizarAProductosLocales();
            
            if(is_array($respuesta)) {
                echo '<script>
                swal({
                      type: "success",
                      title: "¡Sincronización completada!",
                      text: "Productos sincronizados: '.$respuesta["sincronizados"].', Actualizados: '.$respuesta["actualizados"].'",
                      showConfirmButton: true,
                      confirmButtonText: "Cerrar"
                      }).then(function(result){
                                if (result.value) {
                                    window.location = "catalogo-maestro";
                                }
                            })
                </script>';
            } else {
                echo '<script>
                swal({
                      type: "error",
                      title: "¡Error en la sincronización!",
                      text: "'.$respuesta.'",
                      showConfirmButton: true,
                      confirmButtonText: "Cerrar"
                      }).then(function(result){
                                if (result.value) {
                                    window.location = "catalogo-maestro";
                                }
                            })
                </script>';
            }
        }
    }
    
/*=============================================
ELIMINAR PRODUCTO MAESTRO
=============================================*/

public function ctrEliminarProductoMaestro() {
    
    if(isset($_GET["idProductoMaestro"])) {
        
        $datos = $_GET["idProductoMaestro"];
        
        $respuesta = ModeloCatalogoMaestro::mdlEliminarProductoMaestro($datos);
        
        if($respuesta == "ok") {
            
            // Eliminar imagen si existe
            if(isset($_GET["imagen"]) && $_GET["imagen"] != "" && $_GET["imagen"] != "vistas/img/productos/default/anonymous.png") {
                if(file_exists($_GET["imagen"])) {
                    unlink($_GET["imagen"]);
                }
            }
            
            echo '<script>
                swal({
                    type: "success",
                    title: "Producto eliminado",
                    text: "El producto ha sido eliminado del catálogo maestro"
                }).then(function() {
                    window.location = "catalogo-maestro";
                });
            </script>';
            
        } else {
            
            echo '<script>
                swal({
                    type: "error", 
                    title: "Error",
                    text: "No se pudo eliminar el producto"
                }).then(function() {
                    window.location = "catalogo-maestro";
                });
            </script>';
        }
    }
}
    
    /*=============================================
    OBTENER SIGUIENTE CÓDIGO
    =============================================*/
    static public function ctrObtenerSiguienteCodigo() {
        $respuesta = ModeloCatalogoMaestro::mdlObtenerSiguienteCodigo();
        return $respuesta;
    }
    
    /*=============================================
    MOSTRAR CATEGORÍAS CENTRALES
    =============================================*/
    static public function ctrMostrarCategoriasCentrales() {
        $respuesta = ModeloCatalogoMaestro::mdlMostrarCategoriasCentrales();
        return $respuesta;
    }
    
/*=============================================
IMPORTAR DESDE EXCEL - CON CREACIÓN AUTOMÁTICA
=============================================*/

public function ctrImportarDesdeExcel() {
    
    error_log("=== INICIO IMPORTACION CON GENERACION AUTOMATICA ===");
    
    if(isset($_POST["importarExcel"])) {
        
        if(!empty($_FILES["archivoExcel"]["tmp_name"])) {
            
            $archivo = $_FILES["archivoExcel"]["tmp_name"];
            $nombreArchivo = $_FILES["archivoExcel"]["name"];
            
            error_log("Archivo: {$nombreArchivo}");
            
            try {
                
                $contenido = file_get_contents($archivo);
                
                // ✅ LEER PRODUCTOS DEL CSV
                $productos = [];
                $lineas = str_getcsv($contenido, "\n");
                $encabezados = [];
                $primeraLinea = true;
                
                foreach($lineas as $indice => $linea) {
                    
                    if(empty(trim($linea))) continue;
                    
                    if($primeraLinea) {
                        $encabezados = str_getcsv($linea, ',');
                        $encabezados = array_map('trim', $encabezados);
                        error_log("Encabezados: " . implode(" | ", $encabezados));
                        $primeraLinea = false;
                        continue;
                    }
                    
                    $datos = str_getcsv($linea, ',');
                    $datos = array_map('trim', $datos);
                    
                    if(count($datos) >= count($encabezados)) {
                        $producto = array_combine($encabezados, array_slice($datos, 0, count($encabezados)));
                        $productos[] = $producto;
                    }
                }
                
                error_log("Total productos leídos: " . count($productos));
                
                if(empty($productos)) {
                    echo '<script>swal({type: "error", title: "Sin datos", text: "No se encontraron productos válidos"});</script>';
                    return;
                }
                
                // ✅ PROCESAR PRODUCTOS CON LÓGICA INTELIGENTE
                $productosCreados = 0;
                $productosActualizados = 0;
                $productosErrores = 0;
                $erroresDetallados = [];
                
                foreach($productos as $indice => $producto) {
                    
                    try {
                        
                        $fila = $indice + 2;
                        
                        // ✅ VALIDACIÓN: CAMPOS OBLIGATORIOS
                        $descripcion = trim($producto['DESCRIPCION'] ?? '');
                        $id_categoria = trim($producto['ID_CATEGORIA'] ?? '');
                        $precio_venta = trim($producto['PRECIO_VENTA'] ?? '');
                        
                        // Verificar campos obligatorios
                        if(empty($descripcion)) {
                            $erroresDetallados[] = "Fila {$fila}: DESCRIPCION es obligatoria";
                            $productosErrores++;
                            continue;
                        }
                        
                        if(empty($id_categoria) || !is_numeric($id_categoria) || $id_categoria <= 0) {
                            $erroresDetallados[] = "Fila {$fila}: ID_CATEGORIA debe ser un número válido mayor a 0";
                            $productosErrores++;
                            continue;
                        }
                        
                        if(empty($precio_venta) || !is_numeric(str_replace([',', '.'], '', $precio_venta))) {
                            $erroresDetallados[] = "Fila {$fila}: PRECIO_VENTA debe ser un número válido";
                            $productosErrores++;
                            continue;
                        }
                        
                        // ✅ LIMPIAR Y PREPARAR DATOS
                        $precio_venta_limpio = floatval(str_replace([','], '', $precio_venta));
                        
                        $es_divisible_text = trim($producto['ES_DIVISIBLE'] ?? '');
                        $es_divisible = (strtoupper($es_divisible_text) === 'SI') ? 1 : 0;
                        
                        // Códigos hijos opcionales
                        $codigo_hijo_mitad = trim($producto['CODIGO_HIJO_MITAD'] ?? '');
                        $codigo_hijo_tercio = trim($producto['CODIGO_HIJO_TERCIO'] ?? '');
                        $codigo_hijo_cuarto = trim($producto['CODIGO_HIJO_CUARTO'] ?? '');
                        
                        // Limpiar valores "NULL" o vacíos
                        if(in_array(strtoupper($codigo_hijo_mitad), ['NULL', '', '0'])) $codigo_hijo_mitad = '';
                        if(in_array(strtoupper($codigo_hijo_tercio), ['NULL', '', '0'])) $codigo_hijo_tercio = '';
                        if(in_array(strtoupper($codigo_hijo_cuarto), ['NULL', '', '0'])) $codigo_hijo_cuarto = '';
                        
                        // ✅ VERIFICAR SI ES ACTUALIZACIÓN O CREACIÓN
                        $id_existente = trim($producto['ID'] ?? '');
                        $codigo_existente = trim($producto['CODIGO'] ?? '');
                        
                        $esActualizacion = false;
                        
                        // Si tiene ID válido, intentar actualizar
                        if(!empty($id_existente) && is_numeric($id_existente) && $id_existente > 0) {
                            
                            // Verificar que el ID existe en la base de datos
                            require_once "api-transferencias/conexion-central.php";
                            $db = ConexionCentral::conectar();
                            $stmtVerificar = $db->prepare("SELECT id FROM catalogo_maestro WHERE id = ? AND activo = 1");
                            $stmtVerificar->execute([$id_existente]);
                            
                            if($stmtVerificar->rowCount() > 0) {
                                $esActualizacion = true;
                            }
                        }
                        
                        if($esActualizacion) {
                            
                            // ✅ ACTUALIZAR PRODUCTO EXISTENTE
                            $datosActualizar = array(
                                "id" => intval($id_existente),
                                "descripcion" => $descripcion,
                                "id_categoria" => intval($id_categoria),
                                "precio_venta" => $precio_venta_limpio,
                                "imagen" => "vistas/img/productos/default/anonymous.png",
                                "es_divisible" => $es_divisible,
                                "codigo_hijo_mitad" => $codigo_hijo_mitad,
                                "codigo_hijo_tercio" => $codigo_hijo_tercio,
                                "codigo_hijo_cuarto" => $codigo_hijo_cuarto
                            );
                            
                            $respuesta = ModeloCatalogoMaestro::mdlEditarProductoMaestro($datosActualizar);
                            
                            if($respuesta === "ok") {
                                $productosActualizados++;
                                if($indice < 3) {
                                    error_log("ACTUALIZADO fila {$fila}: ID {$id_existente} - {$descripcion}");
                                }
                            } else {
                                $erroresDetallados[] = "Fila {$fila}: Error actualizando - {$respuesta}";
                                $productosErrores++;
                            }
                            
                        } else {
                            
                            // ✅ CREAR PRODUCTO NUEVO CON GENERACIÓN AUTOMÁTICA
                            $datosCrear = array(
                                "descripcion" => $descripcion,
                                "id_categoria" => intval($id_categoria),
                                "precio_venta" => $precio_venta_limpio,
                                "imagen" => "vistas/img/productos/default/anonymous.png",
                                "es_divisible" => $es_divisible,
                                "codigo_hijo_mitad" => $codigo_hijo_mitad,
                                "codigo_hijo_tercio" => $codigo_hijo_tercio,
                                "codigo_hijo_cuarto" => $codigo_hijo_cuarto
                            );
                            
                            $respuesta = ModeloCatalogoMaestro::mdlCrearProductoMaestroAutomatico($datosCrear);
                            
                            if($respuesta['status'] === "ok") {
                                $productosCreados++;
                                if($indice < 3) {
                                    error_log("CREADO fila {$fila}: Código {$respuesta['codigo']} - {$descripcion}");
                                }
                            } else {
                                $erroresDetallados[] = "Fila {$fila}: Error creando - " . ($respuesta['message'] ?? 'Error desconocido');
                                $productosErrores++;
                            }
                        }
                        
                    } catch(Exception $e) {
                        $erroresDetallados[] = "Fila {$fila}: Excepción - " . $e->getMessage();
                        $productosErrores++;
                        error_log("EXCEPCIÓN fila {$fila}: " . $e->getMessage());
                    }
                }
                
                // ✅ RESULTADO FINAL
                error_log("RESULTADO: Creados={$productosCreados}, Actualizados={$productosActualizados}, Errores={$productosErrores}");
                
                $totalProcesados = $productosCreados + $productosActualizados;
                $mensaje = "Productos procesados: {$totalProcesados}";
                
                if($productosCreados > 0) {
                    $mensaje .= "\\n• Nuevos creados: {$productosCreados}";
                }
                
                if($productosActualizados > 0) {
                    $mensaje .= "\\n• Actualizados: {$productosActualizados}";
                }
                
                if($productosErrores > 0) {
                    $mensaje .= "\\n• Errores: {$productosErrores}";
                    
                    if(count($erroresDetallados) > 0) {
                        $primerosErrores = array_slice($erroresDetallados, 0, 3);
                        $mensaje .= "\\n\\nPrimeros errores:\\n" . implode("\\n", $primerosErrores);
                        
                        if(count($erroresDetallados) > 3) {
                            $mensaje .= "\\n... y " . (count($erroresDetallados) - 3) . " errores más.";
                        }
                    }
                }
                
                $tipoAlerta = "success";
                if($totalProcesados === 0) {
                    $tipoAlerta = "error";
                } else if($productosErrores > 0) {
                    $tipoAlerta = "warning";
                }
                
                echo '<script>
                    swal({
                        type: "' . $tipoAlerta . '",
                        title: "Importación completada",
                        text: "' . $mensaje . '"
                    }).then(function() {
                        window.location = "catalogo-maestro";
                    });
                </script>';
                
            } catch(Exception $e) {
                error_log("Error crítico en importación: " . $e->getMessage());
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error crítico",
                        text: "Error del sistema. Revise los logs para más detalles."
                    });
                </script>';
            }
            
        } else {
            echo '<script>
                swal({
                    type: "error",
                    title: "Sin archivo",
                    text: "Debe seleccionar un archivo para importar"
                });
            </script>';
        }
        
    } else {
        error_log("POST importarExcel NO detectado");
    }
    
    error_log("=== FIN IMPORTACION ===");
}

/*=============================================
LEER ARCHIVO EXCEL (BÁSICO)
=============================================*/

private function leerArchivoExcel($archivo) {
    
    $productos = [];
    
    // Método básico para leer Excel como HTML (funciona para archivos generados por nuestro sistema)
    $contenido = file_get_contents($archivo);
    
    // Usar DOMDocument para parsear HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($contenido);
    
    $tablas = $dom->getElementsByTagName('table');
    
    if($tablas->length > 0) {
        $tabla = $tablas->item(0); // Primera tabla
        $filas = $tabla->getElementsByTagName('tr');
        
        // Saltar header (primera fila)
        for($i = 1; $i < $filas->length; $i++) {
            $fila = $filas->item($i);
            $celdas = $fila->getElementsByTagName('td');
            
            if($celdas->length >= 6) { // Mínimo de columnas requeridas
                
                $productos[] = [
                    'id' => trim($celdas->item(0)->textContent),
                    'codigo' => trim($celdas->item(1)->textContent),
                    'descripcion' => trim($celdas->item(2)->textContent),
                    'id_categoria' => trim($celdas->item(3)->textContent),
                    'precio_venta' => trim(str_replace(['.', ','], '', $celdas->item(5)->textContent)),
                    'es_divisible' => trim($celdas->item(6)->textContent),
                    'codigo_hijo_mitad' => trim($celdas->item(7)->textContent ?? ''),
                    'codigo_hijo_tercio' => trim($celdas->item(8)->textContent ?? ''),
                    'codigo_hijo_cuarto' => trim($celdas->item(9)->textContent ?? '')
                ];
            }
        }
    }
    
    return $productos;
}
    
    /*=============================================
    ACTIVAR/DESACTIVAR DIVISIÓN - ¡FUNCIÓN ADICIONAL!
    =============================================*/
    static public function ctrToggleDivisionProducto() {
        if(isset($_POST["toggleDivision"])) {
            
            $datos = array(
                "id" => $_POST["idProductoMaestro"],
                "es_divisible" => $_POST["estadoDivision"]
            );
            
            // Si se desactiva la división, limpiar los hijos
            if($_POST["estadoDivision"] == 0) {
                $datos["codigo_hijo_mitad"] = "";
                $datos["codigo_hijo_tercio"] = "";
                $datos["codigo_hijo_cuarto"] = "";
            }
            
            $respuesta = ModeloCatalogoMaestro::mdlEditarProductoMaestro($datos);
            
            echo json_encode($respuesta);
        }
    }
    
    /*=============================================
    BUSCAR PRODUCTOS PARA HIJOS - ¡FUNCIÓN AJAX!
    =============================================*/
    static public function ctrBuscarProductosParaHijos() {
        if(isset($_POST["termino"])) {
            $respuesta = ModeloCatalogoMaestro::mdlBuscarProductosParaHijos($_POST["termino"]);
            echo json_encode($respuesta);
        }
    }
}

?>