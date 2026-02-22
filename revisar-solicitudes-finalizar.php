<?php
/**
 * Script para revisar y finalizar solicitudes que ya tienen sus despachos completos
 * Uso: dominio.com/revisar-solicitudes-finalizar.php?clave=TU_CLAVE
 * O con sesión de Administrador
 */
session_start();

$clavePermitida = "revisar2025";
$claveRecibida = $_GET['clave'] ?? '';

if ($claveRecibida !== $clavePermitida) {
    if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
        die('Acceso denegado. Usa: ?clave=TU_CLAVE o inicia sesión como Administrador.');
    }
}

require_once __DIR__ . "/api-transferencias/conexion-central.php";
require_once __DIR__ . "/modelos/solicitudes-stock.modelo.php";

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Revisar solicitudes pendientes de finalizar</h2><pre>";

try {
    $conexion = ConexionCentral::conectar();
    
    // Obtener solicitudes aprobadas que tienen despachos vinculados
    $stmt = $conexion->prepare("
        SELECT DISTINCT s.id, s.numero_solicitud, s.estado 
        FROM solicitudes_stock s
        INNER JOIN despachos d ON d.id_solicitud_origen = s.id
        WHERE s.estado = 'aprobado'
        AND d.estado IN ('aceptado', 'en_transito', 'entregado')
    ");
    $stmt->execute();
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Solicitudes aprobadas con despachos en tránsito/entregados: " . count($solicitudes) . "\n\n";
    
    $finalizadas = 0;
    foreach ($solicitudes as $sol) {
        $resultado = ModeloSolicitudesStock::mdlVerificarYFinalizarSolicitud($sol['id'], null, null);
        if ($resultado) {
            echo "✅ {$sol['numero_solicitud']} (id={$sol['id']}) -> FINALIZADO\n";
            $finalizadas++;
        } else {
            echo "⏳ {$sol['numero_solicitud']} (id={$sol['id']}) -> Aún no completa (revisar cantidades)\n";
        }
    }
    
    echo "\n=== COMPLETADO ===\n";
    echo "Solicitudes finalizadas: $finalizadas\n";
    echo "\n⚠️ Elimina o renombra este archivo por seguridad.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
