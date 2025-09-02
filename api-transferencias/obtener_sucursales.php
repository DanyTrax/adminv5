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
    $incluirActual = isset($_GET['incluir_actual']) ? (bool)$_GET['incluir_actual'] : false;
    $conUltimaSincronizacion = isset($_GET['con_sincronizacion']) ? (bool)$_GET['con_sincronizacion'] : false;
    
    // Construir query base
    $query = "
        SELECT 
            id, 
            codigo_sucursal, 
            nombre, 
            direccion, 
            telefono, 
            email, 
            logo, 
            url_base, 
            api_url, 
            activa, 
            es_principal, 
            fecha_creacion, 
            fecha_actualizacion, 
            ultima_sincronizacion, 
            observaciones
        FROM sucursales
    ";
    
    $params = [];
    $whereConditions = [];
    
    // Filtrar solo activas si se solicita
    if ($soloActivas) {
        $whereConditions[] = "activa = 1";
    }
    
    // Aplicar condiciones WHERE si las hay
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    // Ordenar por principal primero, luego por nombre
    $query .= " ORDER BY es_principal DESC, nombre ASC";
    
    $stmt = $dbCentral->prepare($query);
    $stmt->execute($params);
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas y agregar información adicional
    foreach ($sucursales as &$sucursal) {
        
        // Convertir valores boolean
        $sucursal['activa'] = (bool)$sucursal['activa'];
        $sucursal['es_principal'] = (bool)$sucursal['es_principal'];
        
        // Formatear fecha de última sincronización
        if ($sucursal['ultima_sincronizacion'] && $sucursal['ultima_sincronizacion'] !== '0000-00-00 00:00:00') {
            $fechaSincronizacion = new DateTime($sucursal['ultima_sincronizacion']);
            $sucursal['ultima_sincronizacion_formato'] = $fechaSincronizacion->format('d/m/Y H:i:s');
            
            // Calcular tiempo transcurrido
            $ahora = new DateTime();
            $diferencia = $ahora->diff($fechaSincronizacion);
            
            if ($diferencia->d > 0) {
                $sucursal['tiempo_desde_sincronizacion'] = $diferencia->d . ' día(s) atrás';
            } elseif ($diferencia->h > 0) {
                $sucursal['tiempo_desde_sincronizacion'] = $diferencia->h . ' hora(s) atrás';
            } elseif ($diferencia->i > 0) {
                $sucursal['tiempo_desde_sincronizacion'] = $diferencia->i . ' minuto(s) atrás';
            } else {
                $sucursal['tiempo_desde_sincronizacion'] = 'Hace un momento';
            }
        } else {
            $sucursal['ultima_sincronizacion_formato'] = 'Nunca';
            $sucursal['tiempo_desde_sincronizacion'] = 'Nunca sincronizada';
        }
        
        // URL completa del logo si existe
        if (!empty($sucursal['logo'])) {
            $sucursal['logo_url'] = rtrim($sucursal['url_base'], '/') . '/vistas/img/sucursales/' . $sucursal['logo'];
        } else {
            $sucursal['logo_url'] = null;
        }
        
        // Estado de conectividad (se puede implementar más tarde)
        $sucursal['estado_conexion'] = 'desconocido';
        
        // Información adicional
        $sucursal['fecha_creacion_formato'] = date('d/m/Y H:i:s', strtotime($sucursal['fecha_creacion']));
        $sucursal['fecha_actualizacion_formato'] = date('d/m/Y H:i:s', strtotime($sucursal['fecha_actualizacion']));
    }
    
    // Si se requiere incluir la sucursal actual y no está en la lista
    if ($incluirActual) {
        
        // Definir información de la sucursal actual
        $nombreSucursalActual = defined('NOMBRE_SUCURSAL') ? NOMBRE_SUCURSAL : 'Sucursal Actual';
        $sucursalActualExiste = false;
        
        // Verificar si la sucursal actual ya existe en la lista
        foreach ($sucursales as $sucursal) {
            if ($sucursal['nombre'] === $nombreSucursalActual) {
                $sucursalActualExiste = true;
                // Marcar como sucursal actual
                $sucursal['es_actual'] = true;
                break;
            }
        }
        
        // Si no existe, agregar sucursal actual
        if (!$sucursalActualExiste) {
            
            // Obtener información del servidor actual
            $protocoloActual = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $hostActual = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $directorioActual = dirname($_SERVER['SCRIPT_NAME']);
            $urlBaseActual = $protocoloActual . $hostActual . rtrim(str_replace('/api-transferencias', '', $directorioActual), '/') . '/';
            
            $sucursalActual = [
                'id' => 0, // ID especial para sucursal actual
                'codigo_sucursal' => 'ACTUAL',
                'nombre' => $nombreSucursalActual,
                'direccion' => 'Sucursal actual (local)',
                'telefono' => '',
                'email' => '',
                'logo' => '',
                'url_base' => $urlBaseActual,
                'api_url' => $urlBaseActual . 'api-transferencias/',
                'activa' => true,
                'es_principal' => false,
                'es_actual' => true,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_actualizacion' => date('Y-m-d H:i:s'),
                'ultima_sincronizacion' => null,
                'ultima_sincronizacion_formato' => 'Local',
                'tiempo_desde_sincronizacion' => 'Sucursal local',
                'logo_url' => null,
                'estado_conexion' => 'local',
                'fecha_creacion_formato' => date('d/m/Y H:i:s'),
                'fecha_actualizacion_formato' => date('d/m/Y H:i:s'),
                'observaciones' => 'Esta es la sucursal actual desde donde se está ejecutando el sistema'
            ];
            
            // Agregar al inicio del array (para que aparezca primera)
            array_unshift($sucursales, $sucursalActual);
        }
    }
    
    // Estadísticas adicionales
    $estadisticas = [
        'total' => count($sucursales),
        'activas' => 0,
        'inactivas' => 0,
        'principales' => 0,
        'con_logo' => 0,
        'con_sincronizacion_reciente' => 0
    ];
    
    // Calcular estadísticas
    $hace24Horas = new DateTime();
    $hace24Horas->sub(new DateInterval('P1D'));
    
    foreach ($sucursales as $sucursal) {
        
        // Contar estados
        if ($sucursal['activa']) {
            $estadisticas['activas']++;
        } else {
            $estadisticas['inactivas']++;
        }
        
        // Contar principales
        if ($sucursal['es_principal']) {
            $estadisticas['principales']++;
        }
        
        // Contar con logo
        if (!empty($sucursal['logo'])) {
            $estadisticas['con_logo']++;
        }
        
        // Contar con sincronización reciente (últimas 24 horas)
        if ($sucursal['ultima_sincronizacion'] && 
            $sucursal['ultima_sincronizacion'] !== '0000-00-00 00:00:00') {
            
            $fechaSincronizacion = new DateTime($sucursal['ultima_sincronizacion']);
            if ($fechaSincronizacion > $hace24Horas) {
                $estadisticas['con_sincronizacion_reciente']++;
            }
        }
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Sucursales obtenidas correctamente',
        'data' => $sucursales,
        'estadisticas' => $estadisticas,
        'total' => count($sucursales),
        'filtros_aplicados' => [
            'solo_activas' => $soloActivas,
            'incluir_actual' => $incluirActual,
            'con_sincronizacion' => $conUltimaSincronizacion
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    
    error_log("Error de base de datos en obtener_sucursales.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'data' => [],
        'estadisticas' => [
            'total' => 0,
            'activas' => 0,
            'inactivas' => 0,
            'principales' => 0,
            'con_logo' => 0,
            'con_sincronizacion_reciente' => 0
        ]
    ]);
    
} catch (Exception $e) {
    
    error_log("Error general en obtener_sucursales.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'data' => [],
        'estadisticas' => [
            'total' => 0,
            'activas' => 0,
            'inactivas' => 0,
            'principales' => 0,
            'con_logo' => 0,
            'con_sincronizacion_reciente' => 0
        ]
    ]);
}
?>