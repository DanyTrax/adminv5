<?php
/**
 * Función corregida para importar medios de pago usando conexión directa a BD local
 */

// Función para importar medios de pago de sucursales seleccionadas
function importarMediosPagoDeSucursalesSeleccionadas($pdo, $datos_central, $sucursales_seleccionadas) {
    try {
        if (empty($sucursales_seleccionadas)) {
            error_log("No hay sucursales seleccionadas para importar medios de pago");
            return false;
        }
        
        // Obtener datos de las sucursales seleccionadas desde el central
        $url_central = $datos_central['url_central'] ?? '';
        if (empty($url_central)) {
            error_log("URL central no configurada para importar medios de pago");
            return false;
        }
        
        // Conectar a BD central para obtener datos de sucursales
        require_once __DIR__ . '/../api-transferencias/conexion-central.php';
        
        try {
            $pdo_central = ConexionCentral::conectar();
        } catch (Exception $e) {
            error_log("Error al conectar a BD central: " . $e->getMessage());
            return false;
        }
        
        // Obtener datos completos de las sucursales seleccionadas desde BD central
        $sucursales_activas = [];
        foreach ($sucursales_seleccionadas as $seleccionada) {
            $stmt = $pdo_central->prepare("
                SELECT 
                    s.id,
                    s.codigo_sucursal,
                    s.nombre,
                    s.url_base,
                    s.url_api,
                    sl.usuario_bd,
                    sl.password_bd,
                    sl.nombre_bd,
                    sl.host_bd,
                    sl.puerto_bd
                FROM sucursales s
                LEFT JOIN sucursal_local sl ON s.id = sl.id
                WHERE s.id = ? AND s.activo = 1
            ");
            
            $stmt->execute([$seleccionada['id']]);
            $sucursal = $stmt->fetch();
            
            if ($sucursal) {
                $sucursales_activas[] = $sucursal;
            }
        }
        
        if (empty($sucursales_activas)) {
            error_log("No se encontraron datos de las sucursales seleccionadas");
            return false;
        }
        
        $medios_importados = 0;
        $sucursales_procesadas = 0;
        
        // Procesar cada sucursal seleccionada
        foreach ($sucursales_activas as $sucursal) {
            try {
                error_log("Procesando sucursal: {$sucursal['nombre']} - BD: {$sucursal['nombre_bd']}");
                
                // Conectar directamente a BD local de la sucursal
                $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
                $pdo_sucursal = new PDO(
                    $dsn,
                    $sucursal['usuario_bd'],
                    $sucursal['password_bd'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                
                // Obtener medios de pago de la BD local de la sucursal
                $stmt_medios = $pdo_sucursal->prepare("
                    SELECT 
                        id,
                        nombre
                    FROM medios_pago
                    ORDER BY nombre ASC
                ");
                
                $stmt_medios->execute();
                $medios_pago = $stmt_medios->fetchAll();
                
                error_log("Medios de pago encontrados en {$sucursal['nombre']}: " . count($medios_pago));
                
                // Insertar cada medio de pago en la nueva instalación
                foreach ($medios_pago as $medio) {
                    try {
                        // Verificar si ya existe
                        $stmt_check = $pdo->prepare("
                            SELECT id FROM medios_pago 
                            WHERE nombre = ?
                        ");
                        $stmt_check->execute([$medio['nombre']]);
                        
                        if ($stmt_check->fetch()) {
                            error_log("Medio de pago '{$medio['nombre']}' ya existe, omitiendo");
                            continue;
                        }
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO medios_pago (
                                nombre
                            ) VALUES (?)
                        ");
                        
                        $stmt->execute([$medio['nombre']]);
                        
                        $medios_importados++;
                        error_log("Medio de pago importado de {$sucursal['nombre']}: " . $medio['nombre']);
                        
                    } catch (PDOException $e) {
                        error_log("Error al insertar medio de pago '{$medio['nombre']}' de {$sucursal['nombre']}: " . $e->getMessage());
                        continue;
                    }
                }
                
                $sucursales_procesadas++;
                
            } catch (Exception $e) {
                error_log("Error al procesar sucursal {$sucursal['nombre']}: " . $e->getMessage());
                continue;
            }
        }
        
        error_log("Importación completada: $medios_importados medios de pago importados de $sucursales_procesadas sucursales");
        return true;
        
    } catch (Exception $e) {
        error_log("Error general en importarMediosPagoDeSucursalesSeleccionadas: " . $e->getMessage());
        return false;
    }
}
?>
