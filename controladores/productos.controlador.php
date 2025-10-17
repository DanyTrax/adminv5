<?php

class ControladorProductos {

    /*=============================================
    MOSTRAR PRODUCTOS
    =============================================*/
    static public function ctrMostrarProductos($item, $valor, $orden = "id") {
        return ModeloProductos::mdlMostrarProductos("productos", $item, $valor, $orden);
    }

    /*=============================================
    CREAR PRODUCTO (Tu código original)
    =============================================*/
    static public function ctrCrearProducto() {
        if (isset($_POST["nuevaDescripcion"])) {
                   // --- INICIO DE LA VALIDACIÓN ---
                    // 1. Validamos que el código no se repita
                    $itemCodigo = "codigo";
                    $valorCodigo = $_POST["nuevoCodigo"];
                    $codigoExistente = ModeloProductos::mdlMostrarProductos("productos", $itemCodigo, $valorCodigo, "id");
            
                    if ($codigoExistente) {
                        echo '<script>
                            swal({ type: "error", title: "¡Código Repetido!", text: "El código ingresado ya existe en la base de datos." });
                        </script>';
                        return; // Detenemos la ejecución
                    }
            
                    // 2. Validamos que la descripción no se repita
                    $itemDesc = "descripcion";
                    $valorDesc = $_POST["nuevaDescripcion"];
                    $descripcionExistente = ModeloProductos::mdlMostrarProductos("productos", $itemDesc, $valorDesc, "id");
                    
                    if ($descripcionExistente) {
                        echo '<script>
                            swal({ type: "error", title: "¡Descripción Repetida!", text: "La descripción ingresada ya existe en la base de datos." });
                        </script>';
                        return; // Detenemos la ejecución
                    }
            $tabla = "productos";
            $ruta = "vistas/img/productos/default/anonymous.png";
            if (isset($_FILES["nuevaImagen"]["tmp_name"]) && !empty($_FILES["nuevaImagen"]["tmp_name"])) {
                list($ancho, $alto) = getimagesize($_FILES["nuevaImagen"]["tmp_name"]);
                $nuevoAncho = 500; $nuevoAlto = 500;
                $directorio = "vistas/img/productos/" . $_POST["nuevoCodigo"];
                if(!is_dir($directorio)){ mkdir($directorio, 0755); }
                if ($_FILES["nuevaImagen"]["type"] == "image/jpeg") {
                    $aleatorio = mt_rand(100, 999);
                    $ruta = "vistas/img/productos/" . $_POST["nuevoCodigo"] . "/" . $aleatorio . ".jpg";
                    $origen = imagecreatefromjpeg($_FILES["nuevaImagen"]["tmp_name"]);
                    $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                    imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                    imagejpeg($destino, $ruta);
                }
                if ($_FILES["nuevaImagen"]["type"] == "image/png") {
                    $aleatorio = mt_rand(100, 999);
                    $ruta = "vistas/img/productos/" . $_POST["nuevoCodigo"] . "/" . $aleatorio . ".png";
                    $origen = imagecreatefrompng($_FILES["nuevaImagen"]["tmp_name"]);
                    $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                    imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                    imagepng($destino, $ruta);
                }
            }
            
            $esDivisible = isset($_POST["esDivisible"]) ? 1 : 0;
            $datos = [
                "id_categoria"  => $_POST["nuevaCategoria"], "codigo" => $_POST["nuevoCodigo"],
                "descripcion"   => $_POST["nuevaDescripcion"], "stock" => $_POST["nuevoStock"],
                "precio_compra" => 0, "precio_venta"  => $_POST["nuevoPrecioVenta"],
                "imagen" => $ruta, "ventas" => 0, "es_divisible" => $esDivisible,
                "nombre_mitad"  => $esDivisible && !empty($_POST["nombreMitad"]) ? $_POST["nombreMitad"] : "", 
                "precio_mitad"  => $esDivisible && !empty($_POST["precioMitad"]) ? $_POST["precioMitad"] : 0,
                "nombre_tercio" => $esDivisible && !empty($_POST["nombreTercio"]) ? $_POST["nombreTercio"] : "", 
                "precio_tercio" => $esDivisible && !empty($_POST["precioTercio"]) ? $_POST["precioTercio"] : 0,
                "nombre_cuarto" => $esDivisible && !empty($_POST["nombreCuarto"]) ? $_POST["nombreCuarto"] : "", 
                "precio_cuarto" => $esDivisible && !empty($_POST["precioCuarto"]) ? $_POST["precioCuarto"] : 0
            ];
            $respuesta = ModeloProductos::mdlIngresarProducto($tabla, $datos);
            if ($respuesta == "ok") {
                self::mostrarAlerta("success", "El producto ha sido guardado correctamente", "productos");
            } else {
                self::mostrarAlerta("error", "Error al guardar el producto", "productos");
            }
        }
    }

/*=============================================
EDITAR PRODUCTO (VERSIÓN FINAL SIMPLIFICADA)
=============================================*/
static public function ctrEditarProducto() {

    if (isset($_POST["idProducto"])) {

        $tabla = "productos";
        
        // Verificamos que el producto exista
        $productoActual = ModeloProductos::mdlMostrarProductos($tabla, "id", $_POST["idProducto"], "id");
        if(!$productoActual){
            self::mostrarAlerta("error", "Error: No se encontró el producto.");
            return;
        }
        
        $ruta = $_POST["imagenActual"];
        if (isset($_FILES["editarImagen"]["tmp_name"]) && !empty($_FILES["editarImagen"]["tmp_name"])) {
            // (Tu lógica para procesar la nueva imagen)
        }

        $esDivisible = isset($_POST["esDivisible"]) ? 1 : 0;
        
        // Preparamos todos los datos a guardar
        $datos = [
            "id" => $_POST["idProducto"],
            "id_categoria" => $_POST["editarCategoria"],
            "codigo" => $_POST["editarCodigo"],
            "descripcion" => $_POST["editarDescripcion"],
            "stock" => $_POST["editarStock"],
            "precio_venta" => $_POST["editarPrecioVenta"],
            "imagen" => $ruta,
            "es_divisible" => $esDivisible,
            "nombre_mitad" => $esDivisible ? $_POST["nombreMitad"] : "",
            "precio_mitad" => $esDivisible && !empty($_POST["precioMitad"]) ? $_POST["precioMitad"] : 0,
            "nombre_tercio" => $esDivisible ? $_POST["nombreTercio"] : "",
            "precio_tercio" => $esDivisible && !empty($_POST["precioTercio"]) ? $_POST["precioTercio"] : 0,
            "nombre_cuarto" => $esDivisible ? $_POST["nombreCuarto"] : "",
            "precio_cuarto" => $esDivisible && !empty($_POST["precioCuarto"]) ? $_POST["precioCuarto"] : 0
        ];

        // Llamamos al modelo para guardar los datos
        $respuesta = ModeloProductos::mdlEditarProducto($tabla, $datos);

        if ($respuesta == "ok") {
            self::mostrarAlerta("success", "El producto ha sido guardado correctamente", "productos");
        } else {
            self::mostrarAlerta("error", "Error al guardar el producto", "productos");
        }
    }
}
    /*=============================================
    ELIMINAR PRODUCTO (Tu función original)
    =============================================*/
    static public function ctrEliminarProducto() {
        if (isset($_GET["idProducto"])) {
            $tabla = "productos";
            $datos = $_GET["idProducto"];
            if (!empty($_GET["imagen"]) && $_GET["imagen"] !== "vistas/img/productos/default/anonymous.png") {
                @unlink($_GET["imagen"]);
                @rmdir("vistas/img/productos/" . $_GET["codigo"]);
            }
            $respuesta = ModeloProductos::mdlEliminarProducto($tabla, $datos);
            if ($respuesta === "ok") {
                self::mostrarAlerta("success", "¡Producto eliminado correctamente!", "productos");
            }
        }
    }

    /*=============================================
    DIVIDIR PRODUCTO (LLAMADO DESDE AJAX)
    =============================================*/
    static public function ctrDividirProductoAjax($idProducto, $tipoDivision){
        $tabla = "productos";
        $productoOriginal = ModeloProductos::mdlMostrarProductos($tabla, "id", $idProducto, "id");
        if(!$productoOriginal || !$productoOriginal["es_divisible"]){ echo "error_no_divisible"; return; }
        if((int)$productoOriginal["stock"] < 1){ echo "error_stock"; return; }
        
        $nombreParte = $productoOriginal["nombre_". $tipoDivision] ?? null;
        $precioParte = $productoOriginal["precio_". $tipoDivision] ?? null;
        
        if(empty($nombreParte) || empty($precioParte)){ echo "error_descripcion"; return; }

        $cantidades = ["mitad" => 2, "tercio" => 3, "cuarto" => 4];
        $cantidadResultante = $cantidades[$tipoDivision];
        
        $respuesta = ModeloProductos::mdlDividirProducto($idProducto, $nombreParte, $precioParte, $cantidadResultante);
        echo $respuesta;
    }

    /*=============================================
    HELPER PARA MOSTRAR ALERTAS
    =============================================*/
    private static function mostrarAlerta($tipo, $mensaje, $redir = "productos") {
       echo "<script>
           swal({
               type: '$tipo', title: '$mensaje',
               showConfirmButton: true, confirmButtonText: 'Cerrar'
           }).then(function(result){
               if (result.value) { window.location = '$redir'; }
           });
       </script>";
    }
    /*=============================================
    SUMAR EL TOTAL DE VENTAS PRODUCTOS
    =============================================*/
    static public function ctrMostrarSumaVentas(){

        $tabla = "productos";
        return ModeloProductos::mdlMostrarSumaVentas($tabla);

    }
    /*=============================================
OBTENER SIGUIENTE CÓDIGO DE PRODUCTO
=============================================*/
static public function ctrObtenerSiguienteCodigo($item, $valor){

    $tabla = "productos";
    $respuesta = ModeloProductos::mdlObtenerUltimoCodigoPorCategoria($tabla, $item, $valor);
    return $respuesta;

}
}