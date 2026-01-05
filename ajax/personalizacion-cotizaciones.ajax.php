<?php

session_start();

require_once __DIR__ . "/../controladores/personalizacion-cotizaciones.controlador.php";
require_once __DIR__ . "/../modelos/personalizacion-cotizaciones.modelo.php";

if (isset($_POST["accion"])) {
    
    switch ($_POST["accion"]) {
        
        case "obtener_configuracion":
            
            if (isset($_POST["id"])) {
                $configuracion = ControladorPersonalizacionCotizaciones::ctrObtenerConfiguracion($_POST["id"]);
                
                if ($configuracion) {
                    echo json_encode([
                        "success" => true,
                        "configuracion" => $configuracion
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "mensaje" => "Configuración no encontrada"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "ID no proporcionado"
                ]);
            }
            
            break;
            
        case "obtener_sucursales":
            
            $sucursales = ControladorPersonalizacionCotizaciones::ctrObtenerSucursales();
            
            echo json_encode([
                "success" => true,
                "sucursales" => $sucursales
            ]);
            
            break;
            
        case "subir_imagen":
            
            if (isset($_FILES["imagen"]) && isset($_POST["tipo"])) {
                
                $tipo = $_POST["tipo"];
                $archivo = $_FILES["imagen"];
                
                // Validar tipo de archivo
                $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($archivo['type'], $tiposPermitidos)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Tipo de archivo no permitido. Solo se permiten: JPEG, PNG, GIF, WebP'
                    ]);
                    exit;
                }
                
                // Validar tamaño (máximo 5MB)
                if ($archivo['size'] > 5 * 1024 * 1024) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El archivo es demasiado grande. Máximo 5MB'
                    ]);
                    exit;
                }
                
                // Crear directorio si no existe
                $directorio = __DIR__ . "/../vistas/img/cotizacion/";
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0755, true);
                }
                
                // Generar nombre único
                $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                $nombreArchivo = 'logo-cotizacion_' . time() . '.' . $extension;
                $rutaCompleta = $directorio . $nombreArchivo;
                
                // Redimensionar y guardar imagen
                if (redimensionarImagen($archivo['tmp_name'], $rutaCompleta, 'logo-cotizacion')) {
                    
                    // Actualizar la configuración en la base de datos
                    $rutaRelativa = "vistas/img/cotizacion/" . $nombreArchivo;
                    
                    // Obtener ID de sucursal actual
                    $idSucursal = ModeloPersonalizacionCotizaciones::mdlObtenerIdSucursalActual();
                    
                    if (actualizarImagenEnBD($rutaRelativa, $idSucursal)) {
                        echo json_encode([
                            'success' => true,
                            'ruta_imagen' => $rutaRelativa,
                            'mensaje' => 'Imagen subida y aplicada correctamente'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'error' => 'Error al actualizar la configuración en la base de datos'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al procesar la imagen'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Datos incompletos'
                ]);
            }
            
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "mensaje" => "Acción no válida"
            ]);
            break;
    }
    
} else {
    echo json_encode([
        "success" => false,
        "mensaje" => "No se especificó una acción"
    ]);
}

/*=============================================
FUNCIÓN PARA REDIMENSIONAR IMAGEN
=============================================*/
function redimensionarImagen($archivoOrigen, $archivoDestino, $tipo) {
    
    // Obtener dimensiones de la imagen original
    $infoImagen = getimagesize($archivoOrigen);
    if (!$infoImagen) {
        return false;
    }
    
    $anchoOriginal = $infoImagen[0];
    $altoOriginal = $infoImagen[1];
    $tipoImagen = $infoImagen[2];
    
    // Definir dimensiones máximas según el tipo (manteniendo proporción)
    $dimensionesMaximas = [
        'logo-cotizacion' => ['max_ancho' => 400, 'max_alto' => 200]
    ];
    
    if (!isset($dimensionesMaximas[$tipo])) {
        return false;
    }
    
    $maxAncho = $dimensionesMaximas[$tipo]['max_ancho'];
    $maxAlto = $dimensionesMaximas[$tipo]['max_alto'];
    
    // Calcular dimensiones manteniendo la proporción original
    $proporcionOriginal = $anchoOriginal / $altoOriginal;
    $proporcionMaxima = $maxAncho / $maxAlto;
    
    if ($proporcionOriginal > $proporcionMaxima) {
        // La imagen es más ancha, limitar por ancho
        $anchoDestino = $maxAncho;
        $altoDestino = (int)($maxAncho / $proporcionOriginal);
    } else {
        // La imagen es más alta, limitar por alto
        $altoDestino = $maxAlto;
        $anchoDestino = (int)($maxAlto * $proporcionOriginal);
    }
    
    // Crear imagen desde archivo
    switch ($tipoImagen) {
        case IMAGETYPE_JPEG:
            $imagenOriginal = imagecreatefromjpeg($archivoOrigen);
            break;
        case IMAGETYPE_PNG:
            $imagenOriginal = imagecreatefrompng($archivoOrigen);
            break;
        case IMAGETYPE_GIF:
            $imagenOriginal = imagecreatefromgif($archivoOrigen);
            break;
        case IMAGETYPE_WEBP:
            $imagenOriginal = imagecreatefromwebp($archivoOrigen);
            break;
        default:
            return false;
    }
    
    if (!$imagenOriginal) {
        return false;
    }
    
    // Crear imagen redimensionada
    $imagenRedimensionada = imagecreatetruecolor($anchoDestino, $altoDestino);
    
    // Preservar transparencia para PNG
    if ($tipoImagen == IMAGETYPE_PNG) {
        imagealphablending($imagenRedimensionada, false);
        imagesavealpha($imagenRedimensionada, true);
        $transparente = imagecolorallocatealpha($imagenRedimensionada, 255, 255, 255, 127);
        imagefill($imagenRedimensionada, 0, 0, $transparente);
    } else {
        // Para otros formatos, usar fondo blanco
        $blanco = imagecolorallocate($imagenRedimensionada, 255, 255, 255);
        imagefill($imagenRedimensionada, 0, 0, $blanco);
    }
    
    // Redimensionar manteniendo proporción
    imagecopyresampled(
        $imagenRedimensionada, $imagenOriginal,
        0, 0, 0, 0,
        $anchoDestino, $altoDestino,
        $anchoOriginal, $altoOriginal
    );
    
    // Guardar imagen
    $resultado = false;
    switch ($tipoImagen) {
        case IMAGETYPE_JPEG:
            $resultado = imagejpeg($imagenRedimensionada, $archivoDestino, 90);
            break;
        case IMAGETYPE_PNG:
            $resultado = imagepng($imagenRedimensionada, $archivoDestino, 9);
            break;
        case IMAGETYPE_GIF:
            $resultado = imagegif($imagenRedimensionada, $archivoDestino);
            break;
        case IMAGETYPE_WEBP:
            $resultado = imagewebp($imagenRedimensionada, $archivoDestino, 90);
            break;
    }
    
    // Liberar memoria
    imagedestroy($imagenOriginal);
    imagedestroy($imagenRedimensionada);
    
    return $resultado;
}

/*=============================================
OBTENER ID SUCURSAL ACTUAL
=============================================*/
function obtenerIdSucursalActual() {
    
    try {
        require_once __DIR__ . "/../modelos/conexion.php";
        $conexionLocal = Conexion::conectar();
        
        $stmt = $conexionLocal->prepare("SELECT codigo_sucursal FROM sucursal_local LIMIT 1");
        $stmt->execute();
        $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sucursalLocal || empty($sucursalLocal['codigo_sucursal'])) {
            return null;
        }
        
        $codigoSucursal = $sucursalLocal['codigo_sucursal'];
        
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $conexionCentral = ConexionCentral::conectar();
        $stmt = $conexionCentral->prepare("SELECT id FROM sucursales WHERE codigo_sucursal = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$codigoSucursal]);
        $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $sucursal ? (int)$sucursal['id'] : null;
        
    } catch (Exception $e) {
        error_log("Error en obtenerIdSucursalActual: " . $e->getMessage());
        return null;
    }
}

/*=============================================
FUNCIÓN PARA ACTUALIZAR IMAGEN EN BASE DE DATOS (POR SUCURSAL)
=============================================*/
function actualizarImagenEnBD($rutaImagen, $idSucursal = null) {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        // Actualizar solo la configuración activa de la sucursal actual (o global si idSucursal es null)
        if ($idSucursal !== null) {
            // Buscar configuración activa de la sucursal específica
            $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET header_logo = ? WHERE activo = 1 AND id_sucursal = ?");
            $resultado = $stmt->execute([$rutaImagen, $idSucursal]);
            
            // Si no se actualizó ninguna fila, puede que no haya configuración específica de la sucursal
            if ($resultado && $stmt->rowCount() == 0) {
                // Obtener la configuración global activa como base
                $stmt = $conexion->prepare("SELECT * FROM personalizacion_cotizaciones WHERE activo = 1 AND id_sucursal IS NULL LIMIT 1");
                $stmt->execute();
                $configGlobal = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($configGlobal) {
                    // Desactivar todas las configuraciones de esta sucursal
                    $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET activo = 0 WHERE id_sucursal = ?");
                    $stmt->execute([$idSucursal]);
                    
                    // Obtener nombre de sucursal
                    $stmt = $conexion->prepare("SELECT nombre FROM sucursales WHERE id = ?");
                    $stmt->execute([$idSucursal]);
                    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
                    $nombreSucursal = $sucursal ? $sucursal['nombre'] : 'Sucursal #' . $idSucursal;
                    
                    // Crear nueva configuración para esta sucursal basada en la global
                    $stmt = $conexion->prepare("
                        INSERT INTO personalizacion_cotizaciones (
                            id_sucursal,
                            nombre_sucursal,
                            header_logo,
                            logo_width,
                            logo_align_vertical,
                            logo_align_horizontal,
                            header_nombre_empresa,
                            header_nit,
                            header_regimen,
                            header_servicios,
                            header_color_fondo,
                            header_color_texto,
                            header_font_size,
                            footer_direccion,
                            footer_telefono,
                            footer_movil,
                            footer_correo,
                            footer_color_fondo,
                            footer_color_texto,
                            footer_font_size,
                            activo,
                            usuario_creador
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
                    ");
                    
                    $datos = [
                        $idSucursal,
                        $nombreSucursal,
                        $rutaImagen,
                        $configGlobal['logo_width'] ?? 80,
                        $configGlobal['logo_align_vertical'] ?? 'center',
                        $configGlobal['logo_align_horizontal'] ?? 'center',
                        $configGlobal['header_nombre_empresa'],
                        $configGlobal['header_nit'],
                        $configGlobal['header_regimen'],
                        $configGlobal['header_servicios'],
                        $configGlobal['header_color_fondo'],
                        $configGlobal['header_color_texto'],
                        $configGlobal['header_font_size'] ?? 14,
                        $configGlobal['footer_direccion'],
                        $configGlobal['footer_telefono'],
                        $configGlobal['footer_movil'],
                        $configGlobal['footer_correo'],
                        $configGlobal['footer_color_fondo'],
                        $configGlobal['footer_color_texto'],
                        $configGlobal['footer_font_size'] ?? 16,
                        $_SESSION['id'] ?? 1
                    ];
                    
                    $resultado = $stmt->execute($datos);
                }
            }
        } else {
            // Actualizar configuración global activa
            $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET header_logo = ? WHERE activo = 1 AND id_sucursal IS NULL");
            $resultado = $stmt->execute([$rutaImagen]);
        }
        
        return $resultado;
        
    } catch (Exception $e) {
        error_log("Error actualizando imagen en BD: " . $e->getMessage());
        return false;
    }
}

?>

