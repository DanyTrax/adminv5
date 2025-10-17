<?php

// Configuración optimizada para servidor de 8GB
ini_set('memory_limit', '4096M');
ini_set('max_execution_time', 1800);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión si no está iniciada
if (!isset($_SESSION)) {
    session_start();
}

require_once "../controladores/sucursales.controlador.php";
require_once "../modelos/sucursales.modelo.php";

class AjaxSucursales {

    public $idSucursal;
    public $apiUrl;
    public $nuevoEstado;
    public $codigo;

    /*=============================================
    DATATABLE SUCURSALES
    =============================================*/
    public function ajaxDatatableSucursales() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                "draw" => intval($_POST['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
            return;
        }

        try {
            
            $respuesta = ModeloSucursales::mdlObtenerSucursales();

            if ($respuesta && $respuesta['success']) {

                $data = [];
                $codigoActual = defined('CODIGO_SUCURSAL') ? CODIGO_SUCURSAL : '';

                foreach ($respuesta['data'] as $sucursal) {

                    // Código con indicador de sucursal actual
                    $codigo = '<span class="label label-' . ($sucursal['codigo_sucursal'] === $codigoActual ? 'success' : 'primary') . '">' . 
                              htmlspecialchars($sucursal['codigo_sucursal']) . '</span>';
                    
                    if ($sucursal['codigo_sucursal'] === $codigoActual) {
                        $codigo .= ' <i class="fa fa-home text-success" title="Esta sucursal"></i>';
                    }

                    // Nombre con indicador de principal
                    $nombre = htmlspecialchars($sucursal['nombre']);
                    if ($sucursal['es_principal']) {
                        $nombre = '<i class="fa fa-star text-warning"></i> ' . $nombre;
                    }

                    // Dirección truncada
                    $direccion = $sucursal['direccion'] ? 
                        (strlen($sucursal['direccion']) > 50 ? 
                            substr(htmlspecialchars($sucursal['direccion']), 0, 50) . '...' : 
                            htmlspecialchars($sucursal['direccion'])) : 
                        '<span class="text-muted">No especificada</span>';

                    // Teléfono
                    $telefono = $sucursal['telefono'] ? 
                        htmlspecialchars($sucursal['telefono']) : 
                        '<span class="text-muted">No especificado</span>';

                    // Estado
                    $estado = $sucursal['activo'] ? 
                        '<span class="label label-success">Activa</span>' : 
                        '<span class="label label-danger">Inactiva</span>';

                    // Última sincronización
                    $ultimaSync = $sucursal['ultima_sincronizacion_formato'];
                    if ($ultimaSync === 'Nunca') {
                        $ultimaSync = '<span class="text-muted">Nunca</span>';
                    } else {
                        $ultimaSync = '<small>' . $ultimaSync . '</small>';
                    }

                    // Acciones
                    $acciones = '<div class="btn-group">';
                    
                    // Botón probar conexión
                    if ($sucursal['codigo_sucursal'] !== $codigoActual) {
                        $acciones .= '<button class="btn btn-info btn-xs btnProbarConexion" 
                                        apiUrl="' . htmlspecialchars($sucursal['url_api']) . '" 
                                        nombreSucursal="' . htmlspecialchars($sucursal['nombre']) . '"
                                        title="Probar conexión">
                                        <i class="fa fa-wifi"></i>
                                      </button>';
                    }

                    // Botón editar
                    if ($sucursal['codigo_sucursal'] !== $codigoActual) {
                        $acciones .= '<button class="btn btn-warning btn-xs btnEditarSucursal" 
                                        idSucursal="' . $sucursal['id'] . '"
                                        title="Editar sucursal">
                                        <i class="fa fa-pencil"></i>
                                      </button>';
                    }

                    // Botón activar/desactivar
                    if ($sucursal['codigo_sucursal'] !== $codigoActual && !$sucursal['es_principal']) {
                        if ($sucursal['activo']) {
                            $acciones .= '<button class="btn btn-danger btn-xs btnCambiarEstado" 
                                            idSucursal="' . $sucursal['id'] . '" 
                                            estadoActual="1"
                                            nombreSucursal="' . htmlspecialchars($sucursal['nombre']) . '"
                                            title="Desactivar">
                                            <i class="fa fa-eye-slash"></i>
                                          </button>';
                        } else {
                            $acciones .= '<button class="btn btn-success btn-xs btnCambiarEstado" 
                                            idSucursal="' . $sucursal['id'] . '" 
                                            estadoActual="0"
                                            nombreSucursal="' . htmlspecialchars($sucursal['nombre']) . '"
                                            title="Activar">
                                            <i class="fa fa-eye"></i>
                                          </button>';
                        }
                    }

                    // Botón eliminar
                    if ($sucursal['codigo_sucursal'] !== $codigoActual && !$sucursal['es_principal']) {
                        $acciones .= '<button class="btn btn-danger btn-xs btnEliminarSucursal" 
                                        idSucursal="' . $sucursal['id'] . '"
                                        nombreSucursal="' . htmlspecialchars($sucursal['nombre']) . '"
                                        title="Eliminar">
                                        <i class="fa fa-times"></i>
                                      </button>';
                    }

                    $acciones .= '</div>';

                    $data[] = [
                        "codigo" => $codigo,
                        "nombre" => $nombre,
                        "direccion" => $direccion,
                        "telefono" => $telefono,
                        "estado" => $estado,
                        "ultima_sincronizacion" => $ultimaSync,
                        "acciones" => $acciones
                    ];
                }

                $json = [
                    "draw" => intval($_POST['draw'] ?? 1),
                    "recordsTotal" => count($data),
                    "recordsFiltered" => count($data),
                    "data" => $data
                ];

            } else {
                $json = [
                    "draw" => intval($_POST['draw'] ?? 1),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => []
                ];
            }

        } catch (Exception $e) {
            error_log("Error en ajaxDatatableSucursales: " . $e->getMessage());
            $json = [
                "draw" => intval($_POST['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ];
        }

        echo json_encode($json);
    }
        /*=============================================
    OBTENER CONFIGURACIÓN LOCAL
    =============================================*/
    public function ajaxObtenerConfiguracionLocal() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        try {
            
            $configuracion = ModeloSucursales::mdlObtenerConfiguracionLocal();
            
            if ($configuracion) {
                echo json_encode([
                    'success' => true,
                    'data' => $configuracion
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró configuración local',
                    'data' => null
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en ajaxObtenerConfiguracionLocal: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener configuración: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    VERIFICAR ESTADO DE REGISTRO
    =============================================*/
    public function ajaxVerificarEstadoRegistro() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        try {
            
            $estado = ControladorSucursales::ctrVerificarRegistroEnCentral();
            echo json_encode([
                'success' => true,
                'estado' => $estado
            ]);

        } catch (Exception $e) {
            error_log("Error en ajaxVerificarEstadoRegistro: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al verificar estado: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    GENERAR CÓDIGO AUTOMÁTICO
    =============================================*/
    public function ajaxGenerarCodigoAutomatico() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        try {
            
            $codigo = ModeloSucursales::mdlGenerarConsecutivoSucursal();
            echo json_encode([
                'success' => true,
                'codigo' => $codigo
            ]);

        } catch (Exception $e) {
            error_log("Error en ajaxGenerarCodigoAutomatico: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar código: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    DETECTAR URL ACTUAL
    =============================================*/
    public function ajaxDetectarURL() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        try {
            
            $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $directorio = dirname($_SERVER['PHP_SELF'] ?? '');
            
            // Limpiar directorio - remover /ajax del path
            $directorio = str_replace('/ajax', '', $directorio);
            $directorio = rtrim($directorio, '/');
            
            $urlBase = $protocolo . $host . $directorio . '/';
            $urlApi = $urlBase . 'api-transferencias/';

            echo json_encode([
                'success' => true,
                'url_base' => $urlBase,
                'url_api' => $urlApi
            ]);

        } catch (Exception $e) {
            error_log("Error en ajaxDetectarURL: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al detectar URL: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    PROBAR CONEXIÓN CON SUCURSAL
    =============================================*/
    public function ajaxProbarConexion() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        if (!isset($this->apiUrl)) {
            echo json_encode(['success' => false, 'message' => 'URL API requerida']);
            return;
        }

        try {
            
            $respuesta = ModeloSucursales::mdlProbarConexionSucursal($this->apiUrl);
            echo json_encode($respuesta);

        } catch (Exception $e) {
            error_log("Error en ajaxProbarConexion: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al probar conexión: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE SUCURSALES
    =============================================*/
    public function ajaxObtenerEstadisticas() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        try {
            
            $sucursales = ModeloSucursales::mdlObtenerSucursales();
            
            if ($sucursales['success']) {
                
                $estadisticas = [
                    'total' => count($sucursales['data']),
                    'activas' => 0,
                    'inactivas' => 0,
                    'principales' => 0,
                    'ultima_sincronizacion' => 'Nunca'
                ];

                $fechaMasReciente = null;

                foreach ($sucursales['data'] as $sucursal) {
                    
                    // Contar estados
                    if ($sucursal['activo']) {
                        $estadisticas['activas']++;
                    } else {
                        $estadisticas['inactivas']++;
                    }

                    // Contar principales
                    if ($sucursal['es_principal']) {
                        $estadisticas['principales']++;
                    }

                    // Buscar fecha más reciente
                    if (!empty($sucursal['fecha_ultima_sincronizacion_catalogo']) && 
                        $sucursal['fecha_ultima_sincronizacion_catalogo'] !== '0000-00-00 00:00:00') {
                        
                        $timestamp = strtotime($sucursal['fecha_ultima_sincronizacion_catalogo']);
                        
                        if ($fechaMasReciente === null || $timestamp > $fechaMasReciente) {
                            $fechaMasReciente = $timestamp;
                        }
                    }
                }

                // Formatear fecha más reciente
                if ($fechaMasReciente !== null) {
                    $estadisticas['ultima_sincronizacion'] = date('d/m/Y H:i:s', $fechaMasReciente);
                }

                echo json_encode([
                    'success' => true,
                    'estadisticas' => $estadisticas
                ]);

            } else {
                
                echo json_encode([
                    'success' => false,
                    'message' => $sucursales['message'],
                    'estadisticas' => [
                        'total' => 0,
                        'activas' => 0,
                        'inactivas' => 0,
                        'principales' => 0,
                        'ultima_sincronizacion' => 'Error'
                    ]
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en ajaxObtenerEstadisticas: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'estadisticas' => [
                    'total' => 0,
                    'activas' => 0,
                    'inactivas' => 0,
                    'principales' => 0,
                    'ultima_sincronizacion' => 'Error'
                ]
            ]);
        }
    }
        /*=============================================
    EDITAR SUCURSAL
    =============================================*/
    public function ajaxEditarSucursal() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        if (!isset($this->idSucursal)) {
            echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
            return;
        }

        try {
            
            $sucursal = ModeloSucursales::mdlMostrarSucursal("id", $this->idSucursal);
            
            if ($sucursal) {
                echo json_encode([
                    'success' => true,
                    'data' => $sucursal
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sucursal no encontrada'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en ajaxEditarSucursal: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener sucursal: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    CAMBIAR ESTADO DE SUCURSAL
    =============================================*/
    public function ajaxCambiarEstado() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        if (!isset($this->idSucursal) || !isset($this->nuevoEstado)) {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
            return;
        }

        try {
            
            // Obtener datos actuales de la sucursal
            $sucursalActual = ModeloSucursales::mdlMostrarSucursal("id", $this->idSucursal);
            
            if (!$sucursalActual) {
                echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
                return;
            }

            // Preparar datos para actualización (manteniendo los existentes)
            $datos = [
                'id' => $this->idSucursal,
                'nombre' => $sucursalActual['nombre'],
                'direccion' => $sucursalActual['direccion'],
                'telefono' => $sucursalActual['telefono'],
                'email' => $sucursalActual['email'],
                'url_base' => $sucursalActual['url_base'],
                'url_api' => $sucursalActual['url_api'],
                'activo' => $this->nuevoEstado
            ];

            $respuesta = ModeloSucursales::mdlActualizarSucursalCentral($datos);
            echo json_encode($respuesta);

        } catch (Exception $e) {
            error_log("Error en ajaxCambiarEstado: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    public function ajaxEliminarSucursal() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        if (!isset($this->idSucursal)) {
            echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
            return;
        }

        try {
            
            // Verificar que no sea la sucursal actual
            $sucursal = ModeloSucursales::mdlMostrarSucursal("id", $this->idSucursal);
            
            if (!$sucursal) {
                echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
                return;
            }

            $codigoActual = defined('CODIGO_SUCURSAL') ? CODIGO_SUCURSAL : '';
            
            if ($sucursal['codigo_sucursal'] === $codigoActual) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar la sucursal actual']);
                return;
            }

            if ($sucursal['es_principal']) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar la sucursal principal']);
                return;
            }

            $respuesta = ModeloSucursales::mdlEliminarSucursalCentral($this->idSucursal);
            echo json_encode($respuesta);

        } catch (Exception $e) {
            error_log("Error en ajaxEliminarSucursal: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar sucursal: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    VALIDAR CÓDIGO ÚNICO
    =============================================*/
    public function ajaxValidarCodigo() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        if (!isset($this->codigo)) {
            echo json_encode(['success' => false, 'message' => 'Código requerido']);
            return;
        }

        try {
            
            // Verificar en BD central si el código ya existe
            require_once "../api-transferencias/conexion-central.php";
            $pdo = ConexionCentral::conectar();
            
            $stmt = $pdo->prepare("SELECT id FROM sucursales WHERE codigo_sucursal = ?");
            $stmt->execute([$this->codigo]);
            $existe = $stmt->fetch();

            if ($existe) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Este código ya está en uso por otra sucursal',
                    'disponible' => false
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Código disponible',
                    'disponible' => true
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en ajaxValidarCodigo: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al validar código: ' . $e->getMessage(),
                'disponible' => false
            ]);
        }
    }

    /*=============================================
    REGISTRAR ESTA SUCURSAL EN BD CENTRAL
    =============================================*/
    public function ajaxRegistrarEstaSucursal() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        try {
            
            // Obtener configuración local
            $datosLocales = ModeloSucursales::mdlObtenerConfiguracionLocal();
            
            if (!$datosLocales) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Primero debe configurar los datos de esta sucursal'
                ]);
                return;
            }

            // Verificar si ya está registrada
            $verificacion = ModeloSucursales::mdlVerificarSucursalEnCentral($datosLocales['codigo_sucursal']);
            
            if ($verificacion['success'] && $verificacion['registrada']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Esta sucursal ya está registrada en el directorio central'
                ]);
                return;
            }

            // Preparar datos para registro EN BD CENTRAL
            $datos = [
                'codigo_sucursal' => $datosLocales['codigo_sucursal'],
                'nombre' => $datosLocales['nombre'],
                'direccion' => $datosLocales['direccion'],
                'telefono' => $datosLocales['telefono'],
                'email' => $datosLocales['email'],
                'url_base' => $datosLocales['url_base'],
                'url_api' => $datosLocales['url_api'],
                'es_principal' => $datosLocales['es_principal'],
                'activo' => 1
            ];

            // ✅ USAR EL MÉTODO CORRECTO PARA BD CENTRAL
            $respuesta = ModeloSucursales::mdlCrearSucursalCentral($datos);
            
            if ($respuesta['success']) {
                // Actualizar estado local
                ModeloSucursales::mdlActualizarEstadoRegistro($datosLocales['id'], 1);
            }

            echo json_encode($respuesta);

        } catch (Exception $e) {
            error_log("Error en ajaxRegistrarEstaSucursal: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar sucursal: ' . $e->getMessage()
            ]);
        }
    }
/*=============================================
SINCRONIZAR CATÁLOGO A TODAS LAS SUCURSALES
=============================================*/
public function ajaxSincronizarCatalogo() {

    // Verificar permisos
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
        echo json_encode(['success' => false, 'message' => 'Sin permisos']);
        return;
    }

    try {
        
        // ✅ OBTENER CATÁLOGO MAESTRO PROCESADO CON TU LÓGICA EXISTENTE
        require_once "../modelos/catalogo-maestro.modelo.php";
        
        // Usar el nuevo método que reutiliza tu lógica de productos divisibles
        $catalogoMaestro = ModeloCatalogoMaestro::mdlObtenerDatosParaSincronizacion();
        
        if (!$catalogoMaestro || empty($catalogoMaestro)) {
            echo json_encode([
                'success' => false,
                'message' => 'No hay productos en el catálogo maestro para sincronizar'
            ]);
            return;
        }

        // ✅ OBTENER SUCURSALES ACTIVAS (EXCLUYENDO ESTA)
        $respuestaSucursales = ModeloSucursales::mdlObtenerSucursales();
        
        if (!$respuestaSucursales || !$respuestaSucursales['success'] || empty($respuestaSucursales['data'])) {
            echo json_encode([
                'success' => false,
                'message' => 'No hay sucursales registradas para sincronizar'
            ]);
            return;
        }

        $codigoActual = defined('CODIGO_SUCURSAL') ? CODIGO_SUCURSAL : '';
        $sucursales = array_filter($respuestaSucursales['data'], function($s) use ($codigoActual) {
            return $s['activo'] && $s['codigo_sucursal'] !== $codigoActual;
        });

        if (empty($sucursales)) {
            echo json_encode([
                'success' => false,
                'message' => 'No hay sucursales activas disponibles para sincronizar'
            ]);
            return;
        }

        // ✅ PREPARAR DATOS PARA ENVÍO
        $datosEnvio = [
            'accion' => 'sincronizar_catalogo',
            'catalogo' => $catalogoMaestro,
            'origen' => [
                'codigo' => $codigoActual ?: 'CENTRAL',
                'timestamp' => date('Y-m-d H:i:s'),
                'total_productos' => count($catalogoMaestro)
            ]
        ];

        // ✅ ENVIAR A CADA SUCURSAL ACTIVA
        $respuestasSincronizacion = [];
        $tiempoInicio = microtime(true);

        foreach ($sucursales as $sucursal) {
            
            $nombreSucursal = $sucursal['nombre'];
            $apiUrl = rtrim($sucursal['url_api'], '/') . '/sincronizar_catalogo.php';
            
            try {
                
                $tiempoInicioSucursal = microtime(true);
                
                // Configurar cURL para envío
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $apiUrl,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($datosEnvio),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'User-Agent: AdminV5-SyncCatalog/1.0'
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 300, // 5 minutos para sincronización completa
                    CURLOPT_CONNECTTIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3
                ]);

                $respuestaCurl = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $tiempoRespuesta = round((microtime(true) - $tiempoInicioSucursal) * 1000) . 'ms';
                
                if (curl_errno($ch)) {
                    throw new Exception('Error cURL: ' . curl_error($ch));
                }
                
                curl_close($ch);

                if ($httpCode === 200 && $respuestaCurl) {
                    
                    $respuestaJson = json_decode($respuestaCurl, true);
                    
                    if ($respuestaJson && $respuestaJson['success']) {
                        
                        $respuestasSincronizacion[$nombreSucursal] = [
                            'success' => true,
                            'message' => $respuestaJson['message'] ?? 'Sincronización exitosa',
                            'estadisticas' => $respuestaJson['estadisticas'] ?? [],
                            'tiempo_respuesta' => $tiempoRespuesta
                        ];
                        
                    } else {
                        
                        $respuestasSincronizacion[$nombreSucursal] = [
                            'success' => false,
                            'message' => $respuestaJson['message'] ?? 'Error desconocido en la respuesta'
                        ];
                    }
                    
                } else {
                    
                    $respuestasSincronizacion[$nombreSucursal] = [
                        'success' => false,
                        'message' => "Error HTTP {$httpCode}: " . ($respuestaCurl ? substr($respuestaCurl, 0, 100) : 'Sin respuesta')
                    ];
                }

            } catch (Exception $e) {
                
                $respuestasSincronizacion[$nombreSucursal] = [
                    'success' => false,
                    'message' => 'Error de conexión: ' . $e->getMessage()
                ];
            }
        }

        // ✅ PROCESAR RESPUESTAS DE TODAS LAS SUCURSALES
        $respuestaCompleta = [
            'success' => true,
            'message' => 'Sincronización completada',
            'sucursales_exitosas' => 0,
            'sucursales_fallidas' => 0,
            'total_productos' => count($catalogoMaestro),
            'tiempo_total' => round((microtime(true) - $tiempoInicio) * 1000) . 'ms',
            'detalle_sucursales' => [],
            'errores' => []
        ];

        foreach ($respuestasSincronizacion as $sucursal => $respuesta) {
            
            if ($respuesta && $respuesta['success']) {
                
                $respuestaCompleta['sucursales_exitosas']++;
                $respuestaCompleta['detalle_sucursales'][$sucursal] = [
                    'status' => 'exitoso',
                    'productos_procesados' => $respuesta['estadisticas']['productos_procesados'] ?? 0,
                    'productos_nuevos' => $respuesta['estadisticas']['productos_nuevos'] ?? 0,
                    'tiempo_respuesta' => $respuesta['tiempo_respuesta'] ?? 'N/A'
                ];
                
            } else {
                
                $respuestaCompleta['sucursales_fallidas']++;
                $respuestaCompleta['errores'][] = [
                    'sucursal' => $sucursal,
                    'error' => $respuesta['message'] ?? 'Error desconocido'
                ];
            }
        }

        // ✅ DETERMINAR ÉXITO GENERAL
        if ($respuestaCompleta['sucursales_fallidas'] > 0) {
            $respuestaCompleta['success'] = false;
            $respuestaCompleta['message'] = "Sincronización completada con {$respuestaCompleta['sucursales_fallidas']} errores";
        }

        echo json_encode($respuestaCompleta);

    } catch (Exception $e) {
        error_log("Error crítico en ajaxSincronizarCatalogo: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error crítico del sistema: ' . $e->getMessage()
        ]);
    }
}

    /*=============================================
    ACTUALIZAR FECHA DE SINCRONIZACIÓN
    =============================================*/
    private function actualizarFechaSincronizacion($codigoSucursal) {
        try {
            require_once "../api-transferencias/conexion-central.php";
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
    OBTENER PROGRESO DE SINCRONIZACIÓN EN TIEMPO REAL
    =============================================*/
    public function ajaxObtenerProgresoSync() {

        // Verificar permisos
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            return;
        }

        try {
            
            // Leer archivo de progreso temporal si existe
            $archivoProgreso = sys_get_temp_dir() . '/adminv5_sync_progress_' . session_id() . '.json';
            
            if (file_exists($archivoProgreso)) {
                $progreso = json_decode(file_get_contents($archivoProgreso), true);
                echo json_encode([
                    'success' => true,
                    'progreso' => $progreso,
                    'en_proceso' => true
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'progreso' => null,
                    'en_proceso' => false
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en ajaxObtenerProgresoSync: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener progreso: ' . $e->getMessage()
            ]);
        }
    }
    }

/*=============================================
PROCESADORES DE PETICIONES AJAX
=============================================*/

// Configuración inicial para todas las peticiones
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Verificar método de petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar sesión activa
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

try {

    /*=============================================
    DATATABLE SUCURSALES
    =============================================*/
    if (isset($_POST["accion"]) && $_POST["accion"] == "datatable") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxDatatableSucursales();
    }

    /*=============================================
    CONFIGURACIÓN LOCAL
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_config_local") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxObtenerConfiguracionLocal();
    }

    /*=============================================
    VERIFICAR ESTADO DE REGISTRO
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "verificar_estado") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxVerificarEstadoRegistro();
    }

    /*=============================================
    GENERAR CÓDIGO AUTOMÁTICO
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "generar_codigo") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxGenerarCodigoAutomatico();
    }

    /*=============================================
    DETECTAR URL ACTUAL
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "detectar_url") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxDetectarURL();
    }

    /*=============================================
    PROBAR CONEXIÓN CON SUCURSAL
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "probar_conexion") {
        if (!isset($_POST["apiUrl"])) {
            echo json_encode(['success' => false, 'message' => 'URL API requerida']);
            exit;
        }
        
        $ajax = new AjaxSucursales();
        $ajax->apiUrl = $_POST["apiUrl"];
        $ajax->ajaxProbarConexion();
    }

    /*=============================================
    EDITAR SUCURSAL
    =============================================*/
    else if (isset($_POST["idSucursal"]) && !isset($_POST["accion"])) {
        $ajax = new AjaxSucursales();
        $ajax->idSucursal = $_POST["idSucursal"];
        $ajax->ajaxEditarSucursal();
    }

    /*=============================================
    CAMBIAR ESTADO DE SUCURSAL
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "cambiar_estado") {
        if (!isset($_POST["idSucursal"]) || !isset($_POST["nuevoEstado"])) {
            echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
            exit;
        }
        
        $ajax = new AjaxSucursales();
        $ajax->idSucursal = $_POST["idSucursal"];
        $ajax->nuevoEstado = $_POST["nuevoEstado"];
        $ajax->ajaxCambiarEstado();
    }

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "eliminar_sucursal") {
        if (!isset($_POST["idSucursal"])) {
            echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
            exit;
        }
        
        $ajax = new AjaxSucursales();
        $ajax->idSucursal = $_POST["idSucursal"];
        $ajax->ajaxEliminarSucursal();
    }

    /*=============================================
    VALIDAR CÓDIGO ÚNICO
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "validar_codigo") {
        if (!isset($_POST["codigo"])) {
            echo json_encode(['success' => false, 'message' => 'Código requerido']);
            exit;
        }
        
        $ajax = new AjaxSucursales();
        $ajax->codigo = $_POST["codigo"];
        $ajax->ajaxValidarCodigo();
    }

    /*=============================================
    REGISTRAR ESTA SUCURSAL
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "registrar_esta") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxRegistrarEstaSucursal();
    }

    /*=============================================
    SINCRONIZAR CATÁLOGO MAESTRO
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizar_catalogo") {
        // Aumentar límites para sincronización masiva con tus 8GB disponibles
        ini_set('max_execution_time', 7200); // 2 horas
        ini_set('memory_limit', '6144M'); // 6GB de tus 8GB disponibles
        
        $ajax = new AjaxSucursales();
        $ajax->ajaxSincronizarCatalogo();
    }

    /*=============================================
    OBTENER ESTADÍSTICAS
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_estadisticas") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxObtenerEstadisticas();
    }

    /*=============================================
    OBTENER PROGRESO DE SINCRONIZACIÓN
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_progreso_sync") {
        $ajax = new AjaxSucursales();
        $ajax->ajaxObtenerProgresoSync();
    }

    /*=============================================
    PING DE CONECTIVIDAD
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "ping_sucursal") {
        if (!isset($_POST["urlApi"])) {
            echo json_encode(['success' => false, 'message' => 'URL API requerida']);
            exit;
        }

        try {
            
            $inicioTiempo = microtime(true);
            $urlPing = rtrim($_POST["urlApi"], '/') . '/test_conexion.php';
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'header' => [
                        'User-Agent: AdminV5-Ping/1.0',
                        'Accept: application/json'
                    ]
                ]
            ]);
            
            $respuesta = @file_get_contents($urlPing, false, $context);
            $tiempoRespuesta = round((microtime(true) - $inicioTiempo) * 1000);
            
            if ($respuesta !== false) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Conexión exitosa',
                    'tiempo_respuesta' => $tiempoRespuesta . 'ms',
                    'estado' => 'online'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sin respuesta del servidor',
                    'tiempo_respuesta' => $tiempoRespuesta . 'ms',
                    'estado' => 'offline'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error de conectividad: ' . $e->getMessage(),
                'estado' => 'error'
            ]);
        }
    }

    /*=============================================
    BACKUP DE CONFIGURACIÓN
    =============================================*/
    else if (isset($_POST["accion"]) && $_POST["accion"] == "backup_config") {
        
        try {
            
            $configuracion = ModeloSucursales::mdlObtenerConfiguracionLocal();
            
            if ($configuracion) {
                
                // Crear backup en formato JSON
                $backup = [
                    'fecha_backup' => date('Y-m-d H:i:s'),
                    'version' => '5.0',
                    'sucursal' => $configuracion,
                    'servidor' => [
                        'php_version' => PHP_VERSION,
                        'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
                        'deployment_path' => '/home/epicosie/pruebas.acplasticos.com/' // Tu path de deployment
                    ]
                ];
                
                $nombreArchivo = 'backup_sucursal_' . $configuracion['codigo_sucursal'] . '_' . date('Y-m-d_H-i-s') . '.json';
                
                // Enviar como descarga
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
                header('Content-Length: ' . strlen(json_encode($backup, JSON_PRETTY_PRINT)));
                
                echo json_encode($backup, JSON_PRETTY_PRINT);
                exit;
                
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay configuración local para hacer backup'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar backup: ' . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    ACCIÓN NO RECONOCIDA
    =============================================*/
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no reconocida',
            'accion_recibida' => $_POST["accion"] ?? 'No especificada'
        ]);
    }

} catch (Exception $e) {
    
    // Log del error
    error_log("Error crítico en sucursales.ajax.php: " . $e->getMessage());
    
    // Respuesta de error
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error_code' => $e->getCode(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Throwable $t) {
    
    // Capturar errores fatales en PHP 7+
    error_log("Error fatal en sucursales.ajax.php: " . $t->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error fatal del sistema',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

?>