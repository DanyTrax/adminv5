<?php
/*=============================================
ACTUALIZAR CÓDIGO PARA USAR BD CENTRAL
=============================================*/

echo "🔧 Actualizando código para usar BD central\n";
echo "📋 Objetivo: Cambiar todas las referencias de BD local a BD central\n";
echo "🎯 Beneficio: Historial universal accesible desde todas las sucursales\n\n";

// ========================================
// 1. ACTUALIZAR MODELO
// ========================================
echo "🔍 Actualizando modelo...\n";

$archivoModelo = "modelos/registro-descargas-simple.modelo.php";
if (file_exists($archivoModelo)) {
    $contenido = file_get_contents($archivoModelo);
    
    // Cambiar Conexion::conectar() por Conexion::conectarCentral()
    $contenido = str_replace(
        'Conexion::conectar()',
        'Conexion::conectarCentral()',
        $contenido
    );
    
    file_put_contents($archivoModelo, $contenido);
    echo "✅ Modelo actualizado: $archivoModelo\n";
} else {
    echo "⚠️ Archivo no encontrado: $archivoModelo\n";
}

// ========================================
// 2. ACTUALIZAR AJAX DATATABLE
// ========================================
echo "🔍 Actualizando AJAX datatable...\n";

$archivoAjax = "ajax/datatable-registro-descargas-funcional.ajax.php";
if (file_exists($archivoAjax)) {
    $contenido = file_get_contents($archivoAjax);
    
    // Cambiar Conexion::conectar() por Conexion::conectarCentral()
    $contenido = str_replace(
        'Conexion::conectar()',
        'Conexion::conectarCentral()',
        $contenido
    );
    
    file_put_contents($archivoAjax, $contenido);
    echo "✅ AJAX datatable actualizado: $archivoAjax\n";
} else {
    echo "⚠️ Archivo no encontrado: $archivoAjax\n";
}

// ========================================
// 3. ACTUALIZAR DESCARGA EXCEL
// ========================================
echo "🔍 Actualizando descarga Excel...\n";

$archivoExcel = "vistas/modulos/descargar-registro-descargas.php";
if (file_exists($archivoExcel)) {
    $contenido = file_get_contents($archivoExcel);
    
    // Cambiar Conexion::conectar() por Conexion::conectarCentral()
    $contenido = str_replace(
        'Conexion::conectar()',
        'Conexion::conectarCentral()',
        $contenido
    );
    
    file_put_contents($archivoExcel, $contenido);
    echo "✅ Descarga Excel actualizada: $archivoExcel\n";
} else {
    echo "⚠️ Archivo no encontrado: $archivoExcel\n";
}

// ========================================
// 4. ACTUALIZAR AJAX REGISTRO
// ========================================
echo "🔍 Actualizando AJAX registro...\n";

$archivoRegistro = "ajax/registro-descargas-simple.ajax.php";
if (file_exists($archivoRegistro)) {
    $contenido = file_get_contents($archivoRegistro);
    
    // Cambiar Conexion::conectar() por Conexion::conectarCentral()
    $contenido = str_replace(
        'Conexion::conectar()',
        'Conexion::conectarCentral()',
        $contenido
    );
    
    file_put_contents($archivoRegistro, $contenido);
    echo "✅ AJAX registro actualizado: $archivoRegistro\n";
} else {
    echo "⚠️ Archivo no encontrado: $archivoRegistro\n";
}

// ========================================
// 5. ACTUALIZAR CONTROLADOR
// ========================================
echo "🔍 Actualizando controlador...\n";

$archivoControlador = "controladores/registro-descargas-simple.controlador.php";
if (file_exists($archivoControlador)) {
    $contenido = file_get_contents($archivoControlador);
    
    // Cambiar Conexion::conectar() por Conexion::conectarCentral()
    $contenido = str_replace(
        'Conexion::conectar()',
        'Conexion::conectarCentral()',
        $contenido
    );
    
    file_put_contents($archivoControlador, $contenido);
    echo "✅ Controlador actualizado: $archivoControlador\n";
} else {
    echo "⚠️ Archivo no encontrado: $archivoControlador\n";
}

// ========================================
// 6. VERIFICAR CONEXIÓN CENTRAL
// ========================================
echo "🔍 Verificando conexión central...\n";

try {
    require_once "modelos/conexion.php";
    $conexion = Conexion::conectarCentral();
    echo "✅ Conexión central verificada\n";
    
    // Verificar que la tabla existe
    $sql = "SHOW TABLES LIKE 'registro_descargas_stock_transito'";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "✅ Tabla encontrada en BD central\n";
        
        // Contar registros
        $sql = "SELECT COUNT(*) as total FROM registro_descargas_stock_transito";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 Total de registros: " . $resultado['total'] . "\n";
    } else {
        echo "❌ ERROR: Tabla no encontrada en BD central\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// ========================================
// 7. RESUMEN FINAL
// ========================================
echo "\n🎉 ¡ACTUALIZACIÓN COMPLETADA!\n";
echo "==========================================\n";
echo "✅ Código actualizado para usar BD central\n";
echo "✅ Historial ahora es universal\n";
echo "✅ Accesible desde todas las sucursales\n\n";

echo "📋 ARCHIVOS ACTUALIZADOS:\n";
echo "- modelos/registro-descargas-simple.modelo.php\n";
echo "- ajax/datatable-registro-descargas-funcional.ajax.php\n";
echo "- vistas/modulos/descargar-registro-descargas.php\n";
echo "- ajax/registro-descargas-simple.ajax.php\n";
echo "- controladores/registro-descargas-simple.controlador.php\n\n";

echo "🧪 PRÓXIMOS PASOS:\n";
echo "1. Probar funcionalidad en todas las sucursales\n";
echo "2. Verificar que el historial se ve correctamente\n";
echo "3. Confirmar que las descargas se registran en BD central\n";
echo "4. Verificar que el historial es universal\n\n";

echo "✅ Actualización completada\n";
?>
