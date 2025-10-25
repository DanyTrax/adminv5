<?php
// --- INICIO DE LA SESIÓN Y CARGA DE CLASES ---
session_start();
require_once "../../modelos/conexion.php";
require_once "../../api-transferencias/conexion-central.php";

// --- HEADERS Y NOMBRE DE ARCHIVO ---
$nombreArchivo = 'registro-descargas';
if (isset($_GET["fechaInicial"]) && !empty($_GET["fechaInicial"])) {
    $nombreArchivo .= '_' . $_GET["fechaInicial"] . '_a_' . $_GET["fechaFinal"];
}
$nombreArchivo .= '.xls';
header('Expires: 0');
header('Cache-control: private');
header("Content-type: application/vnd.ms-excel; charset=utf-8");
header("Cache-Control: cache, must-revalidate");
header('Content-Description: File Transfer');
header("Pragma: public");
header('Content-Disposition:; filename="' . $nombreArchivo . '"');
header("Content-Transfer-Encoding: binary");

$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

// =================================================================
// OBTENER DATOS DE LA BASE DE DATOS
// =================================================================
try {
    $conexion = ConexionCentral::conectar();
    
    // Construir consulta con filtros de fecha
    $whereClause = "";
    $params = [];
    
    if ($fechaInicial && $fechaFinal) {
        $whereClause = "WHERE DATE(fecha_descarga) BETWEEN :fechaInicial AND :fechaFinal";
        $params[":fechaInicial"] = $fechaInicial;
        $params[":fechaFinal"] = $fechaFinal;
    }
    
    $sql = "
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
        $whereClause
        ORDER BY created_at DESC
    ";
    
    $stmt = $conexion->prepare($sql);
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // =================================================================
    // CONSTRUIR EL EXCEL CON FORMATO Y COLORES
    // =================================================================
    echo "
    <table border='1'>
        <tr><td colspan='10' style='font-weight:bold; background-color:#3c8dbc; color:white; text-align:center;'>REPORTE DE REGISTRO DE DESCARGAS</td></tr>
        <tr><td colspan='10' style='font-weight:bold; background-color:#f0f0f0;'>Generado por: " . $_SESSION["nombre"] . "</td></tr>
        <tr><td colspan='10' style='font-weight:bold; background-color:#f0f0f0;'>Fecha de generación: " . date('d/m/Y H:i:s') . "</td></tr>
        <tr><td colspan='10' style='font-weight:bold; background-color:#f0f0f0;'>Perfil: " . $_SESSION["perfil"] . "</td></tr>
    ";
    
    if ($fechaInicial && $fechaFinal) {
        echo "<tr><td colspan='10' style='font-weight:bold; background-color:#f0f0f0;'>Período del reporte: " . $fechaInicial . " a " . $fechaFinal . "</td></tr>";
    } else {
        echo "<tr><td colspan='10' style='font-weight:bold; background-color:#f0f0f0;'>Período del reporte: Todos los registros</td></tr>";
    }
    
    echo "</table><br><br>";
    
    // --- TABLA PRINCIPAL DE DATOS ---
    echo "
    <table border='1'>
        <tr><td colspan='10' style='font-weight:bold; background-color:#00a65a; color:white; text-align:center;'>REGISTRO DE DESCARGAS DETALLADO</td></tr>
        <tr style='background-color:#f9f9f9;'>
            <th style='font-weight:bold; background-color:#e0e0e0;'>ID</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>FECHA Y HORA</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>CÓDIGO PRODUCTO</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>DESCRIPCIÓN</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>CANTIDAD</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>USUARIO</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>TRANSPORTADOR</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>SUCURSAL</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>DESPACHO</th>
            <th style='font-weight:bold; background-color:#e0e0e0;'>OBSERVACIONES</th>
        </tr>
    ";
    
    $totalCantidad = 0;
    $contador = 0;
    
    foreach($registros as $registro) {
        $totalCantidad += $registro["cantidad_descargada"];
        $contador++;
        
        // Alternar colores de fila
        $colorFila = ($contador % 2 == 0) ? 'background-color:#f9f9f9;' : 'background-color:#ffffff;';
        
        echo "<tr style='$colorFila'>
            <td>" . $registro["id"] . "</td>
            <td>" . $registro["fecha_hora"] . "</td>
            <td>" . $registro["codigo_producto"] . "</td>
            <td>" . $registro["descripcion_producto"] . "</td>
            <td style='text-align:center;'>" . $registro["cantidad_descargada"] . "</td>
            <td>" . $registro["usuario_nombre"] . "</td>
            <td>" . ($registro["transportador_nombre"] ?: 'Sin transportador') . "</td>
            <td>" . $registro["sucursal_nombre"] . "</td>
            <td>" . ($registro["numero_despacho"] ?: 'Sin despacho') . "</td>
            <td>" . ($registro["observaciones"] ?: 'Sin observaciones') . "</td>
        </tr>";
    }
    
    echo "</table><br><br>";
    
    // --- TABLA DE RESUMEN ---
    echo "
    <table border='1'>
        <tr><td colspan='3' style='font-weight:bold; background-color:#f39c12; color:white; text-align:center;'>RESUMEN DEL REPORTE</td></tr>
        <tr>
            <td style='font-weight:bold; background-color:#e0e0e0;'>Total de Registros</td>
            <td style='font-weight:bold; background-color:#e0e0e0;'>Total Cantidad Descargada</td>
            <td style='font-weight:bold; background-color:#e0e0e0;'>Promedio por Registro</td>
        </tr>
        <tr>
            <td style='text-align:center;'>" . count($registros) . "</td>
            <td style='text-align:center;'>" . $totalCantidad . "</td>
            <td style='text-align:center;'>" . (count($registros) > 0 ? round($totalCantidad / count($registros), 2) : 0) . "</td>
        </tr>
    </table><br><br>";
    
    // --- ESTADÍSTICAS POR USUARIO ---
    if (!empty($registros)) {
        $statsUsuario = [];
        $statsTransportador = [];
        $statsSucursal = [];
        
        foreach ($registros as $registro) {
            $statsUsuario[$registro['usuario_nombre']] = ($statsUsuario[$registro['usuario_nombre']] ?? 0) + $registro['cantidad_descargada'];
            if (!empty($registro['transportador_nombre'])) {
                $statsTransportador[$registro['transportador_nombre']] = ($statsTransportador[$registro['transportador_nombre']] ?? 0) + $registro['cantidad_descargada'];
            }
            $statsSucursal[$registro['sucursal_nombre']] = ($statsSucursal[$registro['sucursal_nombre']] ?? 0) + $registro['cantidad_descargada'];
        }
        
        // --- TABLA DE ESTADÍSTICAS POR USUARIO ---
        echo "
        <table border='1'>
            <tr><td colspan='2' style='font-weight:bold; background-color:#00c0ef; color:white; text-align:center;'>ESTADÍSTICAS POR USUARIO</td></tr>
            <tr><td style='font-weight:bold; background-color:#e0e0e0;'>Usuario</td><td style='font-weight:bold; background-color:#e0e0e0;'>Total Descargado</td></tr>
        ";
        foreach ($statsUsuario as $nombre => $cantidad) {
            echo "<tr><td>" . $nombre . "</td><td style='text-align:center;'>" . $cantidad . "</td></tr>";
        }
        echo "</table><br><br>";
        
        // --- TABLA DE ESTADÍSTICAS POR TRANSPORTADOR ---
        if (!empty($statsTransportador)) {
            echo "
            <table border='1'>
                <tr><td colspan='2' style='font-weight:bold; background-color:#dd4b39; color:white; text-align:center;'>ESTADÍSTICAS POR TRANSPORTADOR</td></tr>
                <tr><td style='font-weight:bold; background-color:#e0e0e0;'>Transportador</td><td style='font-weight:bold; background-color:#e0e0e0;'>Total Descargado</td></tr>
            ";
            foreach ($statsTransportador as $nombre => $cantidad) {
                echo "<tr><td>" . $nombre . "</td><td style='text-align:center;'>" . $cantidad . "</td></tr>";
            }
            echo "</table><br><br>";
        }
        
        // --- TABLA DE ESTADÍSTICAS POR SUCURSAL ---
        echo "
        <table border='1'>
            <tr><td colspan='2' style='font-weight:bold; background-color:#605ca8; color:white; text-align:center;'>ESTADÍSTICAS POR SUCURSAL</td></tr>
            <tr><td style='font-weight:bold; background-color:#e0e0e0;'>Sucursal</td><td style='font-weight:bold; background-color:#e0e0e0;'>Total Descargado</td></tr>
        ";
        foreach ($statsSucursal as $nombre => $cantidad) {
            echo "<tr><td>" . $nombre . "</td><td style='text-align:center;'>" . $cantidad . "</td></tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "
    <table border='1'>
        <tr><td style='font-weight:bold; background-color:#dd4b39; color:white;'>ERROR</td></tr>
        <tr><td>Error al generar el reporte: " . $e->getMessage() . "</td></tr>
    </table>";
}
?>
