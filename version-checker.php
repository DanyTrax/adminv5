<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Incluye el archivo que deseas probar
require_once "modelos/productos.modelo.php";

// Ejecuta una función de prueba si quieres
try {
    $datos = array(
        "id_categoria" => 1,
        "codigo" => "TEST123",
        "descripcion" => "Producto de prueba",
        "imagen" => "default.png",
        "stock" => "10",
        "precio_compra" => "100",
        "precio_venta" => "150"
    );

    $respuesta = ModeloProductos::mdlIngresarProducto("productos", $datos);
    echo "<pre>"; print_r($respuesta); echo "</pre>";

} catch (Throwable $e) {
    echo "<strong>❌ ERROR DE COMPATIBILIDAD:</strong><br>";
    echo "Tipo: " . get_class($e) . "<br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
}
