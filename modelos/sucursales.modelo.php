<?php

require_once "conexion.php";

class ModeloSucursales {

    /*=============================================
    OBTENER TODAS LAS SUCURSALES (INCLUYENDO ACTUAL)
    =============================================*/
    static public function mdlObtenerSucursales($soloActivas = false, $incluirActual = true) {
        
        try {
            
            // Construir URL de la API
            $apiUrl = API_URL . "obtener_sucursales.php";
            $params = [];
            
            if ($soloActivas) {
                $params[] = "solo_activas=1";
            }
            
            if ($incluirActual) {
                $params[] = "incluir_actual=1";
            }
            
            if (!empty($params)) {
                $apiUrl .= "?" . implode("&", $params);
            }
            
            // Crear contexto para la petición
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 15,
                    'header' => [
                        'Content-Type: application/json',
                        'User-Agent: AdminV5-Model/1.0'
                    ]
                ]
            ]);
            
            // Realizar petición a la API
            $response = @file_get_contents($apiUrl, false, $context);
            
            if ($response === false) {
                throw new Exception('No se pudo conectar con la API de sucursales');
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['success'])) {
                throw new Exception('Respuesta inválida de la API');
            }
            
            return $data;
            
        } catch (Exception $e) {
            
            error_log("Error en mdlObtenerSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener sucursales: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /*=============================================
    CREAR NUEVA SUCURSAL
    =============================================*/
    static public function mdlCrearSucursal($datos) {
        
        try {
            
            // Validar datos requeridos
            $camposRequeridos = ['codigo_sucursal', 'nombre', 'url_base', 'api_url'];
            foreach ($camposRequeridos as $campo) {
                if (empty($datos[$campo])) {
                    throw new Exception("El campo '$campo' es requerido");
                }
            }
            
            // Preparar datos para envío
            $datosEnvio = [
                'codigo_sucursal' => $datos['codigo_sucursal'],
                'nombre' => $datos['nombre'],
                'direccion' => $datos['direccion'] ?? '',
                'telefono' => $datos['telefono'] ?? '',
                'email' => $datos['email'] ?? '',
                'logo' => $datos['logo'] ?? '',
                'url_base' => rtrim($datos['url_base'], '/') . '/',
                'api_url' => rtrim($datos['api_url'], '/') . '/',
                'activa' => isset($datos['activa']) ? (int)$datos['activa'] : 1,
                'es_principal' => isset($datos['es_principal']) ? (int)$datos['es_principal'] : 0,
                'observaciones' => $datos['observaciones'] ?? ''
            ];
            
            // Configurar petición HTTP
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($datosEnvio),
                    'timeout' => 15
                ]
            ]);
            
            // Enviar a la API
            $apiUrl = API_URL . "crear_sucursal.php";
            $response = @file_get_contents($apiUrl, false, $context);
            
            if ($response === false) {
                throw new Exception('No se pudo conectar con la API');
            }
            
            $resultado = json_decode($response, true);
            
            if (!$resultado || !isset($resultado['success'])) {
                throw new Exception('Respuesta inválida de la API');
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            
            error_log("Error en mdlCrearSucursal: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear sucursal: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ACTUALIZAR SUCURSAL EXISTENTE
    =============================================*/
    static public function mdlActualizarSucursal($datos) {
        
        try {
            
            // Validar datos requeridos
            $camposRequeridos = ['id', 'codigo_sucursal', 'nombre', 'url_base', 'api_url'];
            foreach ($camposRequeridos as $campo) {
                if (empty($datos[$campo])) {
                    throw new Exception("El campo '$campo' es requerido");
                }
            }
            
            // Preparar datos para envío
            $datosEnvio = [
                'id' => (int)$datos['id'],
                'codigo_sucursal' => $datos['codigo_sucursal'],
                'nombre' => $datos['nombre'],
                'direccion' => $datos['direccion'] ?? '',
                'telefono' => $datos['telefono'] ?? '',
                'email' => $datos['email'] ?? '',
                'logo' => $datos['logo'] ?? '',
                'url_base' => rtrim($datos['url_base'], '/') . '/',
                'api_url' => rtrim($datos['api_url'], '/') . '/',
                'activa' => isset($datos['activa']) ? (int)$datos['activa'] : 1,
                'es_principal' => isset($datos['es_principal']) ? (int)$datos['es_principal'] : 0,
                'observaciones' => $datos['observaciones'] ?? ''
            ];
            
            // Configurar petición HTTP
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($datosEnvio),
                    'timeout' => 15
                ]
            ]);
            
            // Enviar a la API
            $apiUrl = API_URL . "actualizar_sucursal.php";
            $response = @file_get_contents($apiUrl, false, $context);
            
            if ($response === false) {
                throw new Exception('No se pudo conectar con la API');
            }
            
            $resultado = json_decode($response, true);
            
            if (!$resultado || !isset($resultado['success'])) {
                throw new Exception('Respuesta inválida de la API');
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            
            error_log("Error en mdlActualizarSucursal: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar sucursal: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    static public function mdlEliminarSucursal($id) {
        
        try {
            
            if (empty($id)) {
                throw new Exception('ID de sucursal requerido');
            }
            
            // Preparar datos para envío
            $datosEnvio = [
                'id' => (int)$id
            ];
            
            // Configurar petición HTTP
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($datosEnvio),
                    'timeout' => 15
                ]
            ]);
            
            // Enviar a la API
            $apiUrl = API_URL . "eliminar_sucursal.php";
            $response = @file_get_contents($apiUrl, false, $context);
            
            if ($response === false) {
                throw new Exception('No se pudo conectar con la API');
            }
            
            $resultado = json_decode($response, true);
            
            if (!$resultado || !isset($resultado['success'])) {
                throw new Exception('Respuesta inválida de la API');
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            
            error_log("Error en mdlEliminarSucursal: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar sucursal: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    PROBAR CONEXIÓN CON SUCURSAL
    =============================================*/
    static public function mdlProbarConexionSucursal($apiUrl) {
        
        try {
            
            if (empty($apiUrl)) {
                throw new Exception('URL de API requerida');
            }
            
            // Preparar datos para envío
            $datosEnvio = [
                'api_url' => $apiUrl
            ];
            
            // Configurar petición HTTP
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($datosEnvio),
                    'timeout' => 15
                ]
            ]);
            
            // Enviar a la API
            $apiUrlPrueba = API_URL . "probar_conexion_sucursal.php";
            $response = @file_get_contents($apiUrlPrueba, false, $context);
            
            if ($response === false) {
                throw new Exception('No se pudo conectar con la API de pruebas');
            }
            
            $resultado = json_decode($response, true);
            
            if (!$resultado || !isset($resultado['success'])) {
                throw new Exception('Respuesta inválida de la API');
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            
            error_log("Error en mdlProbarConexionSucursal: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al probar conexión: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    SUBIR LOGO DE SUCURSAL
    =============================================*/
    static public function mdlSubirLogo($archivo, $codigoSucursal) {
        
        try {
            
            // Validar archivo
            if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al cargar el archivo');
            }
            
            // Validar tipo de archivo
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($archivo['type'], $tiposPermitidos)) {
                throw new Exception('Tipo de archivo no permitido. Solo JPG, PNG o GIF');
            }
            
            // Validar tamaño (max 2MB)
            $tamañoMaximo = 2 * 1024 * 1024; // 2MB
            if ($archivo['size'] > $tamañoMaximo) {
                throw new Exception('El archivo es muy grande. Máximo 2MB');
            }
            
            // Crear directorio si no existe
            $directorioDestino = __DIR__ . "/../vistas/img/sucursales/";
            if (!is_dir($directorioDestino)) {
                if (!mkdir($directorioDestino, 0755, true)) {
                    throw new Exception('No se pudo crear el directorio de logos');
                }
            }
            
            // Generar nombre único para el archivo
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'logo_' . $codigoSucursal . '_' . time() . '.' . strtolower($extension);
            $rutaDestino = $directorioDestino . $nombreArchivo;
            
            // Mover archivo
            if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                throw new Exception('Error al guardar el archivo');
            }
            
            // Redimensionar imagen si es muy grande
            self::redimensionarImagen($rutaDestino, 200, 200);
            
            return [
                'success' => true,
                'message' => 'Logo subido correctamente',
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaDestino
            ];
            
        } catch (Exception $e) {
            
            error_log("Error en mdlSubirLogo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al subir logo: ' . $e->getMessage()
            ];
        }
    }

        /*=============================================
    REDIMENSIONAR IMAGEN
    =============================================*/
    private static function redimensionarImagen($rutaImagen, $anchoMax, $altoMax) {
        
        try {
            
            // Obtener información de la imagen
            $infoImagen = getimagesize($rutaImagen);
            if ($infoImagen === false) {
                return false;
            }
            
            list($anchoOriginal, $altoOriginal, $tipo) = $infoImagen;
            
            // Si la imagen ya es del tamaño correcto o menor, no redimensionar
            if ($anchoOriginal <= $anchoMax && $altoOriginal <= $altoMax) {
                return true;
            }
            
            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($anchoMax / $anchoOriginal, $altoMax / $altoOriginal);
            $nuevoAncho = round($anchoOriginal * $ratio);
            $nuevoAlto = round($altoOriginal * $ratio);
            
            // Crear imagen desde archivo según el tipo
            switch ($tipo) {
                case IMAGETYPE_JPEG:
                    $imagenOriginal = imagecreatefromjpeg($rutaImagen);
                    break;
                case IMAGETYPE_PNG:
                    $imagenOriginal = imagecreatefrompng($rutaImagen);
                    break;
                case IMAGETYPE_GIF:
                    $imagenOriginal = imagecreatefromgif($rutaImagen);
                    break;
                default:
                    return false;
            }
            
            if ($imagenOriginal === false) {
                return false;
            }
            
            // Crear nueva imagen redimensionada
            $imagenRedimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            
            // Mantener transparencia para PNG y GIF
            if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
                imagealphablending($imagenRedimensionada, false);
                imagesavealpha($imagenRedimensionada, true);
                $transparente = imagecolorallocatealpha($imagenRedimensionada, 255, 255, 255, 127);
                imagefill($imagenRedimensionada, 0, 0, $transparente);
            }
            
            // Redimensionar imagen
            imagecopyresampled(
                $imagenRedimensionada, $imagenOriginal,
                0, 0, 0, 0,
                $nuevoAncho, $nuevoAlto, $anchoOriginal, $altoOriginal
            );
            
            // Guardar imagen redimensionada según el tipo
            $resultado = false;
            switch ($tipo) {
                case IMAGETYPE_JPEG:
                    $resultado = imagejpeg($imagenRedimensionada, $rutaImagen, 90);
                    break;
                case IMAGETYPE_PNG:
                    $resultado = imagepng($imagenRedimensionada, $rutaImagen, 6);
                    break;
                case IMAGETYPE_GIF:
                    $resultado = imagegif($imagenRedimensionada, $rutaImagen);
                    break;
            }
            
            // Limpiar memoria
            imagedestroy($imagenOriginal);
            imagedestroy($imagenRedimensionada);
            
            return $resultado;
            
        } catch (Exception $e) {
            
            error_log("Error al redimensionar imagen: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    ELIMINAR LOGO ANTERIOR
    =============================================*/
    static public function mdlEliminarLogo($nombreArchivo) {
        
        try {
            
            if (empty($nombreArchivo)) {
                return true; // No hay logo que eliminar
            }
            
            $rutaArchivo = __DIR__ . "/../vistas/img/sucursales/" . $nombreArchivo;
            
            if (file_exists($rutaArchivo)) {
                if (unlink($rutaArchivo)) {
                    return [
                        'success' => true,
                        'message' => 'Logo eliminado correctamente'
                    ];
                } else {
                    throw new Exception('No se pudo eliminar el archivo');
                }
            } else {
                return [
                    'success' => true,
                    'message' => 'El archivo no existe'
                ];
            }
            
        } catch (Exception $e) {
            
            error_log("Error en mdlEliminarLogo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar logo: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    SINCRONIZAR CATÁLOGO CON SUCURSALES ESPECÍFICAS
    =============================================*/
    static public function mdlSincronizarCatalogoConSucursales($sucursalesSeleccionadas = []) {
        
        try {
            
            // Si no se especifican sucursales, obtener todas las activas
            if (empty($sucursalesSeleccionadas)) {
                $respuestaSucursales = self::mdlObtenerSucursales(true);
                if (!$respuestaSucursales['success']) {
                    throw new Exception('No se pudieron obtener las sucursales: ' . $respuestaSucursales['message']);
                }
                $sucursales = $respuestaSucursales['data'];
            } else {
                // Obtener información de sucursales específicas
                $respuestaSucursales = self::mdlObtenerSucursales(false);
                if (!$respuestaSucursales['success']) {
                    throw new Exception('No se pudieron obtener las sucursales: ' . $respuestaSucursales['message']);
                }
                $todasSucursales = $respuestaSucursales['data'];
                $sucursales = array_filter($todasSucursales, function($sucursal) use ($sucursalesSeleccionadas) {
                    return in_array($sucursal['id'], $sucursalesSeleccionadas) && $sucursal['activa'];
                });
            }
            
            $resultado = [
                'success' => true,
                'sucursales_procesadas' => 0,
                'sucursales_exitosas' => 0,
                'sucursales_fallidas' => 0,
                'detalles' => [],
                'errores' => []
            ];
            
            // Procesar cada sucursal
            foreach ($sucursales as $sucursal) {
                
                // No sincronizar con la sucursal actual (se hace localmente)
                if ($sucursal['nombre'] === NOMBRE_SUCURSAL) {
                    continue;
                }
                
                $resultado['sucursales_procesadas']++;
                
                try {
                    
                    // Preparar datos de notificación
                    $datosNotificacion = [
                        'accion' => 'sincronizar_catalogo',
                        'sucursal_origen' => NOMBRE_SUCURSAL,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    
                    // Configurar petición HTTP
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'POST',
                            'header' => 'Content-Type: application/json',
                            'content' => json_encode($datosNotificacion),
                            'timeout' => 30
                        ]
                    ]);
                    
                    // Enviar notificación a la sucursal
                    $urlNotificacion = rtrim($sucursal['api_url'], '/') . '/notificar_sincronizacion.php';
                    $respuesta = @file_get_contents($urlNotificacion, false, $context);
                    
                    if ($respuesta !== false) {
                        
                        $datosRespuesta = json_decode($respuesta, true);
                        
                        if ($datosRespuesta && $datosRespuesta['success']) {
                            
                            $resultado['sucursales_exitosas']++;
                            $resultado['detalles'][] = [
                                'sucursal' => $sucursal['nombre'],
                                'estado' => 'exitoso',
                                'mensaje' => $datosRespuesta['message'] ?? 'Sincronización completada',
                                'sincronizados' => $datosRespuesta['sincronizados'] ?? 0,
                                'actualizados' => $datosRespuesta['actualizados'] ?? 0
                            ];
                            
                        } else {
                            
                            $resultado['sucursales_fallidas']++;
                            $error = $datosRespuesta['message'] ?? 'Respuesta inválida';
                            $resultado['errores'][] = [
                                'sucursal' => $sucursal['nombre'],
                                'error' => $error
                            ];
                        }
                        
                    } else {
                        
                        $resultado['sucursales_fallidas']++;
                        $resultado['errores'][] = [
                            'sucursal' => $sucursal['nombre'],
                            'error' => 'No se pudo conectar con la sucursal'
                        ];
                    }
                    
                } catch (Exception $e) {
                    
                    $resultado['sucursales_fallidas']++;
                    $resultado['errores'][] = [
                        'sucursal' => $sucursal['nombre'],
                        'error' => 'Error de conexión: ' . $e->getMessage()
                    ];
                }
            }
            
            // Generar mensaje final
            $resultado['message'] = "Sincronización completada. " .
                "Procesadas: {$resultado['sucursales_procesadas']}, " .
                "Exitosas: {$resultado['sucursales_exitosas']}, " .
                "Fallidas: {$resultado['sucursales_fallidas']}";
            
            return $resultado;
            
        } catch (Exception $e) {
            
            error_log("Error en mdlSincronizarCatalogoConSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error general en sincronización: ' . $e->getMessage(),
                'sucursales_procesadas' => 0,
                'sucursales_exitosas' => 0,
                'sucursales_fallidas' => 0
            ];
        }
    }

    /*=============================================
    VALIDAR DATOS DE SUCURSAL
    =============================================*/
    private static function validarDatosSucursal($datos, $esActualizacion = false) {
        
        $errores = [];
        
        // Validar ID si es actualización
        if ($esActualizacion && empty($datos['id'])) {
            $errores[] = 'ID de sucursal requerido para actualización';
        }
        
        // Validar código de sucursal
        if (empty($datos['codigo_sucursal'])) {
            $errores[] = 'Código de sucursal es requerido';
        } elseif (!preg_match('/^[A-Z0-9]{3,10}$/', $datos['codigo_sucursal'])) {
            $errores[] = 'Código de sucursal debe tener entre 3-10 caracteres alfanuméricos en mayúsculas';
        }
        
        // Validar nombre
        if (empty($datos['nombre'])) {
            $errores[] = 'Nombre de sucursal es requerido';
        } elseif (strlen($datos['nombre']) < 3 || strlen($datos['nombre']) > 100) {
            $errores[] = 'Nombre debe tener entre 3-100 caracteres';
        }
        
        // Validar URL base
        if (empty($datos['url_base'])) {
            $errores[] = 'URL base es requerida';
        } elseif (!filter_var($datos['url_base'], FILTER_VALIDATE_URL)) {
            $errores[] = 'URL base no es válida';
        }
        
        // Validar API URL
        if (empty($datos['api_url'])) {
            $errores[] = 'API URL es requerida';
        } elseif (!filter_var($datos['api_url'], FILTER_VALIDATE_URL)) {
            $errores[] = 'API URL no es válida';
        }
        
        // Validar email si se proporciona
        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Email no es válido';
        }
        
        // Validar teléfono si se proporciona
        if (!empty($datos['telefono']) && !preg_match('/^[\d\s\-\(\)\+]{7,20}$/', $datos['telefono'])) {
            $errores[] = 'Teléfono no es válido';
        }
        
        return $errores;
    }

    /*=============================================
    CREAR DIRECTORIO DE LOGOS SI NO EXISTE
    =============================================*/
    static public function mdlCrearDirectorioLogos() {
        
        try {
            
            $directorioLogos = __DIR__ . "/../vistas/img/sucursales/";
            
            if (!is_dir($directorioLogos)) {
                if (mkdir($directorioLogos, 0755, true)) {
                    return [
                        'success' => true,
                        'message' => 'Directorio de logos creado correctamente'
                    ];
                } else {
                    throw new Exception('No se pudo crear el directorio');
                }
            } else {
                return [
                    'success' => true,
                    'message' => 'Directorio ya existe'
                ];
            }
            
        } catch (Exception $e) {
            
            error_log("Error en mdlCrearDirectorioLogos: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear directorio: ' . $e->getMessage()
            ];
        }
    }
}

?>