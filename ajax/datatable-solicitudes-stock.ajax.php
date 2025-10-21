<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// INCLUIR AMBAS CONEXIONES
require_once "../modelos/conexion.php";           // ✅ Base LOCAL
require_once "../api-transferencias/conexion-central.php"; // ✅ Base CENTRAL

try {
    // TEST 1: Verificar conexión LOCAL (usuarios)
    $conexionLocal = Conexion::conectar();
    if (!$conexionLocal) {
        echo json_encode(["error" => "No hay conexión LOCAL"]);
        exit;
    }

    // TEST 2: Verificar conexión CENTRAL (solicitudes)
    $conexionCentral = ConexionCentral::conectar();
    if (!$conexionCentral) {
        echo json_encode(["error" => "No hay conexión CENTRAL"]);
        exit;
    }

    // TEST 3: Verificar tabla usuarios en base LOCAL
    $stmt = $conexionLocal->prepare("SHOW TABLES LIKE 'usuarios'");
    $stmt->execute();
    $tabla_usuarios = $stmt->fetch();
    
    if (!$tabla_usuarios) {
        echo json_encode(["error" => "Tabla usuarios no existe en base LOCAL"]);
        exit;
    }

    // TEST 4: Verificar tabla solicitudes en base CENTRAL
    $stmt = $conexionCentral->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
    $stmt->execute();
    $tabla_solicitudes = $stmt->fetch();
    
    if (!$tabla_solicitudes) {
        echo json_encode(["error" => "Tabla solicitudes_stock no existe en base CENTRAL"]);
        exit;
    }

    // TEST 5: Verificar usuario actual en base LOCAL
    $stmt = $conexionLocal->prepare("SELECT id, nombre, perfil FROM usuarios WHERE id = :id");
    $stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
    $stmt->execute();
    $usuario_actual = $stmt->fetch();
    
    if (!$usuario_actual) {
        echo json_encode(["error" => "Usuario actual no encontrado en base LOCAL"]);
        exit;
    }

    // TEST 6: Contar solicitudes en base CENTRAL
    $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM solicitudes_stock");
    $stmt->execute();
    $resultado = $stmt->fetch();
    $total_solicitudes = $resultado['total'];

    // TEST 7: Si hay solicitudes, obtenerlas de base CENTRAL
    if($total_solicitudes > 0) {
        $stmt = $conexionCentral->prepare("SELECT * FROM solicitudes_stock ORDER BY fecha_solicitud DESC");
        $stmt->execute();
        $solicitudes = $stmt->fetchAll();
        
        // Crear JSON para DataTable usando datos de AMBAS bases
        $data = [];
        foreach($solicitudes as $solicitud) {
    
            // Datos de SOLICITUD (base central)
            $estado = $solicitud["estado"];
            $estadoClass = $estado == 'pendiente' ? 'label-warning' : ($estado == 'aprobado' ? 'label-success' : 'label-danger');
            $estadoHtml = "<span class='label {$estadoClass}'>".ucfirst($estado)."</span>";
            
            // ✅ BOTONES CON CLASES Y ATRIBUTOS PARA JAVASCRIPT
            $acciones = "<div class='btn-group'>";
            
            // ✅ BOTÓN VER - Todos pueden ver
            $acciones .= "<button class='btn btn-info btn-xs btnVerSolicitud' 
                            idSolicitud='{$solicitud["id"]}' 
                            title='Ver detalles'>
                            <i class='fa fa-eye'></i>
                        </button>";
            
            // ✅ BOTÓN CREAR DESPACHO - Solo para solicitudes aprobadas
            if($estado == "aprobado") {
                $acciones .= " <button class='btn btn-primary btn-xs btnCrearDespachoDesdeSolicitud' 
                                idSolicitud='{$solicitud["id"]}' 
                                numeroSolicitud='{$solicitud["numero_solicitud"]}'
                                title='Crear despacho desde esta solicitud'>
                                <i class='fa fa-truck'></i>
                            </button>";
            }
            
            // ✅ BOTÓN VER STOCK PARA TRANSPORTADOR - Solo para transportadores y solicitudes aprobadas
            if($usuario_actual["perfil"] == "Transportador" && $estado == "aprobado") {
                $acciones .= " <button class='btn btn-info btn-xs btnVerStockTransportador' 
                                idSolicitud='{$solicitud["id"]}' 
                                numeroSolicitud='{$solicitud["numero_solicitud"]}'
                                title='Ver stock disponible en sucursales'>
                                <i class='fa fa-cubes'></i>
                            </button>";
            }
            
            // ✅ BOTONES DE ACCIÓN - Solo Transportador/Administrador
            if($usuario_actual["perfil"] == "Transportador" || $usuario_actual["perfil"] == "Administrador") {
                if($estado == "pendiente") {
                    $acciones .= " <button class='btn btn-success btn-xs btnAprobarSolicitud' 
                                    idSolicitud='{$solicitud["id"]}' 
                                    title='Aprobar solicitud'>
                                    <i class='fa fa-check'></i>
                                </button>";
                                
                    $acciones .= " <button class='btn btn-warning btn-xs btnCancelarSolicitud' 
                                    idSolicitud='{$solicitud["id"]}' 
                                    title='Cancelar solicitud'>
                                    <i class='fa fa-times'></i>
                                </button>";
                }
            }
            
            // ✅ BOTÓN ELIMINAR - Solo Administrador
            if($usuario_actual["perfil"] == "Administrador") {
                $acciones .= " <button class='btn btn-danger btn-xs btnEliminarSolicitud' 
                                idSolicitud='{$solicitud["id"]}' 
                                title='Eliminar solicitud'>
                                <i class='fa fa-trash'></i>
                            </button>";
            }
            
            $acciones .= "</div>";
            
            $data[] = [
                $solicitud["id"],
                $solicitud["numero_solicitud"],
                $solicitud["nombre_sucursal_solicitante"],
                $solicitud["nombre_usuario_solicitante"],
                $solicitud["tipo_solicitud"],
                $solicitud["total_productos"] . " productos",
                $estadoHtml,
                date('d/m/Y H:i', strtotime($solicitud["fecha_solicitud"])),
                $solicitud["nombre_usuario_aprobacion"] ?: "N/A",
                $acciones
            ];
        }
        
        echo json_encode([
            "data" => $data,
            "debug_info" => [
                "usuario_local" => $usuario_actual["nombre"] . " (" . $usuario_actual["perfil"] . ")",
                "solicitudes_central" => $total_solicitudes,
                "conexiones" => "LOCAL ✓ CENTRAL ✓"
            ]
        ]);
        
    } else {
        // No hay solicitudes, mostrar tabla vacía con info de debug
        echo json_encode([
            "data" => [],
            "debug_info" => [
                "mensaje" => "No hay solicitudes registradas",
                "usuario_local" => $usuario_actual["nombre"] . " (" . $usuario_actual["perfil"] . ")",
                "conexiones" => "LOCAL ✓ CENTRAL ✓"
            ]
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "error" => "Error en test dual: " . $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
}