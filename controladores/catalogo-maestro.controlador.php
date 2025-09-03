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
IMPORTAR DESDE EXCEL - ACTUALIZAR PRODUCTOS EXISTENTES
=============================================*/

public function ctrImportarDesdeExcel() {
    
    if(isset($_POST["importarExcel"])) {
        
        if(!empty($_FILES["archivoExcel"]["tmp_name"])) {
            
            try {
                
                $archivo = $_FILES["archivoExcel"]["tmp_name"];
                $extension = pathinfo($_FILES["archivoExcel"]["name"], PATHINFO_EXTENSION);
                $nombreArchivo = $_FILES["archivoExcel"]["name"];
                
                error_log("IMPORTACIÓN EXCEL INICIADA - Archivo: " . $nombreArchivo);
                
                if(!in_array(strtolower($extension), ['xls', 'xlsx', 'csv'])) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Formato no válido",
                            text: "Solo se permiten archivos .xls, .xlsx o .csv"
                        });
                    </script>';
                    return;
                }
                
                $contenido = file_get_contents($archivo);
                
                if(empty($contenido)) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Archivo vacío",
                            text: "El archivo seleccionado está vacío"
                        });
                    </script>';
                    return;
                }
                
                $productos = [];
                
                if(strtolower($extension) === 'csv') {
                    
                    $lineas = str_getcsv($contenido, "\n");
                    $encabezados = [];
                    $primeraLinea = true;
                    
                    foreach($lineas as $indice => $linea) {
                        if($primeraLinea) {
                            $encabezados = str_getcsv($linea, ",");
                            $primeraLinea = false;
                            continue;
                        }
                        
                        if(!empty(trim($linea))) {
                            $datos = str_getcsv($linea, ",");
                            if(count($datos) >= count($encabezados)) {
                                $producto = array_combine($encabezados, $datos);
                                $productos[] = $producto;
                            }
                        }
                    }
                    
                } else {
                    
                    preg_match('/<table[^>]*>(.*?)<\/table>/is', $contenido, $matches);
                    
                    if(!isset($matches[1])) {
                        echo '<script>
                            swal({
                                type: "error",
                                title: "Formato no reconocido",
                                text: "No se pudo leer la estructura de la tabla en el archivo"
                            });
                        </script>';
                        return;
                    }
                    
                    $tablaContenido = $matches[1];
                    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tablaContenido, $filas);
                    
                    $encabezados = [];
                    $primeraFila = true;
                    
                    foreach($filas[1] as $indice => $filaContenido) {
                        
                        preg_match_all('/<t[hd][^>]*>(.*?)<\/t[hd]>/is', $filaContenido, $celdas);
                        
                        if($primeraFila) {
                            foreach($celdas[1] as $celda) {
                                $encabezados[] = trim(strip_tags($celda));
                            }
                            $primeraFila = false;
                            continue;
                        }
                        
                        if(count($celdas[1]) >= 3) {
                            
                            $datosLimpios = [];
                            foreach($celdas[1] as $celda) {
                                $valorLimpio = trim(strip_tags($celda));
                                if(is_numeric(str_replace(['.', ','], '', $valorLimpio))) {
                                    $valorLimpio = str_replace(['.', ','], '', $valorLimpio);
                                }
                                $datosLimpios[] = $valorLimpio;
                            }
                            
                            if(count($datosLimpios) >= count($encabezados)) {
                                $producto = array_combine($encabezados, $datosLimpios);
                                $productos[] = $producto;
                            }
                        }
                    }
                }
                
                error_log("Productos extraídos: " . count($productos));
                
                if(empty($productos)) {
                    echo '<script>
                        swal({
                            type: "warning",
                            title: "Archivo vacío",
                            text: "No se encontraron productos válidos en el archivo"
                        });
                    </script>';
                    return;
                }
                
                $productosActualizados = 0;
                $productosErrores = 0;
                $errores = [];
                
                foreach($productos as $indice => $producto) {
                    
                    try {
                        
                        if(empty($producto['ID']) || empty($producto['CODIGO'])) {
                            $errores[] = "Fila " . ($indice + 2) . ": ID o CODIGO vacío";
                            $productosErrores++;
                            continue;
                        }
                        
                        $datos = array(
                            "id" => $producto['ID'],
                            "descripcion" => $producto['DESCRIPCION'] ?? '',
                            "id_categoria" => $producto['ID_CATEGORIA'] ?? 1,
                            "precio_venta" => $producto['PRECIO_VENTA'] ?? 0,
                            "imagen" => "vistas/img/productos/default/anonymous.png",
                            "es_divisible" => (isset($producto['ES_DIVISIBLE']) && strtoupper($producto['ES_DIVISIBLE']) === 'SI') ? 1 : 0,
                            "codigo_hijo_mitad" => $producto['CODIGO_HIJO_MITAD'] ?? '',
                            "codigo_hijo_tercio" => $producto['CODIGO_HIJO_TERCIO'] ?? '',
                            "codigo_hijo_cuarto" => $producto['CODIGO_HIJO_CUARTO'] ?? ''
                        );
                        
                        $respuesta = ModeloCatalogoMaestro::mdlEditarProductoMaestro($datos);
                        
                        if($respuesta === "ok") {
                            $productosActualizados++;
                        } else {
                            $errores[] = "Error actualizando producto ID: " . $producto['ID'];
                            $productosErrores++;
                        }
                        
                    } catch(Exception $e) {
                        $errores[] = "Error en fila " . ($indice + 2) . ": " . $e->getMessage();
                        $productosErrores++;
                    }
                }
                
                $mensaje = "Productos actualizados: " . $productosActualizados;
                if($productosErrores > 0) {
                    $mensaje .= "\\nErrores: " . $productosErrores;
                }
                
                if($productosActualizados > 0) {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "Importación completada",
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
                            text: "' . $mensaje . '"
                        });
                    </script>';
                }
                
            } catch(Exception $e) {
                error_log("Error crítico en importación: " . $e->getMessage());
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
                    title: "Archivo no seleccionado",
                    text: "Debe seleccionar un archivo para importar"
                });
            </script>';
        }
    }
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