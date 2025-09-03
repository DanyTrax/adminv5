<?php

require_once "../controladores/sucursales.controlador.php";
require_once "../modelos/sucursales.modelo.php";

class AjaxSucursales {

    /*=============================================
    DATATABLE SUCURSALES
    =============================================*/
public function ajaxTablaProductos() {

    // Verificar permisos
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
        echo json_encode([
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
            "error" => "Sesión no válida o sin permisos."
        ]);
        return;
    }

    // Llamar al modelo para obtener los datos
    $respuesta = ModeloSucursales::mdlObtenerSucursales();

    // Construir la respuesta JSON basada en el resultado del modelo
    if ($respuesta['success']) {
        
        // El modelo devolvió datos exitosamente
        $data = [];
        foreach ($respuesta['data'] as $sucursal) {

            // Logo
            if (!empty($sucursal['logo'])) {
                $logo = '<img src="vistas/img/sucursales/'.$sucursal['logo'].'" class="img-thumbnail logo-sucursal" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">';
            } else {
                $logo = '<div class="logo-placeholder" style="width: 50px; height: 50px; background: #f4f4f4; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-building text-muted" style="font-size: 20px;"></i></div>';
            }

            // Nombre con indicador de principal
            $nombre = $sucursal['nombre'];
            if ($sucursal['es_principal']) {
                $nombre .= ' <i class="fa fa-star text-warning" title="Sucursal Principal"></i>';
            }

            // Estado con toggle
            $estado = '';
            if ($sucursal['activa']) {
                $estado = '<button class="btn btn-success btn-xs btnCambiarEstado" estadoSucursal="0" idSucursal="'.$sucursal['id'].'"><i class="fa fa-eye"></i></button>';
            } else {
                $estado = '<button class="btn btn-danger btn-xs btnCambiarEstado" estadoSucursal="1" idSucursal="'.$sucursal['id'].'"><i class="fa fa-eye-slash"></i></button>';
            }

            // Última sincronización
            $ultimaSync = $sucursal['ultima_sincronizacion_formato'] ?? '<span class="text-muted">Nunca</span>';

            // Botones de acción
            $acciones = '<div class="btn-group">';
            $acciones .= '<button class="btn btn-warning btn-xs btnEditarSucursal" idSucursal="'.$sucursal['id'].'" data-toggle="modal" data-target="#modalEditarSucursal"><i class="fa fa-pencil"></i></button>';
            $acciones .= '<button class="btn btn-info btn-xs btnProbarConexion" apiUrl="'.$sucursal['api_url'].'" nombreSucursal="'.$sucursal['nombre'].'"><i class="fa fa-wifi"></i></button>';
            if ($sucursal['activa']) {
                $acciones .= '<button class="btn btn-primary btn-xs btnSincronizarIndividual" idSucursal="'.$sucursal['id'].'" nombreSucursal="'.$sucursal['nombre'].'"><i class="fa fa-refresh"></i></button>';
            }
            if (!$sucursal['es_principal']) {
                $acciones .= '<button class="btn btn-danger btn-xs btnEliminarSucursal" idSucursal="'.$sucursal['id'].'" nombreSucursal="'.$sucursal['nombre'].'" logoSucursal="'.$sucursal['logo'].'"><i class="fa fa-times"></i></button>';
            }
            $acciones .= '</div>';

            $data[] = [
                "logo" => $logo,
                "codigo" => $sucursal['codigo_sucursal'],
                "nombre" => $nombre,
                "direccion" => $sucursal['direccion'] ?? '<span class="text-muted">No especificada</span>',
                "telefono" => $sucursal['telefono'] ?? '<span class="text-muted">No especificado</span>',
                "estado" => $estado,
                "ultima_sincronizacion" => $ultimaSync,
                "acciones" => $acciones
            ];
        }

        $json = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ];

    } else {
        
        // El modelo devolvió 'success' => false, construimos una respuesta de error.
        $json = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
            "error" => $respuesta['message'] ?? 'No se pudieron cargar los datos. Verifique la API.'
        ];
    }

    // Imprimir la respuesta final UNA SOLA VEZ
    echo json_encode($json);
}

    /*=============================================
    EDITAR SUCURSAL
    =============================================*/
    public function ajaxEditarSucursal() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode(['error' => 'Sin permisos']);
            return;
        }

        if (isset($this->idSucursal)) {

            $item = "id";
            $valor = $this->idSucursal;

            $respuesta = ControladorSucursales::ctrMostrarSucursal($item, $valor);

            echo json_encode($respuesta);
        }
    }

    /*=============================================
    PROBAR CONEXIÓN CON SUCURSAL
    =============================================*/
    public function ajaxProbarConexion() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }

        if (isset($this->apiUrl)) {

            $respuesta = ModeloSucursales::mdlProbarConexionSucursal($this->apiUrl);
            echo json_encode($respuesta);
        }
    }

    /*=============================================
    CAMBIAR ESTADO DE SUCURSAL
    =============================================*/
    public function ajaxCambiarEstado() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }

        if (isset($this->idSucursal) && isset($this->estadoSucursal)) {

            // Obtener datos actuales de la sucursal
            $sucursal = ControladorSucursales::ctrMostrarSucursal("id", $this->idSucursal);

            if (!$sucursal) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sucursal no encontrada'
                ]);
                return;
            }

            // No permitir desactivar sucursal principal
            if ($sucursal['es_principal'] && $this->estadoSucursal == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede desactivar la sucursal principal'
                ]);
                return;
            }

            // Actualizar solo el estado
            $datos = $sucursal;
            $datos['activa'] = $this->estadoSucursal;

            $respuesta = ModeloSucursales::mdlActualizarSucursal($datos);
            echo json_encode($respuesta);
        }
    }

    /*=============================================
    SINCRONIZAR TODAS LAS SUCURSALES
    =============================================*/
    public function ajaxSincronizarTodas() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }

        try {

            // 1. Sincronizar localmente primero
            require_once "../modelos/catalogo-maestro.modelo.php";
            $resultadoLocal = ModeloCatalogoMaestro::mdlSincronizarAProductosLocales();

            // 2. Sincronizar con otras sucursales
            $resultadoRemoto = ModeloSucursales::mdlSincronizarCatalogoConSucursales();

            // 3. Construir respuesta consolidada
            $respuesta = [
                'success' => true,
                'message' => 'Sincronización completada',
                'detalles' => [
                    'local' => [
                        'sincronizados' => $resultadoLocal['sincronizados'] ?? 0,
                        'actualizados' => $resultadoLocal['actualizados'] ?? 0
                    ],
                    'remotas' => [
                        'procesadas' => $resultadoRemoto['sucursales_procesadas'] ?? 0,
                        'exitosas' => $resultadoRemoto['sucursales_exitosas'] ?? 0,
                        'fallidas' => $resultadoRemoto['sucursales_fallidas'] ?? 0
                    ]
                ]
            ];

            // Mensaje detallado
            $respuesta['message_detallado'] = 
                "SUCURSAL ACTUAL:\n" .
                "• Productos nuevos: " . $respuesta['detalles']['local']['sincronizados'] . "\n" .
                "• Productos actualizados: " . $respuesta['detalles']['local']['actualizados'] . "\n\n" .
                "OTRAS SUCURSALES:\n" .
                "• Sucursales procesadas: " . $respuesta['detalles']['remotas']['procesadas'] . "\n" .
                "• Sincronizaciones exitosas: " . $respuesta['detalles']['remotas']['exitosas'] . "\n" .
                "• Sincronizaciones fallidas: " . $respuesta['detalles']['remotas']['fallidas'];

            echo json_encode($respuesta);

        } catch (Exception $e) {

            echo json_encode([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    SINCRONIZAR SUCURSALES SELECCIONADAS
    =============================================*/
    public function ajaxSincronizarSeleccionadas() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }

        if (isset($this->sucursales)) {

            try {

                $sucursalesIds = json_decode($this->sucursales, true);

                if (empty($sucursalesIds)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Debe seleccionar al menos una sucursal'
                    ]);
                    return;
                }

                // 1. Sincronizar localmente primero
                require_once "../modelos/catalogo-maestro.modelo.php";
                $resultadoLocal = ModeloCatalogoMaestro::mdlSincronizarAProductosLocales();

                // 2. Sincronizar con sucursales seleccionadas
                $resultadoRemoto = ModeloSucursales::mdlSincronizarCatalogoConSucursales($sucursalesIds);

                // 3. Construir respuesta
                $respuesta = [
                    'success' => true,
                    'message' => 'Sincronización selectiva completada',
                    'local' => $resultadoLocal,
                    'remoto' => $resultadoRemoto
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
    OBTENER ESTADÍSTICAS DE SUCURSALES
    =============================================*/
    public function ajaxObtenerEstadisticas() {

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

                $estadisticas = [
                    'total' => count($respuesta['data']),
                    'activas' => 0,
                    'inactivas' => 0,
                    'con_logo' => 0,
                    'principales' => 0,
                    'ultima_sincronizacion' => 'Nunca'
                ];

                $ultimaSyncTimestamp = null;

                foreach ($respuesta['data'] as $sucursal) {

                    // Contar estados
                    if ($sucursal['activa']) {
                        $estadisticas['activas']++;
                    } else {
                        $estadisticas['inactivas']++;
                    }

                    // Contar logos
                    if (!empty($sucursal['logo'])) {
                        $estadisticas['con_logo']++;
                    }

                    // Contar principales
                    if ($sucursal['es_principal']) {
                        $estadisticas['principales']++;
                    }

                    // Buscar última sincronización
                    if (!empty($sucursal['ultima_sincronizacion'])) {
                        $timestamp = strtotime($sucursal['ultima_sincronizacion']);
                        if ($ultimaSyncTimestamp === null || $timestamp > $ultimaSyncTimestamp) {
                            $ultimaSyncTimestamp = $timestamp;
                        }
                    }
                }

                // Formatear última sincronización
                if ($ultimaSyncTimestamp) {
                    $estadisticas['ultima_sincronizacion'] = date('d/m/Y H:i', $ultimaSyncTimestamp);
                }

                echo json_encode([
                    'success' => true,
                    'estadisticas' => $estadisticas
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
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    GENERAR CÓDIGO DE SUCURSAL AUTOMÁTICO
    =============================================*/
    public function ajaxGenerarCodigo() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
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
                } while (in_array($codigo, $codigosExistentes) && $contador <= 999);

                if ($contador > 999) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se ha alcanzado el límite máximo de sucursales (999)'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'codigo' => $codigo
                    ]);
                }

            } else {

                echo json_encode([
                    'success' => false,
                    'message' => 'Error al obtener sucursales: ' . $respuesta['message']
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
    OBTENER LISTA DE SUCURSALES PARA SINCRONIZACIÓN SELECTIVA
    =============================================*/
    public function ajaxObtenerSucursalesSync() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para acceder'
            ]);
            return;
        }

        try {

            $respuesta = ModeloSucursales::mdlObtenerSucursales(true); // Solo activas

            if ($respuesta['success']) {

                $sucursales = [];
                foreach ($respuesta['data'] as $sucursal) {
                    
                    // Excluir la sucursal actual
                    if (defined('NOMBRE_SUCURSAL') && $sucursal['nombre'] === NOMBRE_SUCURSAL) {
                        continue;
                    }

                    $sucursales[] = [
                        'id' => $sucursal['id'],
                        'nombre' => $sucursal['nombre'],
                        'codigo' => $sucursal['codigo_sucursal'],
                        'direccion' => $sucursal['direccion'] ?? '',
                        'es_principal' => $sucursal['es_principal']
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'sucursales' => $sucursales
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
    VALIDAR CÓDIGO DE SUCURSAL ÚNICO
    =============================================*/
    public function ajaxValidarCodigo() {

        // Verificar permisos
        if ($_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                'success' => false,
                'message' => 'Sin permisos para realizar esta acción'
            ]);
            return;
        }

        if (isset($this->codigo)) {

            $idExcluir = isset($this->idExcluir) ? $this->idExcluir : null;
            $respuesta = ModeloSucursales::mdlValidarCodigoUnico($this->codigo, $idExcluir);
            echo json_encode($respuesta);
        }
    }
    /*=============================================
SINCRONIZAR CATÁLOGO MAESTRO CON TODAS LAS SUCURSALES
=============================================*/
public function ajaxSincronizarCatalogoMaestro() {

    // Verificar permisos
    if ($_SESSION["perfil"] != "Administrador") {
        echo json_encode([
            'success' => false,
            'message' => 'Sin permisos para realizar esta acción'
        ]);
        return;
    }

    try {

        // 1. Sincronizar catálogo maestro localmente
        require_once "../modelos/catalogo-maestro.modelo.php";
        $resultadoLocal = ModeloCatalogoMaestro::mdlSincronizarAProductosLocales();

        // 2. Sincronizar con otras sucursales
        $resultadoRemoto = ModeloSucursales::mdlSincronizarCatalogoConSucursales();

        // 3. Construir respuesta consolidada
        $respuesta = [
            'success' => true,
            'message' => 'Catálogo maestro sincronizado correctamente',
            'detalles' => [
                'local' => [
                    'sincronizados' => $resultadoLocal['sincronizados'] ?? 0,
                    'actualizados' => $resultadoLocal['actualizados'] ?? 0
                ],
                'remotas' => [
                    'procesadas' => $resultadoRemoto['sucursales_procesadas'] ?? 0,
                    'exitosas' => $resultadoRemoto['sucursales_exitosas'] ?? 0,
                    'fallidas' => $resultadoRemoto['sucursales_fallidas'] ?? 0
                ]
            ]
        ];

        // Mensaje detallado
        $respuesta['message_detallado'] = 
            "CATÁLOGO MAESTRO SINCRONIZADO:\n\n" .
            "SUCURSAL ACTUAL:\n" .
            "• Productos nuevos: " . $respuesta['detalles']['local']['sincronizados'] . "\n" .
            "• Productos actualizados: " . $respuesta['detalles']['local']['actualizados'] . "\n\n" .
            "OTRAS SUCURSALES:\n" .
            "• Sucursales procesadas: " . $respuesta['detalles']['remotas']['procesadas'] . "\n" .
            "• Sincronizaciones exitosas: " . $respuesta['detalles']['remotas']['exitosas'] . "\n" .
            "• Sincronizaciones fallidas: " . $respuesta['detalles']['remotas']['fallidas'];

        echo json_encode($respuesta);

    } catch (Exception $e) {

        echo json_encode([
            'success' => false,
            'message' => 'Error en sincronización de catálogo: ' . $e->getMessage()
        ]);
    }
}
}

/*=============================================
PROCESAR PETICIONES AJAX
=============================================*/

// Verificar que la sesión esté iniciada
if (!isset($_SESSION)) {
    session_start();
}

// DataTable Sucursales
if (isset($_POST["accion"]) && $_POST["accion"] == "datatable") {
    $tabla = new AjaxSucursales();
    $tabla->ajaxTablaProductos();
}

// Editar Sucursal
if (isset($_POST["idSucursal"])) {
    $editar = new AjaxSucursales();
    $editar->idSucursal = $_POST["idSucursal"];
    $editar->ajaxEditarSucursal();
}

// Probar Conexión
if (isset($_POST["accion"]) && $_POST["accion"] == "probar_conexion") {
    $probar = new AjaxSucursales();
    $probar->apiUrl = $_POST["apiUrl"];
    $probar->ajaxProbarConexion();
}

// Cambiar Estado
if (isset($_POST["accion"]) && $_POST["accion"] == "cambiar_estado") {
    $estado = new AjaxSucursales();
    $estado->idSucursal = $_POST["idSucursal"];
    $estado->estadoSucursal = $_POST["estadoSucursal"];
    $estado->ajaxCambiarEstado();
}

// Sincronizar Todas
if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar_todas") {
    $sincronizar = new AjaxSucursales();
    $sincronizar->ajaxSincronizarTodas();
}

// Sincronizar Seleccionadas
if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar_seleccionadas") {
    $sincronizar = new AjaxSucursales();
    $sincronizar->sucursales = $_POST["sucursales"];
    $sincronizar->ajaxSincronizarSeleccionadas();
}

// Obtener Estadísticas
if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_estadisticas") {
    $estadisticas = new AjaxSucursales();
    $estadisticas->ajaxObtenerEstadisticas();
}

// Generar Código
if (isset($_POST["accion"]) && $_POST["accion"] == "generar_codigo") {
    $codigo = new AjaxSucursales();
    $codigo->ajaxGenerarCodigo();
}

// Obtener Sucursales para Sincronización
if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_sucursales_sync") {
    $sucursales = new AjaxSucursales();
    $sucursales->ajaxObtenerSucursalesSync();
}

// Validar Código Único
if (isset($_POST["accion"]) && $_POST["accion"] == "validar_codigo") {
    $validar = new AjaxSucursales();
    $validar->codigo = $_POST["codigo"];
    $validar->idExcluir = $_POST["idExcluir"] ?? null;
    $validar->ajaxValidarCodigo();
}

// Sincronizar Catálogo Maestro
if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar_catalogo_maestro") {
    $sincronizar = new AjaxSucursales();
    $sincronizar->ajaxSincronizarCatalogoMaestro();
}

?>