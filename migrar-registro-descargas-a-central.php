<?php
/*=============================================
MIGRAR TABLA REGISTRO_DESCARGAS_STOCK_TRANSITO A BD CENTRAL
=============================================*/

echo "🚀 Iniciando migración de registro_descargas_stock_transito a BD Central\n";
echo "📋 Objetivo: Mover historial de descargas de BD local a BD central\n";
echo "🎯 Beneficio: Historial universal accesible desde todas las sucursales\n\n";

// Incluir conexiones
require_once "modelos/conexion.php";

try {
    // ========================================
    // 1. VERIFICAR CONEXIONES
    // ========================================
    echo "🔍 Verificando conexiones...\n";
    
    // Conexión local
    $conexionLocal = Conexion::conectar();
    echo "✅ Conexión local establecida\n";
    
    // Conexión central
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión central establecida\n\n";
    
    // ========================================
    // 2. VERIFICAR TABLA EN BD LOCAL
    // ========================================
    echo "🔍 Verificando tabla en BD local...\n";
    
    $sqlVerificarLocal = "SHOW TABLES LIKE 'registro_descargas_stock_transito'";
    $stmtLocal = $conexionLocal->prepare($sqlVerificarLocal);
    $stmtLocal->execute();
    $tablaLocal = $stmtLocal->fetch();
    
    if (!$tablaLocal) {
        echo "❌ ERROR: La tabla 'registro_descargas_stock_transito' no existe en BD local\n";
        exit;
    }
    echo "✅ Tabla encontrada en BD local\n";
    
    // ========================================
    // 3. VERIFICAR TABLA EN BD CENTRAL
    // ========================================
    echo "🔍 Verificando tabla en BD central...\n";
    
    $sqlVerificarCentral = "SHOW TABLES LIKE 'registro_descargas_stock_transito'";
    $stmtCentral = $conexionCentral->prepare($sqlVerificarCentral);
    $stmtCentral->execute();
    $tablaCentral = $stmtCentral->fetch();
    
    if ($tablaCentral) {
        echo "⚠️ La tabla ya existe en BD central\n";
        echo "¿Desea recrearla? (Esto eliminará todos los datos existentes)\n";
        echo "Presione Enter para continuar o Ctrl+C para cancelar...\n";
        readline();
        
        // Eliminar tabla existente
        $sqlEliminar = "DROP TABLE registro_descargas_stock_transito";
        $conexionCentral->exec($sqlEliminar);
        echo "🗑️ Tabla existente eliminada\n";
    }
    
    // ========================================
    // 4. OBTENER ESTRUCTURA DE LA TABLA LOCAL
    // ========================================
    echo "🔍 Obteniendo estructura de la tabla local...\n";
    
    $sqlEstructura = "SHOW CREATE TABLE registro_descargas_stock_transito";
    $stmtEstructura = $conexionLocal->prepare($sqlEstructura);
    $stmtEstructura->execute();
    $estructura = $stmtEstructura->fetch(PDO::FETCH_ASSOC);
    
    $sqlCrearTabla = $estructura['Create Table'];
    echo "✅ Estructura obtenida\n";
    
    // ========================================
    // 5. CREAR TABLA EN BD CENTRAL
    // ========================================
    echo "🏗️ Creando tabla en BD central...\n";
    
    $conexionCentral->exec($sqlCrearTabla);
    echo "✅ Tabla creada en BD central\n";
    
    // ========================================
    // 6. MIGRAR DATOS EXISTENTES
    // ========================================
    echo "📦 Migrando datos existentes...\n";
    
    // Obtener todos los datos de la tabla local
    $sqlDatos = "SELECT * FROM registro_descargas_stock_transito";
    $stmtDatos = $conexionLocal->prepare($sqlDatos);
    $stmtDatos->execute();
    $datos = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);
    
    $totalRegistros = count($datos);
    echo "📊 Total de registros a migrar: $totalRegistros\n";
    
    if ($totalRegistros > 0) {
        // Preparar INSERT para BD central
        $sqlInsert = "INSERT INTO registro_descargas_stock_transito (
            id, fecha_descarga, codigo_producto, descripcion_producto, 
            cantidad_descargada, usuario_id, usuario_nombre, transportador_id, 
            transportador_nombre, sucursal_id, sucursal_nombre, numero_despacho, 
            observaciones, ip_address, user_agent, created_at, updated_at
        ) VALUES (
            :id, :fecha_descarga, :codigo_producto, :descripcion_producto, 
            :cantidad_descargada, :usuario_id, :usuario_nombre, :transportador_id, 
            :transportador_nombre, :sucursal_id, :sucursal_nombre, :numero_despacho, 
            :observaciones, :ip_address, :user_agent, :created_at, :updated_at
        )";
        
        $stmtInsert = $conexionCentral->prepare($sqlInsert);
        
        $migrados = 0;
        foreach ($datos as $registro) {
            $stmtInsert->execute($registro);
            $migrados++;
            
            if ($migrados % 100 == 0) {
                echo "📦 Migrados: $migrados/$totalRegistros\n";
            }
        }
        
        echo "✅ Datos migrados exitosamente: $migrados registros\n";
    } else {
        echo "ℹ️ No hay datos para migrar\n";
    }
    
    // ========================================
    // 7. VERIFICAR MIGRACIÓN
    // ========================================
    echo "🔍 Verificando migración...\n";
    
    $sqlVerificar = "SELECT COUNT(*) as total FROM registro_descargas_stock_transito";
    $stmtVerificar = $conexionCentral->prepare($sqlVerificar);
    $stmtVerificar->execute();
    $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Registros en BD central: " . $resultado['total'] . "\n";
    
    // ========================================
    // 8. CREAR BACKUP DE TABLA LOCAL
    // ========================================
    echo "💾 Creando backup de tabla local...\n";
    
    $sqlBackup = "CREATE TABLE registro_descargas_stock_transito_backup AS 
                  SELECT * FROM registro_descargas_stock_transito";
    $conexionLocal->exec($sqlBackup);
    echo "✅ Backup creado: registro_descargas_stock_transito_backup\n";
    
    // ========================================
    // 9. ELIMINAR TABLA LOCAL
    // ========================================
    echo "🗑️ Eliminando tabla local...\n";
    
    $sqlEliminarLocal = "DROP TABLE registro_descargas_stock_transito";
    $conexionLocal->exec($sqlEliminarLocal);
    echo "✅ Tabla local eliminada\n";
    
    // ========================================
    // 10. RESUMEN FINAL
    // ========================================
    echo "\n🎉 ¡MIGRACIÓN COMPLETADA EXITOSAMENTE!\n";
    echo "==========================================\n";
    echo "✅ Tabla migrada de BD local a BD central\n";
    echo "✅ Estructura preservada\n";
    echo "✅ Datos migrados: $migrados registros\n";
    echo "✅ Backup creado en BD local\n";
    echo "✅ Tabla local eliminada\n";
    echo "✅ Historial ahora es universal\n\n";
    
    echo "📋 PRÓXIMOS PASOS:\n";
    echo "1. Actualizar código para usar BD central\n";
    echo "2. Probar funcionalidad en todas las sucursales\n";
    echo "3. Verificar que el historial se ve correctamente\n";
    echo "4. Eliminar backup local si todo funciona bien\n\n";
    
    echo "🔧 Archivos a actualizar:\n";
    echo "- modelos/registro-descargas-simple.modelo.php\n";
    echo "- ajax/datatable-registro-descargas-funcional.ajax.php\n";
    echo "- vistas/modulos/descargar-registro-descargas.php\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR durante la migración: " . $e->getMessage() . "\n";
    echo "🔧 Revise los logs para más detalles\n";
    exit(1);
}

echo "✅ Migración completada\n";
?>
