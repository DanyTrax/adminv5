<?php
session_start();

// ✅ LIMPIAR CUALQUIER SALIDA PREVIA
if (ob_get_level()) {
    ob_clean();
}

// ✅ ESTABLECER HEADER PARA JSON
header('Content-Type: application/json; charset=utf-8');

try {
    
    // ✅ VERIFICAR SESIÓN
    if (!isset($_SESSION['perfil'])) {
        echo json_encode([
            "success" => false,
            "message" => "Sin sesión activa",
            "data" => ["contador" => 0, "solicitudes" => []]
        ]);
        exit;
    }

    // ✅ VERIFICAR PERMISOS
    if($_SESSION["perfil"] != "Transportador" && $_SESSION["perfil"] != "Administrador") {
        echo json_encode([
            "success" => true,
            "data" => [
                "contador" => 0,
                "solicitudes" => [],
                "mensaje" => "Sin permisos para ver notificaciones"
            ]
        ]);
        exit;
    }

    // ✅ INCLUIR ARCHIVOS NECESARIOS
    $archivos_requeridos = [
        "../api-transferencias/conexion-central.php",
        "../controladores/solicitudes-stock.controlador.php"
    ];

    foreach($archivos_requeridos as $archivo) {
        if(!file_exists($archivo)) {
            throw new Exception("Archivo requerido no encontrado: $archivo");
        }
        require_once $archivo;
    }

    // ✅ VERIFICAR CONEXIÓN
    $conexion = ConexionCentral::conectar();
    if(!$conexion) {
        throw new Exception("No se pudo conectar a la base central");
    }

    // ✅ PROCESAR SEGÚN ACCIÓN
    $accion = $_POST["accion"] ?? 'obtener_notificaciones';
    
    switch($accion) {
        case 'obtener_notificaciones':
        case 'obtener_pendientes':
            
            // ✅ CONTAR SOLICITUDES PENDIENTES - DIRECTO
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM solicitudes_stock WHERE estado = 'pendiente'");
            $stmt->execute();
            $conteoResult = $stmt->fetch();
            $contador = intval($conteoResult['total'] ?? 0);

            // ✅ OBTENER SOLICITUDES RECIENTES - DIRECTO
            $stmt = $conexion->prepare("
                SELECT 
                    id,
                    numero_solicitud,
                    nombre_sucursal_solicitante,
                    nombre_usuario_solicitante,
                    tipo_solicitud,
                    total_productos,
                    total_cantidad,
                    fecha_solicitud,
                    detalle_adicional
                FROM solicitudes_stock 
                WHERE estado = 'pendiente'
                ORDER BY fecha_solicitud DESC 
                LIMIT 8
            ");
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ✅ PROCESAR FECHAS
            foreach($solicitudes as &$solicitud) {
                $timestamp = strtotime($solicitud['fecha_solicitud']);
                $diferencia = time() - $timestamp;
                
                if($diferencia < 60) {
                    $solicitud['fecha_relativa'] = 'Ahora';
                } elseif($diferencia < 3600) {
                    $minutos = floor($diferencia / 60);
                    $solicitud['fecha_relativa'] = $minutos . 'min';
                } elseif($diferencia < 86400) {
                    $horas = floor($diferencia / 3600);
                    $solicitud['fecha_relativa'] = $horas . 'h';
                } else {
                    $solicitud['fecha_relativa'] = date('d/m', $timestamp);
                }
                
                $solicitud['fecha_formateada'] = date('d/m/Y H:i', $timestamp);
            }

            // ✅ RESPUESTA EXITOSA
            echo json_encode([
                "success" => true,
                "data" => [
                    "contador" => $contador,
                    "solicitudes" => $solicitudes,
                    "perfil" => $_SESSION["perfil"],
                    "timestamp" => time()
                ]
            ]);
            break;
            
        case 'marcar_como_vistas':
            echo json_encode([
                "success" => true,
                "message" => "Notificaciones marcadas como vistas"
            ]);
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "message" => "Acción no reconocida: $accion",
                "data" => ["contador" => 0, "solicitudes" => []]
            ]);
            break;
    }

} catch(Exception $e) {
    // ✅ ERROR CONTROLADO
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage(),
        "data" => [
            "contador" => 0,
            "solicitudes" => []
        ],
        "debug" => [
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "trace" => $e->getTraceAsString()
        ]
    ]);
}

// ✅ LIMPIAR BUFFER DE SALIDA
if (ob_get_level()) {
    ob_end_flush();
}
?>