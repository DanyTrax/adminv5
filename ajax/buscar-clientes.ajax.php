<?php

require_once "../controladores/clientes.controlador.php";

if(isset($_POST["buscarCliente"])) {
    
    $busqueda = $_POST["buscarCliente"];
    
    // Obtener todos los clientes
    $clientes = ControladorClientes::ctrMostrarClientes(null, null);
    
    $resultados = [];
    
    if($clientes) {
        foreach($clientes as $cliente) {
            // Buscar en nombre y documento
            $nombre = strtolower($cliente["nombre"]);
            $documento = $cliente["documento"];
            $busquedaLower = strtolower($busqueda);
            
            // Verificar si coincide con nombre o documento
            if(strpos($nombre, $busquedaLower) !== false || strpos($documento, $busqueda) !== false) {
                $resultados[] = [
                    "id" => $cliente["id"],
                    "nombre" => $cliente["nombre"],
                    "documento" => $documento,
                    "email" => $cliente["email"],
                    "telefono" => $cliente["telefono"]
                ];
            }
        }
    }
    
    // Limitar a 10 resultados
    $resultados = array_slice($resultados, 0, 10);
    
    echo json_encode($resultados);
    
}
