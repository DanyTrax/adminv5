<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion-central.php';

try {
    
    $dbCentral = ConexionCentral::conectar();
    
    // Obtener parámetros opcionales
    $soloActivas = isset($_GET['solo_activas']) ? (bool)$_GET['solo_activas'] : false;
    $conUltimaSincronizacion = isset($_GET['con_sincronizacion']) ? (bool)$_GET['con_sincronizacion'] : false;
    
    // Construir query
    $query = "
        SELECT 
            id, codigo_sucursal, nombre, direccion, telefono, email, logo, 
            url_base, api_url, activa, es_principal, fecha_creacion, 
            fecha_actualizacion, ultima_sincronizacion, observaciones
        FROM sucursales
    ";
    
    $params = [];
    
    if ($soloActivas) {
        $query .= " WHERE activa = 1";
    }
    
    $query .= " ORDER BY es_principal DESC, nombre ASC";
    
    $stmt = $dbCentral->prepare($query);
    $stmt->execute($params);
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas y agregar información adicional
    foreach ($sucursales as &$sucursal) {
        $sucursal['activa'] = (bool)$sucursal['activa'];
        $sucursal['es_principal'] = (bool)$sucursal['es_principal'];
        
        // Formatear fecha de última sincronización
        if ($sucursal['ultima_sincronizacion']) {
            $sucursal['ultima_sincronizacion_formato'] = date('d/m/Y H:i:s', strtotime($sucursal['ultima_sincronizacion']));
        } else {
            $sucursal['ultima_sincronizacion_formato'] = 'Nunca';
        }
        
        // URL completa del logo si existe
        if (!empty($sucursal['logo'])) {
            $sucursal['logo_url'] = $sucursal['url_base'] . 'vistas/img/sucursales/' . $sucursal['logo'];
        } else {
            $sucursal['logo_url'] = null;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Sucursales obtenidas correctamente',
        'data' => $sucursales,
        'total' => count($sucursales)
    ]);
    
} catch (Exception $e) {
    
    error_log("Error en obtener_sucursales.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener sucursales: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>