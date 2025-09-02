<?php

class ControladorSucursales {

    /*=============================================
    MOSTRAR VISTA PRINCIPAL DE SUCURSALES
    =============================================*/
    static public function ctrMostrarSucursales() {
        
        // Verificar que el usuario sea administrador
        if ($_SESSION["perfil"] != "Administrador") {
            echo '<script>
                window.location = "inicio";
            </script>';
            return;
        }
        
        include "vistas/modulos/sucursales.php";
    }

    /*=============================================
    OBTENER SUCURSALES PARA DATATABLES
    =============================================*/
    static public function ctrObtenerSucursalesDataTable() {
        
        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para acceder'
            ]);
            return;
        }
        
        try {
            
            $soloActivas = isset($_GET['solo_activas']) && $_GET['solo_activas'] == '1';
            $respuesta = ModeloSucursales::mdlObtenerSucursales($soloActivas);
            
            if ($respuesta['success']) {
                
                // Formatear datos para DataTables
                $datos = [];
                foreach ($respuesta['data'] as $sucursal) {
                    
                    // Estado con toggle switch
                    $estado = $sucursal['activa'] ? 
                        '<span class="badge badge-success">Activa</span>' : 
                        '<span class="badge badge-danger">Inactiva</span>';
                    
                    // Logo
                    $logo = '';
                    if (!empty($sucursal['logo'])) {
                        $logo = '<img src="vistas/img/sucursales/'.$sucursal['logo'].'" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">';
                    } else {
                        $logo = '<i class="fa fa-building text-muted" style="font-size: 30px;"></i>';
                    }
                    
                    // Principal
                    $principal = $sucursal['es_principal'] ? 
                        '<i class="fa fa-star text-warning" title="Sucursal Principal"></i>' : '';
                    
                    // Última sincronización
                    $ultimaSync = $sucursal['ultima_sincronizacion_formato'] ?? 'Nunca';
                    
                    // Botones de acción
                    $acciones = '
                        <div class="btn-group">
                            <button class="btn btn-info btn-sm btnEditarSucursal" 
                                    idSucursal="'.$sucursal['id'].'" 
                                    title="Editar">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn btn-success btn-sm btnProbarConexion" 
                                    apiUrl="'.$sucursal['api_url'].'" 
                                    nombreSucursal="'.$sucursal['nombre'].'" 
                                    title="Probar Conexión">
                                <i class="fa fa-wifi"></i>
                            </button>
                            <button class="btn btn-primary btn-sm btnSincronizarSucursal" 
                                    idSucursal="'.$sucursal['id'].'" 
                                    nombreSucursal="'.$sucursal['nombre'].'" 
                                    title="Sincronizar">
                                <i class="fa fa-refresh"></i>
                            </button>';
                    
                    // Solo permitir eliminar si no es principal
                    if (!$sucursal['es_principal']) {
                        $acciones .= '
                            <button class="btn btn-danger btn-sm btnEliminarSucursal" 
                                    idSucursal="'.$sucursal['id'].'" 
                                    nombreSucursal="'.$sucursal['nombre'].'" 
                                    title="Eliminar">
                                <i class="fa fa-trash"></i>
                            </button>';
                    }
                    
                    $acciones .= '</div>';
                    
                    $datos[] = [
                        $logo,
                        $sucursal['codigo_sucursal'],
                        $sucursal['nombre'] . ' ' . $principal,
                        $sucursal['direccion'] ?? '',
                        $sucursal['telefono'] ?? '',
                        $estado,
                        $ultimaSync,
                        $acciones
                    ];
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $datos
                ]);
                
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $respuesta['message']
                ]);
            }
            
        } catch (Exception $e) {
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener sucursales: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    CREAR NUEVA SUCURSAL
    =============================================*/
    static public function ctrCrearSucursal() {
        
        if (isset($_POST["nuevoCodigo"])) {
            
            // Verificar permisos
            if ($_SESSION["perfil"] != "Administrador") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "No tienes permisos para realizar esta acción",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
                return;
            }
            
            // Validar datos básicos
            if (empty($_POST["nuevoCodigo"]) || empty($_POST["nuevoNombre"]) || 
                empty($_POST["nuevaUrlBase"]) || empty($_POST["nuevaApiUrl"])) {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "Todos los campos marcados con * son obligatorios",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
            
            // Preparar datos
            $datos = [
                'codigo_sucursal' => strtoupper(trim($_POST["nuevoCodigo"])),
                'nombre' => trim($_POST["nuevoNombre"]),
                'direccion' => trim($_POST["nuevaDireccion"]) ?? '',
                'telefono' => trim($_POST["nuevoTelefono"]) ?? '',
                'email' => trim($_POST["nuevoEmail"]) ?? '',
                'url_base' => trim($_POST["nuevaUrlBase"]),
                'api_url' => trim($_POST["nuevaApiUrl"]),
                'activa' => isset($_POST["nuevaActiva"]) ? 1 : 0,
                'es_principal' => isset($_POST["nuevaPrincipal"]) ? 1 : 0,
                'observaciones' => trim($_POST["nuevasObservaciones"]) ?? '',
                'logo' => ''
            ];
            
            // Procesar logo si se subió
            if (isset($_FILES["nuevoLogo"]) && $_FILES["nuevoLogo"]["error"] == UPLOAD_ERR_OK) {
                $resultadoLogo = ModeloSucursales::mdlSubirLogo($_FILES["nuevoLogo"], $datos['codigo_sucursal']);
                if ($resultadoLogo['success']) {
                    $datos['logo'] = $resultadoLogo['nombre_archivo'];
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "Error al subir logo: '.$resultadoLogo['message'].'",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
            }
            
            // Crear sucursal
            $respuesta = ModeloSucursales::mdlCrearSucursal($datos);
            
            if ($respuesta['success']) {
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Correcto!",
                        text: "La sucursal ha sido guardada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
                
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "'.$respuesta['message'].'",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    MOSTRAR DATOS DE SUCURSAL PARA EDITAR
    =============================================*/
    static public function ctrMostrarSucursal($item, $valor) {
        
        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            return null;
        }
        
        try {
            
            $respuesta = ModeloSucursales::mdlObtenerSucursales();
            
            if ($respuesta['success']) {
                
                foreach ($respuesta['data'] as $sucursal) {
                    if ($sucursal[$item] == $valor) {
                        return $sucursal;
                    }
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            
            error_log("Error en ctrMostrarSucursal: " . $e->getMessage());
            return null;
        }
    }

    /*=============================================
    ACTUALIZAR SUCURSAL
    =============================================*/
    static public function ctrActualizarSucursal() {
        
        if (isset($_POST["editarId"])) {
            
            // Verificar permisos
            if ($_SESSION["perfil"] != "Administrador") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "No tienes permisos para realizar esta acción",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
                return;
            }
            
            // Validar datos básicos
            if (empty($_POST["editarCodigo"]) || empty($_POST["editarNombre"]) || 
                empty($_POST["editarUrlBase"]) || empty($_POST["editarApiUrl"])) {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "Todos los campos marcados con * son obligatorios",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
            
            // Preparar datos
            $datos = [
                'id' => (int)$_POST["editarId"],
                'codigo_sucursal' => strtoupper(trim($_POST["editarCodigo"])),
                'nombre' => trim($_POST["editarNombre"]),
                'direccion' => trim($_POST["editarDireccion"]) ?? '',
                'telefono' => trim($_POST["editarTelefono"]) ?? '',
                'email' => trim($_POST["editarEmail"]) ?? '',
                'url_base' => trim($_POST["editarUrlBase"]),
                'api_url' => trim($_POST["editarApiUrl"]),
                'activa' => isset($_POST["editarActiva"]) ? 1 : 0,
                'es_principal' => isset($_POST["editarPrincipal"]) ? 1 : 0,
                'observaciones' => trim($_POST["editarObservaciones"]) ?? '',
                'logo' => trim($_POST["logoActual"]) ?? ''
            ];
            
            // Procesar nuevo logo si se subió
            if (isset($_FILES["editarLogo"]) && $_FILES["editarLogo"]["error"] == UPLOAD_ERR_OK) {
                
                // Eliminar logo anterior si existe
                if (!empty($datos['logo'])) {
                    ModeloSucursales::mdlEliminarLogo($datos['logo']);
                }
                
                // Subir nuevo logo
                $resultadoLogo = ModeloSucursales::mdlSubirLogo($_FILES["editarLogo"], $datos['codigo_sucursal']);
                if ($resultadoLogo['success']) {
                    $datos['logo'] = $resultadoLogo['nombre_archivo'];
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "Error al subir logo: '.$resultadoLogo['message'].'",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
            }
            
            // Actualizar sucursal
            $respuesta = ModeloSucursales::mdlActualizarSucursal($datos);
            
            if ($respuesta['success']) {
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Correcto!",
                        text: "La sucursal ha sido actualizada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
                
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "'.$respuesta['message'].'",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    static public function ctrEliminarSucursal() {
        
        if (isset($_GET["idSucursal"])) {
            
            // Verificar permisos
            if ($_SESSION["perfil"] != "Administrador") {
                echo '<script>
                    window.location = "inicio";
                </script>';
                return;
            }
            
            $id = (int)$_GET["idSucursal"];
            
            // Obtener datos de la sucursal antes de eliminar
            $sucursal = self::ctrMostrarSucursal("id", $id);
            
            if ($sucursal && $sucursal['es_principal']) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "No se puede eliminar la sucursal principal",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
                return;
            }
            
            // Eliminar sucursal
            $respuesta = ModeloSucursales::mdlEliminarSucursal($id);
            
            if ($respuesta['success']) {
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Correcto!",
                        text: "La sucursal ha sido borrada correctamente",
                        showConfirmButton: true,
                                                confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
                
            } else {
                
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: "'.$respuesta['message'].'",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "sucursales";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    PROBAR CONEXIÓN CON SUCURSAL
    =============================================*/
    static public function ctrProbarConexionSucursal() {
        
        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }
        
        if (isset($_POST["apiUrl"])) {
            
            $apiUrl = $_POST["apiUrl"];
            $respuesta = ModeloSucursales::mdlProbarConexionSucursal($apiUrl);
            
            echo json_encode($respuesta);
        }
    }

    /*=============================================
    SINCRONIZAR CATÁLOGO CON TODAS LAS SUCURSALES
    =============================================*/
    static public function ctrSincronizarTodasSucursales() {
        
        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }
        
        try {
            
            // 1. Primero sincronizar localmente
            require_once "modelos/catalogo-maestro.modelo.php";
            $resultadoLocal = ModeloCatalogoMaestro::mdlSincronizarAProductosLocales();
            
            // 2. Luego sincronizar con otras sucursales
            $resultadoRemoto = ModeloSucursales::mdlSincronizarCatalogoConSucursales();
            
            // 3. Combinar resultados
            $respuesta = [
                'success' => true,
                'local' => $resultadoLocal,
                'remoto' => $resultadoRemoto,
                'message' => "Sincronización completada. " .
                    "Local: {$resultadoLocal['sincronizados']} nuevos, {$resultadoLocal['actualizados']} actualizados. " .
                    "Remotas: {$resultadoRemoto['sucursales_exitosas']}/{$resultadoRemoto['sucursales_procesadas']} sucursales sincronizadas."
            ];
            
            echo json_encode($respuesta);
            
        } catch (Exception $e) {
            
            echo json_encode([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    SINCRONIZAR CATÁLOGO CON SUCURSALES ESPECÍFICAS
    =============================================*/
    static public function ctrSincronizarSucursalesSeleccionadas() {
        
        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }
        
        if (isset($_POST["sucursales"])) {
            
            try {
                
                $sucursalesSeleccionadas = json_decode($_POST["sucursales"], true);
                
                if (empty($sucursalesSeleccionadas)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Debe seleccionar al menos una sucursal'
                    ]);
                    return;
                }
                
                // 1. Primero sincronizar localmente
                require_once "modelos/catalogo-maestro.modelo.php";
                $resultadoLocal = ModeloCatalogoMaestro::mdlSincronizarAProductosLocales();
                
                // 2. Sincronizar con sucursales seleccionadas
                $resultadoRemoto = ModeloSucursales::mdlSincronizarCatalogoConSucursales($sucursalesSeleccionadas);
                
                // 3. Combinar resultados
                $respuesta = [
                    'success' => true,
                    'local' => $resultadoLocal,
                    'remoto' => $resultadoRemoto,
                    'message' => "Sincronización completada. " .
                        "Local: {$resultadoLocal['sincronizados']} nuevos, {$resultadoLocal['actualizados']} actualizados. " .
                        "Remotas: {$resultadoRemoto['sucursales_exitosas']}/{$resultadoRemoto['sucursales_procesadas']} sucursales sincronizadas."
                ];
                
                echo json_encode($respuesta);
                
            } catch (Exception $e) {
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Error en sincronización selectiva: ' . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    CAMBIAR ESTADO DE SUCURSAL (ACTIVA/INACTIVA)
    =============================================*/
    static public function ctrCambiarEstadoSucursal() {
        
        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }
        
        if (isset($_POST["idSucursal"]) && isset($_POST["estadoSucursal"])) {
            
            try {
                
                $id = (int)$_POST["idSucursal"];
                $estado = (int)$_POST["estadoSucursal"];
                
                // Obtener datos actuales de la sucursal
                $sucursal = self::ctrMostrarSucursal("id", $id);
                
                if (!$sucursal) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Sucursal no encontrada'
                    ]);
                    return;
                }
                
                // No permitir desactivar sucursal principal
                if ($sucursal['es_principal'] && $estado == 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se puede desactivar la sucursal principal'
                    ]);
                    return;
                }
                
                // Actualizar solo el estado
                $datos = $sucursal;
                $datos['activa'] = $estado;
                
                $respuesta = ModeloSucursales::mdlActualizarSucursal($datos);
                echo json_encode($respuesta);
                
            } catch (Exception $e) {
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al cambiar estado: ' . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE SUCURSALES
    =============================================*/
    static public function ctrObtenerEstadisticasSucursales() {
        
        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para acceder'
            ]);
            return;
        }
        
        try {
            
            $respuesta = ModeloSucursales::mdlObtenerSucursales();
            
            if ($respuesta['success']) {
                
                $total = count($respuesta['data']);
                $activas = 0;
                $inactivas = 0;
                $conLogo = 0;
                $principales = 0;
                $ultimaSincronizacion = null;
                
                foreach ($respuesta['data'] as $sucursal) {
                    
                    if ($sucursal['activa']) {
                        $activas++;
                    } else {
                        $inactivas++;
                    }
                    
                    if (!empty($sucursal['logo'])) {
                        $conLogo++;
                    }
                    
                    if ($sucursal['es_principal']) {
                        $principales++;
                    }
                    
                    // Obtener la sincronización más reciente
                    if ($sucursal['ultima_sincronizacion']) {
                        if (!$ultimaSincronizacion || $sucursal['ultima_sincronizacion'] > $ultimaSincronizacion) {
                            $ultimaSincronizacion = $sucursal['ultima_sincronizacion'];
                        }
                    }
                }
                
                $estadisticas = [
                    'success' => true,
                    'total' => $total,
                    'activas' => $activas,
                    'inactivas' => $inactivas,
                    'con_logo' => $conLogo,
                    'principales' => $principales,
                    'ultima_sincronizacion' => $ultimaSincronizacion ? date('d/m/Y H:i:s', strtotime($ultimaSincronizacion)) : 'Nunca'
                ];
                
                echo json_encode($estadisticas);
                
            } else {
                
                echo json_encode([
                    'success' => false,
                    'message' => $respuesta['message']
                ]);
            }
            
        } catch (Exception $e) {
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    VALIDAR PERMISOS DE ADMINISTRADOR
    =============================================*/
    private static function validarPermisosAdmin() {
        
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            return false;
        }
        
        return true;
    }

    /*=============================================
    LIMPIAR DATOS DE ENTRADA
    =============================================*/
    private static function limpiarDatos($datos) {
        
        $datoslimpios = [];
        
        foreach ($datos as $clave => $valor) {
            
            if (is_string($valor)) {
                $datoslimpios[$clave] = trim(strip_tags($valor));
            } else {
                $datoslimpios[$clave] = $valor;
            }
        }
        
        return $datoslimpios;
    }

    /*=============================================
    VALIDAR FORMATO DE URL
    =============================================*/
    private static function validarUrl($url) {
        
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /*=============================================
    VALIDAR FORMATO DE EMAIL
    =============================================*/
    private static function validarEmail($email) {
        
        if (empty($email)) {
            return true; // Email opcional
        }
        
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /*=============================================
    GENERAR CÓDIGO DE SUCURSAL AUTOMÁTICO
    =============================================*/
    static public function ctrGenerarCodigoSucursal() {
        
        if (!self::validarPermisosAdmin()) {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }
        
        try {
            
            $respuesta = ModeloSucursales::mdlObtenerSucursales();
            
            if ($respuesta['success']) {
                
                $codigosExistentes = [];
                foreach ($respuesta['data'] as $sucursal) {
                    $codigosExistentes[] = $sucursal['codigo_sucursal'];
                }
                
                // Generar código SUC001, SUC002, etc.
                $contador = 1;
                do {
                    $codigo = 'SUC' . str_pad($contador, 3, '0', STR_PAD_LEFT);
                    $contador++;
                } while (in_array($codigo, $codigosExistentes));
                
                echo json_encode([
                    'success' => true,
                    'codigo' => $codigo
                ]);
                
            } else {
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al generar código: ' . $respuesta['message']
                ]);
            }
            
        } catch (Exception $e) {
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar código: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    EJECUTAR ACCIONES AJAX
    =============================================*/
    static public function ctrEjecutarAccion($accion) {
        
        switch ($accion) {
            
            case 'obtener_sucursales':
                self::ctrObtenerSucursalesDataTable();
                break;
                
            case 'probar_conexion':
                self::ctrProbarConexionSucursal();
                break;
                
            case 'sincronizar_todas':
                self::ctrSincronizarTodasSucursales();
                break;
                
            case 'sincronizar_seleccionadas':
                self::ctrSincronizarSucursalesSeleccionadas();
                break;
                
            case 'cambiar_estado':
                self::ctrCambiarEstadoSucursal();
                break;
                
            case 'obtener_estadisticas':
                self::ctrObtenerEstadisticasSucursales();
                break;
                
            case 'generar_codigo':
                self::ctrGenerarCodigoSucursal();
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Acción no válida'
                ]);
                break;
        }
    }
}

// Ejecutar acción si viene por AJAX
if (isset($_POST['accion']) || isset($_GET['accion'])) {
    $accion = $_POST['accion'] ?? $_GET['accion'];
    ControladorSucursales::ctrEjecutarAccion($accion);
}

// Llamar a controladores según el contexto
if (isset($_POST["nuevoCodigo"])) {
    ControladorSucursales::ctrCrearSucursal();
}

if (isset($_POST["editarId"])) {
    ControladorSucursales::ctrActualizarSucursal();
}

if (isset($_GET["idSucursal"])) {
    ControladorSucursales::ctrEliminarSucursal();
}

?>