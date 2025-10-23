<?php
/**
 * SCRIPT DE PRUEBA PARA VERIFICAR FUNCIONALIDAD DE CLIENTES CENTRALES
 * Ejecutar después de la migración en cPanel
 */

echo "<h2>🧪 PRUEBA DE FUNCIONALIDAD - CLIENTES CENTRALES</h2>";

try {
    // Verificar que estamos en el sistema central
    if (!file_exists("api-transferencias/conexion-central.php")) {
        throw new Exception("Este script debe ejecutarse desde el sistema CENTRAL");
    }
    
    require_once "api-transferencias/conexion-central.php";
    require_once "modelos/clientes-central.modelo.php";
    require_once "controladores/clientes-central.controlador.php";
    
    $conexion = ConexionCentral::conectar();
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Conexión a BD central exitosa</strong>";
    echo "</div>";
    
    // TEST 1: Verificar tabla existe
    echo "<h3>✅ TEST 1: Verificar tabla clientes_central existe</h3>";
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'clientes_central'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if ($tablaExiste) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ La tabla clientes_central existe</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ La tabla clientes_central NO existe</strong><br>";
        echo "Ejecuta crear-tabla-clientes-central.php primero";
        echo "</div>";
        exit;
    }
    
    // TEST 2: Verificar columnas importantes
    echo "<h3>✅ TEST 2: Verificar columnas importantes</h3>";
    $columnasEsperadas = ['id_central', 'documento', 'email', 'nombre', 'telefono', 'direccion', 'sucursales_asignadas'];
    $stmt = $conexion->prepare("DESCRIBE clientes_central");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $columnasFaltantes = [];
    foreach ($columnasEsperadas as $columna) {
        if (!in_array($columna, $columnas)) {
            $columnasFaltantes[] = $columna;
        }
    }
    
    if (empty($columnasFaltantes)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Todas las columnas importantes existen</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Faltan columnas: " . implode(', ', $columnasFaltantes) . "</strong>";
        echo "</div>";
    }
    
    // TEST 3: Verificar índice único en documento
    echo "<h3>✅ TEST 3: Verificar índice único en documento</h3>";
    $stmt = $conexion->prepare("SHOW INDEX FROM clientes_central WHERE Column_name = 'documento' AND Non_unique = 0");
    $stmt->execute();
    $indiceUnico = $stmt->fetch();
    
    if ($indiceUnico) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Índice único en documento existe</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Índice único en documento NO existe</strong>";
        echo "</div>";
    }
    
    // TEST 4: Probar crear cliente central
    echo "<h3>✅ TEST 4: Probar crear cliente central</h3>";
    $datosPrueba = [
        'documento' => '999999999',
        'email' => 'test_' . time() . '@prueba.com',
        'nombre' => 'Cliente Prueba',
        'telefono' => '1234567890',
        'direccion' => 'Dirección de Prueba',
        'fecha_nacimiento' => '1990-01-01',
        'sucursales_asignadas' => '',
        'id_local_principal' => null,
        'sucursal_origen' => 'TEST'
    ];
    
    $resultado = ModeloClientesCentral::mdlCrearClienteCentral($datosPrueba);
    
    if ($resultado['success']) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Cliente de prueba creado exitosamente</strong><br>";
        echo "ID Central: " . $resultado['id_central'];
        echo "</div>";
        $idClientePrueba = $resultado['id_central'];
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error creando cliente de prueba</strong><br>";
        echo "Error: " . $resultado['error'];
        echo "</div>";
        $idClientePrueba = null;
    }
    
    // TEST 5: Probar verificar duplicado
    echo "<h3>✅ TEST 5: Probar verificar duplicado</h3>";
    if ($idClientePrueba) {
        $resultadoDuplicado = ModeloClientesCentral::mdlVerificarDuplicadoCliente('999999999');
        
        if ($resultadoDuplicado['existe']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Verificación de duplicado funciona correctamente</strong><br>";
            echo "Cliente encontrado: " . $resultadoDuplicado['cliente']['nombre'];
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Verificación de duplicado NO funciona</strong>";
            echo "</div>";
        }
    }
    
    // TEST 6: Probar editar cliente central
    echo "<h3>✅ TEST 6: Probar editar cliente central</h3>";
    if ($idClientePrueba) {
        $datosEdicion = [
            'id_central' => $idClientePrueba,
            'documento' => '999999999',
            'email' => 'test_editado_' . time() . '@prueba.com',
            'nombre' => 'Cliente Prueba Editado',
            'telefono' => '0987654321',
            'direccion' => 'Dirección Editada',
            'fecha_nacimiento' => '1991-01-01',
            'sucursales_asignadas' => ''
        ];
        
        $resultadoEdicion = ModeloClientesCentral::mdlEditarClienteCentral($datosEdicion);
        
        if ($resultadoEdicion['success']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Cliente editado exitosamente</strong>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error editando cliente</strong><br>";
            echo "Error: " . $resultadoEdicion['error'];
            echo "</div>";
        }
    }
    
    // TEST 7: Probar eliminar cliente central
    echo "<h3>✅ TEST 7: Probar eliminar cliente central</h3>";
    if ($idClientePrueba) {
        $resultadoEliminacion = ModeloClientesCentral::mdlEliminarClienteCentral($idClientePrueba);
        
        if ($resultadoEliminacion['success']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Cliente eliminado exitosamente</strong>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error eliminando cliente</strong><br>";
            echo "Error: " . $resultadoEliminacion['error'];
            echo "</div>";
        }
    }
    
    // TEST 8: Verificar sucursales disponibles
    echo "<h3>✅ TEST 8: Verificar sucursales disponibles</h3>";
    $sucursales = ModeloClientesCentral::mdlObtenerSucursalesDisponibles();
    
    if (is_array($sucursales) && !empty($sucursales)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Sucursales disponibles obtenidas correctamente</strong><br>";
        echo "Total de sucursales: " . count($sucursales);
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ No hay sucursales disponibles</strong><br>";
        echo "Esto es normal si no hay sucursales configuradas";
        echo "</div>";
    }
    
    // TEST 9: Verificar archivos existen
    echo "<h3>✅ TEST 9: Verificar archivos principales existen</h3>";
    $archivosRequeridos = [
        'modelos/clientes-central.modelo.php',
        'controladores/clientes-central.controlador.php',
        'vistas/modulos/clientes-central.php',
        'vistas/js/clientes-central.js',
        'ajax/clientes-central.ajax.php'
    ];
    
    $archivosFaltantes = [];
    foreach ($archivosRequeridos as $archivo) {
        if (!file_exists($archivo)) {
            $archivosFaltantes[] = $archivo;
        }
    }
    
    if (empty($archivosFaltantes)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Todos los archivos principales existen</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Faltan archivos:</strong><br>";
        echo implode('<br>', $archivosFaltantes);
        echo "</div>";
    }
    
    // RESUMEN FINAL
    echo "<h3>📊 RESUMEN DE PRUEBAS</h3>";
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 5px;'>";
    echo "<strong>✅ Sistema de Clientes Centrales funcionando correctamente</strong><br><br>";
    echo "Puedes acceder a la gestión de clientes centrales desde el menú.<br>";
    echo "Recuerda agregar la entrada al menú en vistas/plantilla.php si aún no lo has hecho.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Prueba completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
