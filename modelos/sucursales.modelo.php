<?php

require_once "conexion.php";

class ModeloSucursales {

    /*=============================================
    OBTENER TODAS LAS SUCURSALES DESDE BD CENTRAL
    =============================================*/
    static public function mdlObtenerSucursales($soloActivas = false) {
        try {
            // Usar conexión central
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $sql = "SELECT * FROM sucursales";
            $params = [];
            
            if ($soloActivas) {
                $sql .= " WHERE activo = 1";
            }
            
            $sql .= " ORDER BY es_principal DESC, fecha_registro ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear fechas y datos
            foreach ($sucursales as &$sucursal) {
                $sucursal['activo'] = (bool)$sucursal['activo'];
                $sucursal['es_principal'] = (bool)$sucursal['es_principal'];
                
            if ($sucursal['fecha_actualizacion']) {
                $sucursal['ultima_sincronizacion_formato'] = date('d/m/Y H:i:s', strtotime($sucursal['fecha_actualizacion']));
            } else {
                $sucursal['ultima_sincronizacion_formato'] = 'Nunca';
            }
            }
            
            return [
                'success' => true,
                'message' => 'Sucursales obtenidas correctamente',
                'data' => $sucursales
            ];
            
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
    CONFIGURAR SUCURSAL LOCAL (TABLA LOCAL)
    =============================================*/
    static public function mdlConfigurarSucursalLocal($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE id = 1");
            $stmt->execute();
            $existe = $stmt->fetch();
            
            if ($existe) {
                // Actualizar configuración existente
                $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET 
                    codigo_sucursal = :codigo_sucursal,
                    nombre = :nombre,
                    direccion = :direccion,
                    telefono = :telefono,
                    email = :email,
                    url_base = :url_base,
                    url_api = :url_api,
                    es_principal = :es_principal,
                    fecha_actualizacion = NOW()
                    WHERE id = 1");
            } else {
                // Crear nueva configuración
                $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (
                    codigo_sucursal, nombre, direccion, telefono, email, 
                    url_base, url_api, es_principal, activo, fecha_registro, fecha_actualizacion
                ) VALUES (
                    :codigo_sucursal, :nombre, :direccion, :telefono, :email,
                    :url_base, :url_api, :es_principal, 1, NOW(), NOW()
                )");
            }
            
            $stmt->bindParam(":codigo_sucursal", $datos["codigo_sucursal"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":url_base", $datos["url_base"], PDO::PARAM_STR);
            $stmt->bindParam(":url_api", $datos["url_api"], PDO::PARAM_STR);
            $stmt->bindParam(":es_principal", $datos["es_principal"], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlConfigurarSucursalLocal: " . $e->getMessage());
            return "error";
        }
    }

    /*=============================================
    OBTENER CONFIGURACIÓN LOCAL
    =============================================*/
    static public function mdlObtenerConfiguracionLocal() {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local WHERE id = 1");
            $stmt->execute();
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $datos ? $datos : null;
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerConfiguracionLocal: " . $e->getMessage());
            return null;
        }
    }

    /*=============================================
    GENERAR CONSECUTIVO AUTOMÁTICO DE SUCURSAL
    =============================================*/
    static public function mdlGenerarConsecutivoSucursal() {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            // Obtener el último código registrado
            $stmt = $pdo->prepare("SELECT codigo_sucursal FROM sucursales 
                                  WHERE codigo_sucursal REGEXP '^SUC[0-9]{3}$' 
                                  ORDER BY CAST(SUBSTRING(codigo_sucursal, 4) AS UNSIGNED) DESC 
                                  LIMIT 1");
            $stmt->execute();
            $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ultimo) {
                // Extraer número y sumar 1
                $numero = intval(substr($ultimo['codigo_sucursal'], 3));
                $siguienteNumero = $numero + 1;
            } else {
                // Primer código
                $siguienteNumero = 1;
            }
            
            // Formatear con ceros a la izquierda
            $nuevoCodigo = 'SUC' . str_pad($siguienteNumero, 3, '0', STR_PAD_LEFT);
            
            return $nuevoCodigo;
            
        } catch (Exception $e) {
            error_log("Error en mdlGenerarConsecutivoSucursal: " . $e->getMessage());
            return 'SUC001'; // Código por defecto
        }
    }

    /*=============================================
    CREAR SUCURSAL EN BD CENTRAL
    =============================================*/
    static public function mdlCrearSucursalCentral($datos) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            // Verificar si ya existe
            $stmt = $pdo->prepare("SELECT id FROM sucursales WHERE codigo_sucursal = ?");
            $stmt->execute([$datos["codigo_sucursal"]]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe una sucursal con ese código'
                ];
            }
            
            // Insertar nueva sucursal
            $stmt = $pdo->prepare("INSERT INTO sucursales (
                codigo_sucursal, nombre, direccion, telefono, email,
                url_base, url_api, es_principal, activo,
                fecha_registro, fecha_actualizacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
            
            $resultado = $stmt->execute([
                $datos["codigo_sucursal"],
                $datos["nombre"],
                $datos["direccion"],
                $datos["telefono"],
                $datos["email"],
                $datos["url_base"],
                $datos["url_api"],
                $datos["es_principal"] ? 1 : 0
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Sucursal registrada correctamente',
                    'id' => $pdo->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar la sucursal'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlCrearSucursalCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ACTUALIZAR SUCURSAL EN BD CENTRAL
    =============================================*/
    static public function mdlActualizarSucursalCentral($datos) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("UPDATE sucursales SET 
                nombre = ?, direccion = ?, telefono = ?, email = ?,
                url_base = ?, url_api = ?, activo = ?, fecha_actualizacion = NOW()
                WHERE id = ?");
            
            $resultado = $stmt->execute([
                $datos["nombre"],
                $datos["direccion"],
                $datos["telefono"],
                $datos["email"],
                $datos["url_base"],
                $datos["url_api"],
                $datos["activo"],
                $datos["id"]
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Sucursal actualizada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la sucursal'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlActualizarSucursalCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ELIMINAR SUCURSAL DE BD CENTRAL
    =============================================*/
    static public function mdlEliminarSucursalCentral($id) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            // Verificar si es sucursal principal
            $stmt = $pdo->prepare("SELECT es_principal FROM sucursales WHERE id = ?");
            $stmt->execute([$id]);
            $sucursal = $stmt->fetch();
            
            if (!$sucursal) {
                return [
                    'success' => false,
                    'message' => 'Sucursal no encontrada'
                ];
            }
            
            if ($sucursal['es_principal']) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar la sucursal principal'
                ];
            }
            
            // Eliminar sucursal
            $stmt = $pdo->prepare("DELETE FROM sucursales WHERE id = ?");
            $resultado = $stmt->execute([$id]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Sucursal eliminada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar la sucursal'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEliminarSucursalCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    VERIFICAR SI SUCURSAL ESTÁ REGISTRADA EN BD CENTRAL
    =============================================*/
    static public function mdlVerificarSucursalEnCentral($codigoSucursal) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("SELECT id, activo FROM sucursales WHERE codigo_sucursal = ?");
            $stmt->execute([$codigoSucursal]);
            $sucursal = $stmt->fetch();
            
            if ($sucursal) {
                return [
                    'success' => true,
                    'registrada' => true,
                    'activa' => (bool)$sucursal['activo'],
                    'id' => $sucursal['id']
                ];
            } else {
                return [
                    'success' => false,
                    'registrada' => false
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlVerificarSucursalEnCentral: " . $e->getMessage());
            return [
                'success' => false,
                'registrada' => false,
                'message' => 'Error al verificar registro: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    OBTENER SUCURSALES ACTIVAS PARA SINCRONIZACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesCentral($soloActivas = false) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $sql = "SELECT codigo_sucursal, nombre, url_api, activo FROM sucursales";
            
            if ($soloActivas) {
                $sql .= " WHERE activo = 1";
            }
            
            $sql .= " ORDER BY es_principal DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $sucursales
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesCentral: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener sucursales: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /*=============================================
    SINCRONIZAR CATÁLOGO MAESTRO CON SUCURSALES
    =============================================*/
    static public function mdlSincronizarCatalogoConSucursales($catalogoMaestro, $sucursales) {
        try {
            $resultados = [
                'success' => true,
                'total_sucursales' => count($sucursales),
                'exitosas' => 0,
                'fallidas' => 0,
                'detalles' => []
            ];
            
            $codigoActual = defined('CODIGO_SUCURSAL') ? CODIGO_SUCURSAL : 'DESCONOCIDO';
            
            foreach ($sucursales as $sucursal) {
                
                // No sincronizar consigo mismo
                if ($sucursal['codigo_sucursal'] === $codigoActual) {
                    continue;
                }
                
                $detalleSync = [
                    'sucursal' => $sucursal['nombre'],
                    'codigo' => $sucursal['codigo_sucursal'],
                    'url_api' => $sucursal['url_api'],
                    'estado' => 'fallido',
                    'mensaje' => 'Error desconocido'
                ];
                
                try {
                    
                    // Preparar datos para envío
                    $datosEnvio = [
                        'accion' => 'sincronizar_catalogo',
                        'catalogo' => $catalogoMaestro,
                        'origen' => $codigoActual,
                        'timestamp' => time()
                    ];
                    
                    // Crear contexto para envío HTTP
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'POST',
                            'header' => 'Content-Type: application/json',
                            'content' => json_encode($datosEnvio),
                            'timeout' => 30
                        ]
                    ]);
                    
                    $urlSync = rtrim($sucursal['url_api'], '/') . '/sincronizar_catalogo.php';
                    $respuesta = @file_get_contents($urlSync, false, $context);
                    
                    if ($respuesta !== false) {
                        $resultado = json_decode($respuesta, true);
                        
                        if ($resultado && isset($resultado['success']) && $resultado['success']) {
                            $detalleSync['estado'] = 'exitoso';
                            $detalleSync['mensaje'] = $resultado['message'] ?? 'Sincronización completada';
                            $resultados['exitosas']++;
                            
                            // Actualizar fecha de última sincronización
                            self::mdlActualizarFechaSincronizacion($sucursal['codigo_sucursal']);
                            
                        } else {
                            $detalleSync['mensaje'] = $resultado['message'] ?? 'Error en respuesta de API';
                            $resultados['fallidas']++;
                        }
                    } else {
                        $detalleSync['mensaje'] = 'No se pudo conectar con la sucursal';
                        $resultados['fallidas']++;
                    }
                    
                } catch (Exception $e) {
                    $detalleSync['mensaje'] = 'Excepción: ' . $e->getMessage();
                    $resultados['fallidas']++;
                }
                
                $resultados['detalles'][] = $detalleSync;
            }
            
            // Generar mensaje consolidado
            $resultados['mensaje_detallado'] = sprintf(
                "SINCRONIZACIÓN COMPLETADA:<br><br>" .
                "• Total de sucursales: %d<br>" .
                "• Sincronizaciones exitosas: %d<br>" .
                "• Sincronizaciones fallidas: %d<br><br>" .
                "Detalles por sucursal:<br>%s",
                $resultados['total_sucursales'],
                $resultados['exitosas'],
                $resultados['fallidas'],
                implode('<br>', array_map(function($detalle) {
                    $icono = $detalle['estado'] === 'exitoso' ? '✅' : '❌';
                    return sprintf("$icono %s: %s", $detalle['sucursal'], $detalle['mensaje']);
                }, $resultados['detalles']))
            );
            
            return $resultados;
            
        } catch (Exception $e) {
            error_log("Error en mdlSincronizarCatalogoConSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage(),
                'total_sucursales' => 0,
                'exitosas' => 0,
                'fallidas' => 0,
                'detalles' => []
            ];
        }
    }

    /*=============================================
    ACTUALIZAR FECHA DE ÚLTIMA SINCRONIZACIÓN
    =============================================*/
    private static function mdlActualizarFechaSincronizacion($codigoSucursal) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("UPDATE sucursales SET 
                fecha_ultima_sincronizacion_catalogo = NOW() 
                WHERE codigo_sucursal = ?");
            $stmt->execute([$codigoSucursal]);
            
        } catch (Exception $e) {
            error_log("Error actualizando fecha de sincronización: " . $e->getMessage());
        }
    }

    /*=============================================
    ACTUALIZAR ESTADO DE REGISTRO LOCAL
    =============================================*/
    static public function mdlActualizarEstadoRegistro($id, $registrada) {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE sucursal_local SET 
                registrada_en_central = ?, fecha_actualizacion = NOW() 
                WHERE id = ?");
            
            return $stmt->execute([$registrada, $id]);
            
        } catch (Exception $e) {
            error_log("Error en mdlActualizarEstadoRegistro: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    OBTENER NOMBRE POR CÓDIGO
    =============================================*/
    static public function mdlObtenerNombrePorCodigo($codigo) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT nombre FROM sucursal_local WHERE codigo_sucursal = ?");
            $stmt->execute([$codigo]);
            $resultado = $stmt->fetch();
            
            return $resultado ? $resultado['nombre'] : 'Sucursal';
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerNombrePorCodigo: " . $e->getMessage());
            return 'Sucursal';
        }
    }

    /*=============================================
    MOSTRAR SUCURSAL ESPECÍFICA (PARA AJAX)
    =============================================*/
    static public function mdlMostrarSucursal($item, $valor) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE $item = ?");
            $stmt->execute([$valor]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlMostrarSucursal: " . $e->getMessage());
            return null;
        }
    }

    /*=============================================
    PROBAR CONEXIÓN CON SUCURSAL
    =============================================*/
    static public function mdlProbarConexionSucursal($apiUrl) {
        try {
            $inicioTiempo = microtime(true);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'header' => 'User-Agent: AdminV5-Test/1.0'
                ]
            ]);
            
            $urlTest = rtrim($apiUrl, '/') . '/test_conexion.php';
            $respuesta = @file_get_contents($urlTest, false, $context);
            $tiempoTranscurrido = round((microtime(true) - $inicioTiempo) * 1000);
            
            if ($respuesta !== false) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con la sucursal',
                    'tiempo_respuesta' => $tiempoTranscurrido . 'ms',
                    'respuesta' => $respuesta
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo conectar. Verifique URL y disponibilidad del servidor.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlProbarConexionSucursal: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al probar conexión: ' . $e->getMessage()
            ];
        }
    }
    /*=============================================
    CREAR SUCURSAL LOCAL (VERSIÓN CORREGIDA)
    =============================================*/
    static public function mdlCrearSucursalLocal($tabla, $datos) {
        try {
            // Verificar que la tabla sea la correcta
            if ($tabla !== "sucursal_local") {
                error_log("Tabla incorrecta en mdlCrearSucursalLocal: " . $tabla);
                return "error";
            }
            
            $stmt = Conexion::conectar()->prepare("INSERT INTO sucursal_local (
                codigo_sucursal, nombre, direccion, telefono, email,
                url_base, url_api, es_principal, activo, registrada_en_central,
                fecha_registro, fecha_actualizacion
            ) VALUES (
                :codigo_sucursal, :nombre, :direccion, :telefono, :email,
                :url_base, :url_api, :es_principal, :activo, :registrada_en_central,
                NOW(), NOW()
            )");
            
            $stmt->bindParam(":codigo_sucursal", $datos["codigo_sucursal"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":url_base", $datos["url_base"], PDO::PARAM_STR);
            $stmt->bindParam(":url_api", $datos["url_api"], PDO::PARAM_STR);
            $stmt->bindParam(":es_principal", $datos["es_principal"], PDO::PARAM_INT);
            $stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
            $stmt->bindParam(":registrada_en_central", $datos["registrada_en_central"], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error SQL en mdlCrearSucursalLocal: " . print_r($errorInfo, true));
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Excepción en mdlCrearSucursalLocal: " . $e->getMessage());
            return "error";
        }
    }
}

?>