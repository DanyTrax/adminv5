<?php
/**
 * Ejecuta migraciones SQL en la base CENTRAL
 * Uso: dominio.com/sqlejecucion.php?clave=TU_CLAVE_SECRETA
 * 
 * IMPORTANTE: Cambia TU_CLAVE_SECRETA por una clave y elimina este archivo después de usarlo
 */
session_start();

// Seguridad: solo ejecutar con clave o si está logueado como admin
$clavePermitida = "migrar2025"; // CAMBIA ESTA CLAVE antes de usar
$claveRecibida = $_GET['clave'] ?? '';

if ($claveRecibida !== $clavePermitida) {
    if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
        die('Acceso denegado. Usa: ?clave=TU_CLAVE o inicia sesión como Administrador.');
    }
}

require_once __DIR__ . "/api-transferencias/conexion-central.php";

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Ejecutando migración: trazabilidad-solicitud-despacho</h2>";
echo "<pre>";

$resultados = [];

try {
    $conexion = ConexionCentral::conectar();
    
    // 1. stock_transito: id_solicitud_origen
    try {
        $conexion->exec("ALTER TABLE stock_transito ADD COLUMN id_solicitud_origen INT NULL AFTER id_despacho_origen");
        $resultados[] = "OK: stock_transito.id_solicitud_origen agregada";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $resultados[] = "YA EXISTE: stock_transito.id_solicitud_origen";
        } else {
            throw $e;
        }
    }
    
    // 2. stock_transito: numero_solicitud
    try {
        $conexion->exec("ALTER TABLE stock_transito ADD COLUMN numero_solicitud VARCHAR(50) NULL AFTER id_solicitud_origen");
        $resultados[] = "OK: stock_transito.numero_solicitud agregada";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $resultados[] = "YA EXISTE: stock_transito.numero_solicitud";
        } else {
            throw $e;
        }
    }
    
    // 3. stock_transito: índice
    try {
        $conexion->exec("ALTER TABLE stock_transito ADD INDEX idx_solicitud_transportador (id_solicitud_origen, transportador_id, codigo_producto)");
        $resultados[] = "OK: índice idx_solicitud_transportador creado";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            $resultados[] = "YA EXISTE: índice idx_solicitud_transportador";
        } else {
            throw $e;
        }
    }
    
    // 4. registro_descargas_stock_transito: id_solicitud_origen
    try {
        $conexion->exec("ALTER TABLE registro_descargas_stock_transito ADD COLUMN id_solicitud_origen INT NULL AFTER numero_despacho");
        $resultados[] = "OK: registro_descargas_stock_transito.id_solicitud_origen agregada";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $resultados[] = "YA EXISTE: registro_descargas_stock_transito.id_solicitud_origen";
        } else {
            throw $e;
        }
    }
    
    // 5. registro_descargas_stock_transito: numero_solicitud
    try {
        $conexion->exec("ALTER TABLE registro_descargas_stock_transito ADD COLUMN numero_solicitud VARCHAR(50) NULL AFTER id_solicitud_origen");
        $resultados[] = "OK: registro_descargas_stock_transito.numero_solicitud agregada";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            $resultados[] = "YA EXISTE: registro_descargas_stock_transito.numero_solicitud";
        } else {
            throw $e;
        }
    }
    
    echo "\n=== MIGRACIÓN COMPLETADA ===\n\n";
    foreach ($resultados as $r) {
        echo $r . "\n";
    }
    echo "\n✅ Base CENTRAL actualizada correctamente.\n";
    echo "\n⚠️ Elimina este archivo (sqlejecucion.php) por seguridad.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nResultados hasta el error:\n";
    foreach ($resultados as $r) {
        echo $r . "\n";
    }
}

echo "</pre>";
