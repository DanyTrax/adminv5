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
IMPORTAR DESDE EXCEL - ACTUALIZADO PARA CSV
=============================================*/

public function ctrImportarDesdeExcel() {
    
    error_log("=== INICIO IMPORTACION ===");
    
    if(isset($_POST["importarExcel"])) {
        
        error_log("POST importarExcel detectado");
        
        if(!empty($_FILES["archivoExcel"]["tmp_name"])) {
            
            $archivo = $_FILES["archivoExcel"]["tmp_name"];
            $nombreArchivo = $_FILES["archivoExcel"]["name"];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            
            error_log("Archivo: {$nombreArchivo} - Extensión: {$extension}");
            
            try {
                
                $contenido = file_get_contents($archivo);
                error_log("Contenido leído: " . strlen($contenido) . " bytes");
                error_log("Primeros 200 chars: " . substr($contenido, 0, 200));
                
                $productos = [];
                
                // ✅ DETECTAR Y PROCESAR SEGÚN FORMATO
                if($extension === 'csv' || strpos($contenido, ',') !== false) {
                    
                    // ✅ FORMATO CSV
                    error_log("Procesando como CSV");
                    
                    $lineas = str_getcsv($contenido, "\n");
                    $encabezados = [];
                    $primeraLinea = true;
                    
                    foreach($lineas as $indice => $linea) {
                        
                        if(empty(trim($linea))) continue;
                        
                        if($primeraLinea) {
                            $encabezados = str_getcsv($linea, ',');
                            $encabezados = array_map('trim', $encabezados);
                            error_log("Encabezados CSV: " . implode(" | ", $encabezados));
                            $primeraLinea = false;
                            continue;
                        }
                        
                        $datos = str_getcsv($linea, ',');
                        $datos = array_map('trim', $datos);
                        
                        if(count($datos) >= count($encabezados) && !empty($datos[0])) {
                            $producto = array_combine($encabezados, array_slice($datos, 0, count($encabezados)));
                            $productos[] = $producto;
                            
                            if(count($productos) <= 3) {
                                error_log("Producto CSV " . count($productos) . ": ID=" . ($producto['ID'] ?? 'N/A'));
                            }
                        }
                    }
                    
                } elseif(strpos($contenido, '<table') !== false) {
                    
                    // ✅ FORMATO HTML (Archivo original del exportador)
                    error_log("Procesando como HTML");
                    
                    preg_match('/<table[^>]*>(.*?)<\/table>/is', $contenido, $matches);
                    
                    if(isset($matches[1])) {
                        
                        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $matches[1], $filas);
                        
                        $encabezados = [];
                        $primeraFila = true;
                        
                        foreach($filas[1] as $indice => $filaContenido) {
                            
                            preg_match_all('/<t[hd][^>]*>(.*?)<\/t[hd]>/is', $filaContenido, $celdas);
                            
                            if($primeraFila) {
                                foreach($celdas[1] as $celda) {
                                    $encabezados[] = trim(strip_tags($celda));
                                }
                                error_log("Encabezados HTML: " . implode(" | ", $encabezados));
                                $primeraFila = false;
                                continue;
                            }
                            
                            if(count($celdas[1]) >= 3) {
                                $datosLimpios = [];
                                foreach($celdas[1] as $celda) {
                                    $datosLimpios[] = trim(strip_tags($celda));
                                }
                                
                                if(count($datosLimpios) >= count($encabezados) && !empty($datosLimpios[0])) {
                                    $producto = array_combine($encabezados, array_slice($datosLimpios, 0, count($encabezados)));
                                    $productos[] = $producto;
                                }
                            }
                        }
                    }
                    
                } else {
                    
                    // ✅ FORMATO TEXTO (Excel guardado como texto)
                    error_log("Procesando como texto delimitado");
                    
                    $lineas = explode("\n", $contenido);
                    $encabezados = [];
                    $primeraLinea = true;
                    
                    foreach($lineas as $linea) {
                        
                        $linea = trim($linea);
                        if(empty($linea)) continue;
                        
                        // Detectar delimitador
                        $delimitador = (strpos($linea, "\t") !== false) ? "\t" : ";";
                        
                        if($primeraLinea) {
                            $encabezados = explode($delimitador, $linea);
                            $encabezados = array_map('trim', $encabezados);
                            error_log("Encabezados TEXTO: " . implode(" | ", $encabezados));
                            $primeraLinea = false;
                            continue;
                        }
                        
                        $datos = explode($delimitador, $linea);
                        $datos = array_map('trim', $datos);
                        
                        if(count($datos) >= count($encabezados) && !empty($datos[0])) {
                            $producto = array_combine($encabezados, array_slice($datos, 0, count($encabezados)));
                            $productos[] = $producto;
                        }
                    }
                }
                
                error_log("Total productos extraídos: " . count($productos));
                
                if(empty($productos)) {
                    echo '<script>
                        swal({
                            type: "warning",
                            title: "Sin datos válidos",
                            text: "No se encontraron productos válidos en el archivo"
                        });
                    </script>';
                    return;
                }
                
                // ✅ ACTUALIZAR PRODUCTOS
                $productosActualizados = 0;
                $productosErrores = 0;
                
                foreach($productos as $indice => $producto) {
                    
                    try {
                        
                        $id = $producto['ID'] ?? '';
                        $descripcion = $producto['DESCRIPCION'] ?? '';
                        
                        if(empty($id)) {
                            $productosErrores++;
                            error_log("Error fila " . ($indice + 2) . ": ID vacío");
                            continue;
                        }
                        
                        $datos = array(
                            "id" => $id,
                            "descripcion" => $descripcion,
                            "id_categoria" => $producto['ID_CATEGORIA'] ?? 1,
                            "precio_venta" => $producto['PRECIO_VENTA'] ?? 0,
                            "imagen" => "vistas/img/productos/default/anonymous.png",
                            "es_divisible" => (($producto['ES_DIVISIBLE'] ?? '') === 'SI') ? 1 : 0,
                            "codigo_hijo_mitad" => $producto['CODIGO_HIJO_MITAD'] ?? '',
                            "codigo_hijo_tercio" => $producto['CODIGO_HIJO_TERCIO'] ?? '',
                            "codigo_hijo_cuarto" => $producto['CODIGO_HIJO_CUARTO'] ?? ''
                        );
                        
                        error_log("Actualizando ID {$id}: {$descripcion}");
                        
                        $respuesta = ModeloCatalogoMaestro::mdlEditarProductoMaestro($datos);
                        
                        if($respuesta === "ok") {
                            $productosActualizados++;
                        } else {
                            $productosErrores++;
                            error_log("Error actualizando ID {$id}: {$respuesta}");
                        }
                        
                    } catch(Exception $e) {
                        $productosErrores++;
                        error_log("Excepción en producto {$indice}: " . $e->getMessage());
                    }
                }
                
                error_log("RESULTADO: Actualizados={$productosActualizados}, Errores={$productosErrores}");
                
                $mensaje = "Productos actualizados: {$productosActualizados}";
                if($productosErrores > 0) {
                    $mensaje .= "\\nErrores: {$productosErrores}";
                }
                
                if($productosActualizados > 0) {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "Importación exitosa",
                            text: "' . $mensaje . '"
                        }).then(function() {
                            window.location = "catalogo-maestro";
                        });
                    </script>';
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Sin actualizaciones",
                            text: "' . $mensaje . '\\n\\nRevise los logs para más detalles."
                        });
                    </script>';
                }
                
            } catch(Exception $e) {
                error_log("Error crítico: " . $e->getMessage());
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error crítico",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
            }
            
        } else {
            echo '<script>
                swal({
                    type: "error",
                    title: "Sin archivo",
                    text: "Debe seleccionar un archivo"
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