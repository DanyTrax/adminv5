<?php
/*=============================================
PUNTO DE ENTRADA PRINCIPAL - AdminV5
Sistema con bypass para instalación CORREGIDO
=============================================*/

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =============================================
// DETECTOR DE CONTEXTO: INSTALACIÓN vs SISTEMA
// =============================================

$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';

// Limpiar parámetros GET de la URI para el análisis
$clean_uri = strtok($request_uri, '?');

// Log de debug para el routing
error_log("ROUTING DEBUG: REQUEST_URI = {$request_uri}");
error_log("ROUTING DEBUG: CLEAN_URI = {$clean_uri}");
error_log("ROUTING DEBUG: SCRIPT_NAME = {$script_name}");

// Detectar si se está accediendo a instalación
if (preg_match('#/instalacion(/.*)?$#', $clean_uri, $matches)) {
    
    // ✅ MODO INSTALACIÓN DETECTADO
    error_log("ROUTING: Modo instalación detectado");
    
    $instalacion_path = __DIR__ . '/instalacion/';
    
    // Verificar que la carpeta de instalación existe
    if (!is_dir($instalacion_path)) {
        http_response_code(404);
        die(json_encode([
            'error' => 'Directorio de instalación no encontrado',
            'path' => $instalacion_path
        ]));
    }
    
    // Determinar qué archivo de instalación cargar
    $instalacion_subpath = $matches[1] ?? '/';
    
    if ($instalacion_subpath === '/' || empty($instalacion_subpath)) {
        $instalacion_file = 'index.php';
    } else {
        $instalacion_file = ltrim($instalacion_subpath, '/');
        
        // Si termina en /, agregar index.php
        if (substr($instalacion_file, -1) === '/') {
            $instalacion_file .= 'index.php';
        }
    }
    
    $archivo_instalacion = $instalacion_path . $instalacion_file;
    
    error_log("ROUTING: Archivo instalación = {$archivo_instalacion}");
    
    // Verificaciones de seguridad
    $instalacion_realpath = realpath($instalacion_path);
    $archivo_realpath = realpath($archivo_instalacion);
    
    if (file_exists($archivo_instalacion) && 
        is_file($archivo_instalacion) &&
        $archivo_realpath &&
        strpos($archivo_realpath, $instalacion_realpath) === 0) {
        
        error_log("ROUTING: Ejecutando archivo de instalación = {$archivo_instalacion}");
        
        // Cambiar directorio de trabajo y ejecutar
        $old_cwd = getcwd();
        chdir($instalacion_path);
        
        // Preservar parámetros GET originales
        if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
            $query_string = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            if ($query_string) {
                parse_str($query_string, $_GET);
                $_SERVER['QUERY_STRING'] = $query_string;
            }
        }
        
        include $archivo_instalacion;
        
        // Restaurar directorio
        chdir($old_cwd);
        exit;
        
    } else {
        // Archivo de instalación no encontrado
        error_log("ROUTING ERROR: Archivo no encontrado = {$archivo_instalacion}");
        http_response_code(404);
        die(json_encode([
            'error' => 'Archivo de instalación no encontrado',
            'archivo_solicitado' => $instalacion_file,
            'ruta_completa' => $archivo_instalacion,
            'archivo_existe' => file_exists($archivo_instalacion),
            'es_archivo' => is_file($archivo_instalacion),
            'permisos_legibles' => is_readable($archivo_instalacion)
        ]));
    }
}

// =============================================
// MODO SISTEMA PRINCIPAL (CÓDIGO ORIGINAL)
// =============================================

error_log("ROUTING: Modo sistema principal");

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