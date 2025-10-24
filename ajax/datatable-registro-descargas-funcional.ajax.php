<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS - FUNCIONAL
=============================================*/

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir conexión
require_once "../modelos/conexion.php";

try {
    // Verificar conexión
    $conexion = Conexion::conectar();
    if (!$conexion) {
        echo json_encode(["error" => "No hay conexión a la base de datos"]);
        exit;
    }

    // Verificar tabla
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if (!$tabla) {
        echo json_encode(["error" => "Tabla registro_descargas_stock_transito no existe"]);
        exit;
    }

    // Obtener datos con formato correcto
    $stmt = $conexion->prepare("
        SELECT 
            id,
            DATE_FORMAT(fecha_descarga, '%d/%m/%Y %H:%i:%s') as fecha_hora,
            codigo_producto,
            descripcion_producto,
            cantidad_descargada,
            usuario_nombre,
            transportador_nombre,
            sucursal_nombre,
            numero_despacho,
            observaciones
        FROM registro_descargas_stock_transito 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear datos para DataTable
    $data = [];
    foreach($datos as $dato) {
        $data[] = [
            $dato['id'],
            $dato['fecha_hora'],
            $dato['codigo_producto'],
            $dato['descripcion_producto'],
            $dato['cantidad_descargada'],
            $dato['usuario_nombre'],
            $dato['transportador_nombre'],
            $dato['sucursal_nombre'],
            $dato['numero_despacho'],
            $dato['observaciones']
        ];
    }

    // Respuesta para DataTable
    echo json_encode([
        "data" => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
?>
