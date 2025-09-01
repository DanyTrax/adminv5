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
        
        $respuesta = ModeloCatalogoMaestro::mdlIngresarProductoMaestro($datos);
        
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
        
        // ✅ MANEJAR DIVISIÓN CON LIMPIEZA DE CAMPOS BORRADOS
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
            
            // Convertir strings vacíos a cadena vacía definitivamente
            $codigoHijoMitad = ($codigoHijoMitad === "") ? "" : $codigoHijoMitad;
            $codigoHijoTercio = ($codigoHijoTercio === "") ? "" : $codigoHijoTercio;
            $codigoHijoCuarto = ($codigoHijoCuarto === "") ? "" : $codigoHijoCuarto;
        }
        
        // Debug para verificar qué se está guardando
        error_log("=== GUARDANDO PRODUCTO ===");
        error_log("Es divisible: " . $esDivisible);
        error_log("Código mitad: '" . $codigoHijoMitad . "'");
        error_log("Código tercio: '" . $codigoHijoTercio . "'");
        error_log("Código cuarto: '" . $codigoHijoCuarto . "'");
        
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
                
                // Validar extensión
                if(!in_array(strtolower($extension), ['xls', 'xlsx', 'csv'])) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Formato no válido",
                            text: "Solo se permiten archivos Excel (.xls, .xlsx) o CSV"
                        });
                    </script>';
                    return;
                }
                
                // Leer archivo Excel
                if($extension == 'csv') {
                    $productos = $this->leerArchivoCSV($archivo);
                } else {
                    $productos = $this->leerArchivoExcel($archivo);
                }
                
                if(empty($productos)) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Archivo vacío",
                            text: "No se encontraron productos válidos en el archivo"
                        });
                    </script>';
                    return;
                }
                
                // Procesar productos
                $actualizados = 0;
                $errores = 0;
                $erroresDetalle = [];
                
                foreach($productos as $fila => $producto) {
                    
                    // Validar campos requeridos
                    if(empty($producto['id']) || empty($producto['descripcion'])) {
                        $errores++;
                        $erroresDetalle[] = "Fila $fila: ID o descripción vacíos";
                        continue;
                    }
                    
                    // Preparar datos para actualización
                    $datos = array(
                        "id" => $producto['id'],
                        "descripcion" => trim($producto['descripcion']),
                        "id_categoria" => (int)$producto['id_categoria'],
                        "precio_venta" => (float)str_replace(['.', ','], ['', '.'], $producto['precio_venta']),
                        "es_divisible" => (strtoupper(trim($producto['es_divisible'])) === 'SI') ? 1 : 0,
                        "codigo_hijo_mitad" => trim($producto['codigo_hijo_mitad'] ?? ''),
                        "codigo_hijo_tercio" => trim($producto['codigo_hijo_tercio'] ?? ''),
                        "codigo_hijo_cuarto" => trim($producto['codigo_hijo_cuarto'] ?? '')
                    );
                    
                    // Actualizar producto
                    $resultado = ModeloCatalogoMaestro::mdlActualizarProductoMaestro($datos);
                    
                    if($resultado) {
                        $actualizados++;
                    } else {
                        $errores++;
                        $erroresDetalle[] = "Fila $fila: Error al actualizar producto ID " . $producto['id'];
                    }
                }
                
                // Mostrar resultado
                if($actualizados > 0) {
                    $mensaje = "$actualizados productos actualizados correctamente";
                    if($errores > 0) {
                        $mensaje .= " ($errores errores encontrados)";
                    }
                    
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
                            title: "Error en importación",
                            text: "No se pudo actualizar ningún producto. Verifique el formato del archivo."
                        });
                    </script>';
                }
                
            } catch (Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al procesar archivo",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
            }
            
        } else {
            echo '<script>
                swal({
                    type: "error",
                    title: "No se seleccionó archivo",
                    text: "Por favor seleccione un archivo Excel para importar"
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