<?php
session_start();

if(isset($_GET['editar'])) {
    $idDespacho = $_GET['editar'];
    
    echo "<h2>🔍 Debugging edición de despacho ID: $idDespacho</h2>";
    
    try {
        require_once "controladores/despachos.controlador.php";
        require_once "modelos/despachos.modelo.php";
        
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if($despacho) {
            echo "<h3>✅ Despacho encontrado:</h3>";
            echo "<pre>";
            print_r($despacho);
            echo "</pre>";
            
            echo "<h3>🔍 Productos del despacho:</h3>";
            $productos = json_decode($despacho["productos_despacho"], true);
            if($productos) {
                echo "<pre>";
                print_r($productos);
                echo "</pre>";
            } else {
                echo "<p>❌ Error al parsear productos JSON</p>";
                echo "<p>JSON original: " . $despacho["productos_despacho"] . "</p>";
            }
            
        } else {
            echo "<p>❌ Despacho no encontrado</p>";
        }
        
    } catch(Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p>❌ No se proporcionó ID de despacho</p>";
}
?>