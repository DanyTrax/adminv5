<?php
/*=============================================
PUNTO DE ENTRADA PRINCIPAL - AdminV5
Sistema con bypass para instalación
=============================================*/

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =============================================
// DETECTOR DE CONTEXTO: INSTALACIÓN vs SISTEMA
// =============================================

$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$base_path = dirname($_SERVER['SCRIPT_NAME']);

// Normalizar la ruta base
if ($base_path === '/') {
    $base_path = '';
}

// Detectar si se está accediendo a instalación
if (preg_match('#^' . preg_quote($base_path, '#') . '/instalacion/?(.*)$#', $request_uri, $matches)) {
    
    // ✅ MODO INSTALACIÓN
    $instalacion_file = $matches[1] ?? 'index.php';
    
    // Si la ruta está vacía o termina en /, usar index.php
    if (empty($instalacion_file) || $instalacion_file === '/' || is_dir(__DIR__ . '/instalacion/' . $instalacion_file)) {
        $instalacion_file = 'index.php';
    }
    
    $archivo_instalacion = __DIR__ . '/instalacion/' . $instalacion_file;
    
    // Verificaciones de seguridad
    if (file_exists($archivo_instalacion) && 
        is_file($archivo_instalacion) &&
        strpos(realpath($archivo_instalacion), realpath(__DIR__ . '/instalacion/')) === 0) {
        
        // Log de acceso a instalación
        error_log("INSTALACION ACCESS: {$request_uri} -> {$archivo_instalacion}");
        
        // Cambiar directorio de trabajo y ejecutar
        chdir(__DIR__ . '/instalacion/');
        include $archivo_instalacion;
        exit;
        
    } else {
        // Archivo de instalación no encontrado
        http_response_code(404);
        echo json_encode([
            'error' => 'Archivo de instalación no encontrado',
            'archivo' => $instalacion_file,
            'ruta_completa' => $archivo_instalacion
        ]);
        exit;
    }
}

// =============================================
// MODO SISTEMA PRINCIPAL (CÓDIGO ORIGINAL)
// =============================================

require_once "src/Utils.php";

require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/clientes.controlador.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/cotizaciones.controlador.php";
require_once "controladores/contabilidad.controlador.php";
require_once "controladores/medios-pago.controlador.php";
require_once "controladores/catalogo-maestro.controlador.php";
require_once "controladores/sucursales.controlador.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "extensiones/vendor/autoload.php";
require_once "modelos/cotizaciones.modelo.php";
require_once "modelos/contabilidad.modelo.php";
require_once "modelos/medios-pago.modelo.php";
require_once "modelos/catalogo-maestro.modelo.php";
require_once "modelos/sucursales.modelo.php";

require_once "src/MedioPago.php";
require_once "src/FormaPago.php";

// Ejecutar sistema principal
$plantilla = new ControladorPlantilla();
$plantilla->ctrPlantilla();
?>