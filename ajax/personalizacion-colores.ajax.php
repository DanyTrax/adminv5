<?php
/*=============================================
AJAX PERSONALIZACIÓN DE COLORES CON SUBIDA DE IMÁGENES
=============================================*/

require_once "../controladores/personalizacion-colores-simplificado-con-imagenes.controlador.php";

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
                    
                    if (actualizarImagenEnBD($campoImagen, $rutaRelativa)) {
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
                
                require_once "../controladores/personalizacion-colores-simplificado-funcional.controlador.php";
                
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
    
    // Definir dimensiones según el tipo
    $dimensiones = [
        'icono-pequeno' => [50, 50],
        'logo-menu' => [200, 50],
        'logo-login' => [200, 100]
    ];
    
    if (!isset($dimensiones[$tipo])) {
        return false;
    }
    
    list($anchoDestino, $altoDestino) = $dimensiones[$tipo];
    
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
    }
    
    // Redimensionar
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
FUNCIÓN PARA ACTUALIZAR IMAGEN EN BASE DE DATOS
=============================================*/
function actualizarImagenEnBD($campoImagen, $rutaImagen) {
    
    try {
        require_once "../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        // Actualizar la configuración activa
        $sql = "UPDATE personalizacion_colores SET $campoImagen = ? WHERE activo = 1";
        $stmt = $conexion->prepare($sql);
        $resultado = $stmt->execute([$rutaImagen]);
        
        return $resultado;
        
    } catch (Exception $e) {
        error_log("Error actualizando imagen en BD: " . $e->getMessage());
        return false;
    }
}
?>