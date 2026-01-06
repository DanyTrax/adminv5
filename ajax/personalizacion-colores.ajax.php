<?php
/*=============================================
AJAX PERSONALIZACIÓN DE COLORES CON SUBIDA DE IMÁGENES
=============================================*/

require_once "../controladores/personalizacion-colores-simplificado-funcional.controlador.php";

if (isset($_POST["accion"])) {
    
    switch ($_POST["accion"]) {
        
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
                $directorio = "../vistas/img/personalizacion/";
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0755, true);
                }
                
                // Generar nombre único
                $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $tipo . '_' . time() . '.' . $extension;
                $rutaCompleta = $directorio . $nombreArchivo;
                
                // Redimensionar y guardar imagen
                if (redimensionarImagen($archivo['tmp_name'], $rutaCompleta, $tipo)) {
                    
                    // Actualizar la configuración en la base de datos
                    $campoImagen = str_replace('-', '_', $tipo);
                    $rutaRelativa = "vistas/img/personalizacion/" . $nombreArchivo;
                    
                    // Obtener ID de sucursal: primero del POST, si no existe, de la sucursal actual
                    $idSucursal = !empty($_POST['id_sucursal']) ? (int)$_POST['id_sucursal'] : obtenerIdSucursalActual();
                    
                    if (actualizarImagenEnBD($campoImagen, $rutaRelativa, $idSucursal)) {
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
            
        case "obtener_configuracion":
            
            if (isset($_POST["id"])) {
                
                // Usar el controlador ya incluido en la línea 6
                $id = $_POST["id"];
                $resultado = ControladorPersonalizacionColores::ctrObtenerConfiguracion($id);
                
                echo json_encode($resultado);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'ID de configuración no proporcionado'
                ]);
                exit;
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No se especificó ninguna acción'
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
        'icono-pequeno' => ['max_ancho' => 50, 'max_alto' => 50],
        'logo-menu' => ['max_ancho' => 200, 'max_alto' => 50],
        'logo-login' => ['max_ancho' => 300, 'max_alto' => 150] // Aumentado para mejor proporción
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
        // Obtener código de sucursal desde BD local
        require_once "../modelos/conexion.php";
        $conexionLocal = Conexion::conectar();
        
        $stmt = $conexionLocal->prepare("SELECT codigo_sucursal FROM sucursal_local LIMIT 1");
        $stmt->execute();
        $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sucursalLocal || empty($sucursalLocal['codigo_sucursal'])) {
            return null;
        }
        
        $codigoSucursal = $sucursalLocal['codigo_sucursal'];
        
        // Obtener ID de sucursal desde BD central
        require_once "../api-transferencias/conexion-central.php";
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
function actualizarImagenEnBD($campoImagen, $rutaImagen, $idSucursal = null) {
    
    try {
        require_once "../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        // Actualizar solo la configuración activa de la sucursal actual (o global si idSucursal es null)
        if ($idSucursal !== null) {
            // Buscar configuración activa de la sucursal específica
            $sql = "UPDATE personalizacion_colores SET $campoImagen = ? WHERE activo = 1 AND id_sucursal = ?";
            $stmt = $conexion->prepare($sql);
            $resultado = $stmt->execute([$rutaImagen, $idSucursal]);
            
            // Si no se actualizó ninguna fila, puede que no haya configuración específica de la sucursal
            // En ese caso, crear una nueva configuración activa para esta sucursal
            if ($resultado && $stmt->rowCount() == 0) {
                // Obtener la configuración global activa como base
                $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1 AND id_sucursal IS NULL LIMIT 1");
                $stmt->execute();
                $configGlobal = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($configGlobal) {
                    // Desactivar todas las configuraciones de esta sucursal
                    $stmt = $conexion->prepare("UPDATE personalizacion_colores SET activo = 0 WHERE id_sucursal = ?");
                    $stmt->execute([$idSucursal]);
                    
                    // Crear nueva configuración para esta sucursal basada en la global
                    $stmt = $conexion->prepare("
                        INSERT INTO personalizacion_colores (
                            nombre_configuracion,
                            login_gradient_start,
                            login_gradient_end,
                            navbar_color,
                            navbar_hover_color,
                            sidebar_color,
                            sidebar_hover_color,
                            sidebar_text_color,
                            icono_pequeno,
                            logo_menu,
                            logo_login,
                            activo,
                            id_sucursal,
                            usuario_creador
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
                    ");
                    
                    // Actualizar el campo de imagen correspondiente
                    $datos = [
                        $configGlobal['nombre_configuracion'] . ' - ' . date('Y-m-d H:i:s'),
                        $configGlobal['login_gradient_start'],
                        $configGlobal['login_gradient_end'],
                        $configGlobal['navbar_color'],
                        $configGlobal['navbar_hover_color'],
                        $configGlobal['sidebar_color'],
                        $configGlobal['sidebar_hover_color'],
                        $configGlobal['sidebar_text_color'],
                        $campoImagen == 'icono_pequeno' ? $rutaImagen : $configGlobal['icono_pequeno'],
                        $campoImagen == 'logo_menu' ? $rutaImagen : $configGlobal['logo_menu'],
                        $campoImagen == 'logo_login' ? $rutaImagen : $configGlobal['logo_login'],
                        $idSucursal,
                        $_SESSION['id'] ?? 1
                    ];
                    
                    $resultado = $stmt->execute($datos);
                }
            }
        } else {
            // Actualizar configuración global activa
            $sql = "UPDATE personalizacion_colores SET $campoImagen = ? WHERE activo = 1 AND id_sucursal IS NULL";
            $stmt = $conexion->prepare($sql);
            $resultado = $stmt->execute([$rutaImagen]);
        }
        
        return $resultado;
        
    } catch (Exception $e) {
        error_log("Error actualizando imagen en BD: " . $e->getMessage());
        return false;
    }
}
?>